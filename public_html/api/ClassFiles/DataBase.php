<?php

use PgSql\Result;

require(dirname(__DIR__) . "/ConfigFiles/DataBaseConfig.php");
require(dirname(__DIR__) . "/Exceptions/PGException.php");
require(dirname(__DIR__) . "/tools.php");
require "CookieManager.php";
require "Verifications.php";
require(dirname(__DIR__) . "/constants.php");

class DataBase
{
	protected PgSql\Connection $connect;
	private string $servername;
	private string $username;
	private string $password;
	private string $dbname;
	private string $port;
	private CookieManager $cookieManager;

	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		$dbc = new DataBaseConfig();
		$this->servername = $dbc->servername;
		$this->username = $dbc->username;
		$this->password = $dbc->password;
		$this->dbname = $dbc->databasename;
		$this->port = $dbc->port;
		$this->dbConnect();
		$this->cookieManager = new CookieManager();
	}

	/**
	 * @throws PGException
	 */
	function dbConnect(): void
	{
		$connection_string = sprintf("host = %s port = %s dbname = %s user = %s password = %s", $this->servername, $this->port, $this->dbname, $this->username, $this->password);
		$this->connect = pg_pconnect($connection_string);
		if(!$this->connect){
			throw new PGException(pg_last_error());
		}
	}

	function prepareData($data): string
	{
		return pg_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
	}

	function checkCookie(): bool{
		return $this->cookieManager->isValidCookie();
	}

	/**
	 * @throws PGException
	 */
	private function checkQueryResult($result, $errorMessage=""): void
	{
		if(!$result){
			if(strlen($errorMessage) == 0){
				$errorMessage = pg_last_error();
			}
			throw new PGException($errorMessage);
		}
	}

	/**
	 * @throws PGException
	 */
	function usernameInUse($username): bool{
		$sql = sprintf("SELECT COUNT(username) FROM Logins WHERE username = '%s'", $this->prepareData($username));
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0, 0) == 1;
	}

	/**
	 * @throws PGException
	 */
	function emailInUse($email): bool{
		$sql = sprintf("SELECT COUNT(email) FROM Customers WHERE email = '%s'", $this->prepareData($email));
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0, 0) != 0;
	}

	/**
	 * @throws PGException
	 * @throws InvalidArgumentException
	 */
	function logIn($username, $password): string|false
	{
		$defaultErrorMessage = "Incorrect username/password.";
		if(!$this->cookieManager->isValidCookie()){
			$username = $this->prepareData($username);
			$password = $this->prepareData($password);
			$sql = sprintf("SELECT * FROM Logins WHERE username = '%s'", $username);
			$result = pg_query($this->connect, $sql);

			$this->checkQueryResult($result, $defaultErrorMessage);

			$row = pg_fetch_assoc($result);
			$affected_rows = pg_affected_rows($result);
			if ($affected_rows == 0) {
				$pg_error = pg_last_error();
				if(strlen($pg_error) == 0){
					$pg_error = $defaultErrorMessage;
				}
				throw new PGException($pg_error);
			}

			if ($affected_rows > 1){ // Look into blocking this IP
				throw new InvalidArgumentException("Don't even try to inject this.");
			}

			$dbusername = $row['username'];
			$dbpassword = $row['password'];

			if (!($dbusername == $username && password_verify($password, $dbpassword))) {
				throw new InvalidArgumentException($defaultErrorMessage);
			}
		} else{
			$sql = sprintf("SELECT * FROM Logins WHERE username = '%s'", $username);
			$result = pg_query($this->connect, $sql);
			$this->checkQueryResult($result);

			$row = pg_fetch_assoc($result);
			if (pg_affected_rows($result) == 0){
				throw new PGException(pg_last_error());
			}
		}

		$user_id = $row["id"];
		$sql = sprintf("SELECT authenticated_email FROM Customers WHERE id = %s", $user_id);
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);
		if (pg_affected_rows($result) == 0) {
			header("Response: You do not have an account with us, please create one at " . HTTPS_HOST . "/signup.");
			return false;
		}
		$row = convert_to_bool(pg_fetch_result($result, 0, 0));
		if(!$row){
			header("Response: You must verify your email before you log in.");
			return false;
		}

		$this->cookieManager->createCookie($username);
		return "Logged In Successfully";
	}

	/**
	 * @throws PGException
	 */
	function signUp($fullname, $email, $username, $password, $phone, $address_number, $direction, $streetname, $city, $state, $zipcode, $apt, $branch) : bool
	{
		#region Data preparation
		$fullname = $this->prepareData($fullname);
		$password = $this->prepareData($password);
		$email = $this->prepareData($email);
		$username = $this->prepareData($username);
		$password = password_hash($this->prepareData($password), CRYPT_SHA512);
		$phone = $this->prepareData($phone);
		$branch = $this->prepareData($branch);
		$address_id = $this->createAddress($address_number, $direction, $streetname, $city, $state, $zipcode, $apt);
		#endregion

		# region Getting the branch id
		$sql = sprintf("SELECT id FROM Branch WHERE name = '%s'", $branch);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		if(pg_num_rows($result) == 0){
			throw new InvalidArgumentException(sprintf("The branch with the name \"%s\" could not be found", $branch));
		}
		$branch_id = pg_fetch_result($result, 0, 0);

		# endregion

		$sql = sprintf("INSERT INTO Customers(name,email,phone,home_branch,address) VALUES ('%s','%s','%s',%s,%s) RETURNING id", $fullname, $email, $phone, $branch_id, $address_id);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		$user_id = pg_fetch_result($result, 0, 0);

		$result = pg_query($this->connect, sprintf("INSERT INTO Logins VALUES (%s,'%s','%s')", $user_id, $username, $password));
		$this->checkQueryResult($result);

		$this->cookieManager->createCookie($username);

		$verification = new Verifications();
		$verification->send_verification_email($email, $fullname);
		return true;
	}

	/**
	 * @throws PGException
	 */
	function postAddress($id, $streetNumber, $direction, $streetName, $city, $state, $zipcode): string{
		$streetNumber = $this->prepareData($streetNumber);
		$direction = $this->prepareData($direction);
		$streetName = $this->prepareData($streetName);
		$city = $this->prepareData($city);
		$state = $this->prepareData($state);
		$zipcode = $this->prepareData($zipcode);

		if($id == null){
			$row = $this->createAddress($streetNumber, $direction, $streetName, $city, $state, $zipcode);
			$this->checkQueryResult($row);
			return $this->parseAddress($row);
		}

		$result = pg_query($this->connect, sprintf("SELECT * FROM addresses WHERE id = %s", $id));
		$this->checkQueryResult($result);
		if(pg_affected_rows($result) == 0){
			return $this->parseAddress($this->createAddress($streetNumber, $direction, $streetName, $city, $state, $zipcode));
		}
		return $this->parseAddress($this->updateAddress($id, $streetNumber, $direction, $streetName, $city, $state, $zipcode));
	}

	private function parseAddress($row): string{
		return sprintf("=%s=%s=%s=%s=%s=%s=%s=", $row["id"], $row["number"], $row["direction"], $row["street_name"], $row["city"], $row["state"], $row["zipcode"]);
	}

	/**
	 * @throws PGException
	 */
	private function updateAddress($id, $streetNumber, $direction, $streetName, $city, $state, $zipcode): array {
		$streetNumber = $this->prepareData($streetNumber);
		$direction = $this->prepareData($direction);
		$streetName = $this->prepareData($streetName);
		$city = $this->prepareData($city);
		$state = $this->prepareData($state);
		$zipcode = $this->prepareData($zipcode);

		$dct = array(
			"number" => $streetNumber,
			"direction" => $direction,
			"street_name" => $streetName,
			"city" => $city,
			"state" => $state,
			"zipcode" => $zipcode
		);

		foreach($dct as $attribute => $value){
			if($value == null){
				continue;
			}
			//Checks if the value needs to be changed
			$check = pg_query($this->connect, sprintf("SELECT %s FROM Addresses WHERE id = %s", $attribute, $id));
			$this->checkQueryResult($check);
			$row = pg_fetch_assoc($check);
			if($row[$attribute] == $value){
				continue;
			}

			$this->checkQueryResult(pg_query($this->connect, sprintf("UPDATE Addresses SET %s = %s WHERE id = %s", $attribute, $value, $id)));
		}

		$result = pg_query($this->connect, sprintf("SELECT * FROM Addresses WHERE id = %s", $id));
		$this->checkQueryResult($result);
		return pg_fetch_assoc($result);
	}

	/**
	 * @throws PGException
	 */
	private function createAddress($address_number, $direction, $streetName, $city, $state, $zipcode, $apt): int
	{
		$address_number = $this->prepareData($address_number);
		$direction = $this->prepareData($direction);
		$streetName = $this->prepareData($streetName);
		$city = $this->prepareData($city);
		$state = $this->prepareData($state);
		$zipcode = $this->prepareData($zipcode);
		$apt = $this->prepareData($apt);

		$sql = sprintf("SELECT id FROM Addresses WHERE number = %s AND UPPER(direction::TEXT) = '%s' AND UPPER(street_name) = '%s' AND UPPER(city) = '%s' AND UPPER(state) = '%s' AND zipcode = '%s' AND UPPER(unitnumber) = '%s'",
			$address_number, strtoupper($direction), strtoupper($streetName), strtoupper($city), strtoupper($state), $zipcode, strtoupper($apt));

		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		$address_count = pg_num_rows($result);
		if($address_count == 0){ // Address isn't in the database, add it
			if(strlen($apt) == 0){
				$sql = sprintf("INSERT INTO Addresses(number, direction, street_name, city, state, zipcode) VALUES(%s,'%s','%s','%s','%s',%s) RETURNING id",
					$address_number, $direction, $streetName, $city, $state, $zipcode);
			} else{
				$sql = sprintf("INSERT INTO Addresses(number, direction, street_name, city, state, zipcode, unitnumber) VALUES(%s,'%s','%s','%s','%s',%s,'%s') RETURNING id",
					$address_number, $direction, $streetName, $city, $state, $zipcode, $apt);
			}
			$result = pg_query($this->connect, $sql);
			$this->checkQueryResult($result);
			if(pg_num_rows($result) == 0){
				throw new InvalidArgumentException("Something happened creating the address tuple");
			}
		}
		return pg_fetch_result($result, 0, 0);
	}

	public function isLoggedIn(): bool{
		return $this->cookieManager->isValidCookie();
	}

	/**
	 * @throws PGException
	 */
	public function getName(): string|false{
		$currentId = $this->getCurrentUserId();
		if(!$currentId){
			return false;
		}
		$sql = sprintf("SELECT name FROM Customers WHERE id = '%s' LIMIT 1", $currentId);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0);
	}

	public function getFirstName(): string|false{
		$name = $this->getName();
		if(!$name){
			return false;
		}
		return explode(" ", $name)[0];
	}

	public function logout(): void{
		$this->cookieManager->deleteCookie();
	}

	public function query($command): bool|Result
	{
		if(!str_starts_with($command, "SELECT")){
			return false;
		}
		return pg_query($this->connect, $command);
	}

	/**
	 * @throws PGException
	 */
	public function getCurrentUserId(): int|bool{
		$username = $this->cookieManager->getCookieUsername();
		if(!$username){
			return false;
		}
		$sql = sprintf("SELECT id FROM logins WHERE username = '%s' LIMIT 1", $username);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0);
	}
}
