<?php

use PgSql\Result;

require_once (dirname(__DIR__) . "/ConfigFiles/DataBaseConfig.php");
require_once (dirname(__DIR__) . "/Exceptions/PGException.php");
require_once (dirname(__DIR__) . "/tools.php");
require_once "CookieManager.php";
require_once "User.php";
require_once "Verifications.php";
require_once "CS425Class.php";
require_once "Authentication.php";
require_once (dirname(__DIR__) . "/constants.php");

class DataBase extends CS425Class
{
	private CookieManager $cookieManager;
	private Authentication $authenticator;

	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		parent::__construct(new DataBaseConfig());
		$this->cookieManager = new CookieManager();
		$this->authenticator = new Authentication();
	}

	function checkCookie(): bool{
		return $this->cookieManager->isValidCookie();
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
	function logIn($username, $password, $authcode=""): string|false
	{
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$authcode = $this->prepareData($authcode);
		$defaultErrorMessage = "Incorrect username/password.";
		if(!$this->cookieManager->isValidCookie()){
			$sql = sprintf("SELECT * FROM Logins WHERE username = '%s'", $username);
			$result = pg_query($this->connect, $sql);

			$this->checkQueryResult($result, $defaultErrorMessage);

			$row = pg_fetch_assoc($result);
			$affected_rows = pg_affected_rows($result);
			if ($affected_rows == 0) {
				$pg_error = pg_last_error();
				if(strlen($pg_error) == 0){
					if(!$this->employeeLogin($username, $password, $authcode)){
						$pg_error = $defaultErrorMessage;
					} else{
						return "Employee Logged In Successfully";
					}
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
			}  # TODO: Implement cookies for employees
		}

		$user_id = $row["id"];
		$sql = sprintf("SELECT authenticated_email FROM Customers WHERE id = %s", $user_id);
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);
		if (pg_affected_rows($result) == 0) {
			// The login is for an employee
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

	function employeeLogin($username, $password, $authcode): string|false{
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$authcode = (int)$this->prepareData($authcode);
		$result = pg_query($this->connect, sprintf("SELECT username, password, totp_secret FROM EmployeeLogins WHERE username = %s", $username));
		$affected_rows = pg_affected_rows($result);
		if($affected_rows != 0){
			return false;
		}
		$row = pg_fetch_assoc($result, 0, 0);
		if (!($row["username"] == $username && password_verify($password, $row["password"]))) {
			throw new InvalidArgumentException("Invalid username or password.");
		}
		if(!$this->authenticator->checkTOTP($username, $authcode, true)){
			header("Response: Incorrect 2 Factor Authentication.");
			return false;
		}
		$this->cookieManager->createCookie($username);
		return "Employee Logged In";
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
	public function getCurrentUserId(): User|false {
		$username = $this->cookieManager->getCookieUsername();
		if(!$username){ return false; }
		$sql = sprintf("SELECT id FROM logins WHERE username = '%s' LIMIT 1", $username);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return new User(pg_fetch_result($result, 0));
	}
}
