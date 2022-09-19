***REMOVED***
require "DataBaseConfig.php";
require "PGException.php";
require "CookieManager.php";

class DataBase
***REMOVED***
	public $connect;
	public $data;
	private $sql;
	private string $servername;
	private string $username;
	private string $password;
	private string $databasename;
	private string $port;
	private CookieManager $cookieManager;

	/**
	 * @throws PGException
	 */
***REMOVED***
	***REMOVED***
		$this->connect = null;
		$this->data = null;
		$this->sql = null;
		$dbc = new DataBaseConfig();
		$this->servername = $dbc->servername;
		$this->username = $dbc->username;
		$this->password = $dbc->password;
		$this->databasename = $dbc->databasename;
		$this->port = $dbc->port;
		$this->dbConnect();
		$this->cookieManager = new CookieManager("Some random key");
		echo "Database created: <br>";
***REMOVED***

	/**
	 * @throws PGException
	 */
	function dbConnect(): void
	***REMOVED***
		$connection_string = sprintf("host = %s port = %s dbname = %s user = %s password = %s", $this->servername, $this->port, $this->databasename, $this->username, $this->password);
		$this->connect = pg_pconnect($connection_string);
		if(!$this->connect)***REMOVED***
			throw new PGException(pg_last_error());
	***REMOVED***
***REMOVED***

	function prepareData($data): string
	***REMOVED***
		return pg_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
***REMOVED***

	/**
	 * @throws PGException
	 */
	private function checkQueryResult($result): void
	***REMOVED***
		if(!$result)***REMOVED***
			throw new PGException(pg_last_error());
	***REMOVED***
***REMOVED***

	function _print($statement): void
	***REMOVED***
		echo $statement . "<br>";
***REMOVED***

	/**
	 * @throws PGException
	 */
	function usernameInUse($username): bool***REMOVED***
		$sql = sprintf("SELECT username FROM Logins WHERE username = '%s'", $this->prepareData($username));
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);

		return pg_affected_rows($result) != 0;
***REMOVED***

	/**
	 * @throws PGException
	 */
	function emailInUse($email): bool***REMOVED***
		$sql = sprintf("SELECT email FROM AccountHolders WHERE email = '%s'", $this->prepareData($email));
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);

		return pg_affected_rows($result) != 0;
***REMOVED***

	/**
	 * @throws PGException
	 * @throws InvalidArgumentException
	 */
	function logIn($username, $password): string|bool
	***REMOVED***
		if(!$this->cookieManager->isValidCookie())***REMOVED***
			$username = $this->prepareData($username);
			$password = $this->prepareData($password);
			$sql = sprintf("SELECT * FROM Logins WHERE username = '%s'", $username);
			$result = pg_query($this->connect, $sql);

			$this->checkQueryResult($result);

			$row = pg_fetch_assoc($result);
			if (pg_affected_rows($result) == 0) ***REMOVED***
				throw new PGException(pg_last_error());
		***REMOVED***

			$dbusername = $row['username'];
			$dbpassword = $row['password'];

			if (!($dbusername == $username && password_verify($password, $dbpassword))) ***REMOVED***
				throw new InvalidArgumentException("Incorrect username/password");
		***REMOVED***
	***REMOVED*** else***REMOVED***
			$sql = sprintf("SELECT * FROM Logins WHERE username = '%s'", $username);
			$result = pg_query($this->connect, $sql);
			$this->checkQueryResult($result);

			$row = pg_fetch_assoc($result);
			if (pg_affected_rows($result) == 0)***REMOVED***
				throw new PGException(pg_last_error());
		***REMOVED***
	***REMOVED***

		$sql = sprintf("SELECT * FROM AccountHolders WHERE id = %s", $row["id"]);
		$result = pg_query($this->connect, $sql);

		$this->checkQueryResult($result);
		$row = pg_fetch_assoc($result);
		if (pg_affected_rows($result) == 0) ***REMOVED*** return false;***REMOVED***
		$this->cookieManager->createCookie($username);
		return sprintf("=%s=,=%s=,=%s=", $row["id"], $row["fullname"], $row["email"]);
