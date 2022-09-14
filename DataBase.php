***REMOVED***
require "DataBaseConfig.php";

class DataBase
***REMOVED***
	public $connect;
	public $data;
	private $sql;
	protected $servername;
	protected $username;
	protected $password;
	protected $databasename;
	protected $port;

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
		$connection_string = sprintf("host=%s port=%s dbname=%s user=%s password=%s", $this->servername, $this->port, $this->databasename, $this->username, $this->password);
		$this->connect = pg_connect($connection_string);
		return $this->connect;
***REMOVED***

	function prepareData($data)
	***REMOVED***
		return pg_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
***REMOVED***

	function usernameInUse($username)***REMOVED***
		$this->sql = sprintf("SELECT username FROM AccountHolders WHERE username = '%s'", $this->prepareData($username));
		$result = pg_query($this->connect, $this->sql);
		return pg_affected_rows($result) != 0;
***REMOVED***

	function emailInUse($email)***REMOVED***
		$this->sql = sprintf("SELECT email FROM AccountHolders WHERE email = '%s'", $this->prepareData($email));
		$result = pg_query($this->connect, $this->sql);
		return pg_affected_rows($result) != 0;
***REMOVED***

	function logIn($table, $username, $password)
	***REMOVED***
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$this->sql = sprintf("SELECT * FROM %s WHERE username = '%s'", $table, $username);
		$result = pg_query($this->connect, $this->sql);
		$row = pg_fetch_assoc($result);
		if (pg_affected_rows($result) != 0) ***REMOVED***
			$dbusername = $row['username'];
			$dbpassword = $row['password'];
			if ($dbusername == $username && password_verify($password, $dbpassword)) ***REMOVED***
				$login = true;
		***REMOVED*** else $login = false;
	***REMOVED*** else $login = false;

		return $login;
***REMOVED***

	function signUp($table, $fullname, $email, $username, $password)
	***REMOVED***
		$fullname = $this->prepareData($fullname);
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$email = $this->prepareData($email);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$this->sql = sprintf("INSERT INTO %s (fullname, username, password, email) VALUES ('%s','%s','%s','%s')", $table, $fullname, $username, $password, $email);
		if (pg_query($this->connect, $this->sql)) ***REMOVED***
			return true;
	***REMOVED*** else return false;
***REMOVED***

***REMOVED***
?>