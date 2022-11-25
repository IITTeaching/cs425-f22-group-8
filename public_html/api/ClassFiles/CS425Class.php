<?php

require_once "../ConfigFiles/Config.php";

class CS425Class
{
	protected PgSql\Connection $connect;

	/**
	 * @throws PGException
	 */
	public function __construct(Config $config)
	{
		$this->dbConnect($config);
	}

	/**
	 * @throws PGException
	 */
	private function dbConnect(Config $config): void
	{
		$connection_string = sprintf("host = %s port = %s dbname = %s user = %s password = %s", $this->servername, $this->port, $this->dbname, $this->username, $this->password);
		$this->connect = pg_pconnect($connection_string);
		if(!$this->connect){
			throw new PGException(pg_last_error());
		}
	}

	protected function prepareData($data): string
	{
		return pg_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
	}

	/**
	 * @throws PGException
	 */
	protected function checkQueryResult($result, $errorMessage=""): void
	{
		if(!$result){
			if(strlen($errorMessage) == 0){
				$errorMessage = pg_last_error();
			}
			throw new PGException($errorMessage);
		}
	}
}