***REMOVED***
require "DataBaseConfig.php";

class DataBase
***REMOVED***
	public $connect;
	public $data;
	private $sql;
	protected string $servername;
	protected string $username;
	protected string $password;
	protected string $databasename;
	protected string $port;

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
***REMOVED***

	function dbConnect()
	***REMOVED***
		$connection_string = sprintf("host = %s port = %s dbname = %s user = %s password = %s", $this->servername, $this->port, $this->databasename, $this->username, $this->password);
		$this->connect = pg_pconnect($connection_string);
		return $this->connect;
***REMOVED***

	function prepareData($data): string
	***REMOVED***
		return pg_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
***REMOVED***

	function usernameInUse($username): bool***REMOVED***
		$this->sql = sprintf("SELECT username FROM Logins WHERE username = '%s'", $this->prepareData($username));
		$result = pg_query($this->connect, $this->sql);
		return pg_affected_rows($result) != 0;
***REMOVED***

	function emailInUse($email): bool***REMOVED***
		$this->sql = sprintf("SELECT email FROM AccountHolders WHERE email = '%s'", $this->prepareData($email));
		$result = pg_query($this->connect, $this->sql);
		return pg_affected_rows($result) != 0;
***REMOVED***

	function logIn($username, $password)
	***REMOVED***
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$this->sql = sprintf("SELECT * FROM Logins WHERE username = '%s'", $username);
		$result = pg_query($this->connect, $this->sql);
		$row = pg_fetch_assoc($result);
		if (pg_affected_rows($result) != 0) ***REMOVED***
			$dbusername = $row['username'];
			$dbpassword = $row['password'];
			if ($dbusername == $username && password_verify($password, $dbpassword)) ***REMOVED***
				$this->sql = sprintf("SELECT * FROM AccountHolders WHERE id = %s", $row["id"]);
				$result = pg_query($this->connect, $this->sql);
				$row = pg_fetch_assoc($result);
				if (pg_affected_rows($result) == 0) ***REMOVED*** return false;***REMOVED***
				return sprintf("=%s=,=%s=,=%s=", $row["id"], $row["fullname"], $row["email"]);
		***REMOVED***
			return false;
	***REMOVED***
		return false;
***REMOVED***

	function signUp($fullname, $email, $address, $username, $password)
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
			return false;
	***REMOVED***

		$result = pg_query($this->connect, sprintf("SELECT id FROM AccountHolders WHERE email = '%s'", $email));
		$row = pg_fetch_assoc($result);
		if(pg_affected_rows($result) == 0)***REMOVED***
			return false;
	***REMOVED***

		// TODO: Get the row created in AccountHolders to grab the id and use it
		$this->sql = sprintf("INSERT INTO Logins VALUES ('%s','%s','%s')", $row["id"], $username, $password);
		if (!pg_query($this->connect, $this->sql)) ***REMOVED***
			// TODO: If return false, make sure the holder info wasn't added
			return false;
	***REMOVED***

		return true;
***REMOVED***
***REMOVED***