***REMOVED***

	/**
	 * @throws PGException
	 */
	function signUp($fullname, $email, $address, $username, $password) : bool
	***REMOVED***
		$fullname = $this->prepareData($fullname);
		$address = $this->prepareData($address);
		$password = $this->prepareData($password);
		$email = $this->prepareData($email);
		$username = $this->prepareData($username);
		$password = password_hash($this->prepareData($password), CRYPT_SHA512);

		$this->sql = sprintf("INSERT INTO AccountHolders(fullname, address_id, email) VALUES ('%s',%s,'%s')", $fullname, $address, $email);
		if (!pg_query($this->connect, $this->sql)) ***REMOVED***
			// TODO: If return false, make sure the holder info wasn't added
			throw new PGException(pg_last_error());
	***REMOVED***

		$result = pg_query($this->connect, sprintf("SELECT id FROM AccountHolders WHERE email = '%s'", $email));
		$this->checkQueryResult($result);

		$row = pg_fetch_assoc($result);
		if(pg_affected_rows($result) == 0)***REMOVED***
			throw new PGException(pg_last_error());
	***REMOVED***

		// TODO: Get the row created in AccountHolders to grab the id and use it
		if (!pg_query($this->connect, sprintf("INSERT INTO Logins VALUES ('%s','%s','%s')", $row["id"], $username, $password))) ***REMOVED***
			// TODO: If return false, make sure the holder info wasn't added
			throw new PGException(pg_last_error());
	***REMOVED***

		$this->cookieManager->createCookie($username);
		return true;
***REMOVED***

	/**
	 * @throws PGException
	 */
	function postAddress($id, $streetNumber, $direction, $streetName, $city, $state, $zipcode): string***REMOVED***
		$streetNumber = $this->prepareData($streetNumber);
		$direction = $this->prepareData($direction);
		$streetName = $this->prepareData($streetName);
		$city = $this->prepareData($city);
		$state = $this->prepareData($state);
		$zipcode = $this->prepareData($zipcode);

		if($id == null)***REMOVED***
			$row = $this->createAddress($streetNumber, $direction, $streetName, $city, $state, $zipcode);
			$this->checkQueryResult($row);
			return $this->parseAddress($row);
	***REMOVED***

		$result = pg_query($this->connect, sprintf("SELECT * FROM addresses WHERE id = %s", $id));
		$this->checkQueryResult($result);
		if(pg_affected_rows($result) == 0)***REMOVED***
			return $this->parseAddress($this->createAddress($streetNumber, $direction, $streetName, $city, $state, $zipcode));
	***REMOVED***
		return $this->parseAddress($this->updateAddress($id, $streetNumber, $direction, $streetName, $city, $state, $zipcode));
***REMOVED***

	private function parseAddress($row): string***REMOVED***
		return sprintf("=%s=%s=%s=%s=%s=%s=%s=", $row["id"], $row["number"], $row["direction"], $row["street_name"], $row["city"], $row["state"], $row["zipcode"]);
***REMOVED***

	/**
	 * @throws PGException
	 */
	private function updateAddress($id, $streetNumber, $direction, $streetName, $city, $state, $zipcode): PgSql\Result ***REMOVED***
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

		foreach($dct as $attribute => $value)***REMOVED***
			if($value == null)***REMOVED***
				continue;
		***REMOVED***
			//Checks if the value needs to be changed
			$check = pg_query($this->connect, sprintf("SELECT %s FROM addresses WHERE id = %s", $attribute, $id));
			$this->checkQueryResult($check);
			$row = pg_fetch_assoc($check);
			if($row[$attribute] == $value)***REMOVED***
				continue;
		***REMOVED***

			$this->checkQueryResult(pg_query($this->connect, sprintf("UPDATE addresses SET %s = %s WHERE id = %s", $attribute, $value, $id)));
	***REMOVED***

		$result = pg_query($this->connect, sprintf("SELECT * FROM addresses WHERE id = %s", $id));
		$this->checkQueryResult($result);
		return pg_fetch_assoc($result);
***REMOVED***

	/**
	 * @throws PGException
	 */
	private function createAddress($streetNumber, $direction, $streetName, $city, $state, $zipcode): array
	***REMOVED***
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
***REMOVED***
***REMOVED***
