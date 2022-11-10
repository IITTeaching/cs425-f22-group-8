<?php
require "DataBaseConfig.php";
require "PGException.php";
require "CookieManager.php";

class DataBase
{
	protected PgSql\Connection $connect;
	private string $servername;
	private string $username;
	private string $password;
	private string $dbname;
	private string $port;
	private bool $loggedIn;
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
		$this->cookieManager = new CookieManager("Some random key");
		$this->loggedIn = false;
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

	function _print($statement): void
	{
		echo $statement . "<br>";
	}

	/**
	 * @throws PGException
	 */
	function usernameInUse($username): bool{
		$sql = sprintf("SELECT username FROM Logins WHERE username = '%s'", $this->prepareData($username));
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);

		return pg_affected_rows($result) != 0;
	}

	/**
	 * @throws PGException
	 */
	function emailInUse($email): bool{
		$sql = sprintf("SELECT email FROM AccountHolders WHERE email = '%s'", $this->prepareData($email));
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);

		return pg_affected_rows($result) != 0;
	}

	/**
	 * @throws PGException
	 * @throws InvalidArgumentException
	 */
	function logIn($username, $password): string|bool
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

		$sql = sprintf("SELECT * FROM AccountHolders WHERE id = %s", $row["id"]);
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);
		$row = pg_fetch_assoc($result);
		if (pg_affected_rows($result) == 0) { return false;}
		$this->cookieManager->createCookie($username);
		$this->loggedIn = true;
		return sprintf("=%s=,=%s=,=%s=", $row["id"], $row["fullname"], $row["email"]);
	}

	/**
	 * @throws PGException
	 */
	function signUp($fullname, $email, $address, $username, $password) : bool
	{
		$fullname = $this->prepareData($fullname);
		$address = $this->prepareData($address);
		$password = $this->prepareData($password);
		$email = $this->prepareData($email);
		$username = $this->prepareData($username);
		$password = password_hash($this->prepareData($password), CRYPT_SHA512);

		$sql = sprintf("INSERT INTO AccountHolders(fullname, address_id, email) VALUES ('%s',%s,'%s')", $fullname, $address, $email);
		if (!pg_query($this->connect, $sql)) {
			// TODO: If return false, make sure the holder info wasn't added
			throw new PGException(pg_last_error());
		}

		$result = pg_query($this->connect, sprintf("SELECT id FROM AccountHolders WHERE email = '%s'", $email));
		$this->checkQueryResult($result);

		$row = pg_fetch_assoc($result);
		if(pg_affected_rows($result) == 0){
			throw new PGException(pg_last_error());
		}

		// TODO: Get the row created in AccountHolders to grab the id and use it
		if (!pg_query($this->connect, sprintf("INSERT INTO Logins VALUES ('%s','%s','%s')", $row["id"], $username, $password))) {
			// TODO: If return false, make sure the holder info wasn't added
			throw new PGException(pg_last_error());
		}

		$this->cookieManager->createCookie($username);
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
			$check = pg_query($this->connect, sprintf("SELECT %s FROM addresses WHERE id = %s", $attribute, $id));
			$this->checkQueryResult($check);
			$row = pg_fetch_assoc($check);
			if($row[$attribute] == $value){
				continue;
			}

			$this->checkQueryResult(pg_query($this->connect, sprintf("UPDATE addresses SET %s = %s WHERE id = %s", $attribute, $value, $id)));
		}

		$result = pg_query($this->connect, sprintf("SELECT * FROM addresses WHERE id = %s", $id));
		$this->checkQueryResult($result);
		return pg_fetch_assoc($result);
	}

	/**
	 * @throws PGException
	 */
	private function createAddress($streetNumber, $direction, $streetName, $city, $state, $zipcode): array
	{
		$streetNumber = $this->prepareData($streetNumber);
		$direction = $this->prepareData($direction);
		$streetName = $this->prepareData($streetName);
		$city = $this->prepareData($city);
		$state = $this->prepareData($state);
		$zipcode = $this->prepareData($zipcode);

		$sql = sprintf("INSERT INTO addresses(number,direction,street_name,city,state,zipcode) VALUES(%s,'%s','%s','%s','%s','%s') RETURNING id", $streetNumber,$direction,$streetName,$city,$state,$zipcode);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		$row = pg_fetch_assoc($result);
		$id = $row["id"];

		$sql = sprintf("SELECT * FROM Addresses WHERE id = %s", $id);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);

		return pg_fetch_assoc($result);
	}

	public function isLoggedIn(): bool{
		return $this->loggedIn;  // This is going to need to check the cookie, because this is recalled on every time the page changes
	}
}
