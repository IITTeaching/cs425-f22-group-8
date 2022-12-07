<?php

use PgSql\Result;

require_once (dirname(__DIR__) . "/ConfigFiles/Config.php");
require_once (dirname(__DIR__) . "/Exceptions/PGException.php");

class CS425Class
{
	protected PgSql\Connection|false $connect;

	/**
	 * @throws PGException
	 */
	public function __construct(Config|false $config)
	{
		if(!$config){
			$this->connect = false;
		}
		else{
			$this->dbConnect($config);
		}
	}

	public function __destruct(Exception $error=null){
		pg_close($this->connect);
		if(!is_null($error)){
			throw $error;
		}
	}

	/**
	 * @throws PGException
	 */
	private function dbConnect(Config $cfg): void
	{
		// $connection_string = sprintf("host = %s port = %d dbname = %s user = %s password = %s", $cfg->getHost(), $cfg->getPort(), $cfg->getDataBaseName(), $cfg->getUserName(), $cfg->getPassword());
		$this->connect = pg_pconnect($cfg->getConnectionString());
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

	public function query($query, $errorMessage=""): bool|Result
	{
		if(!$this->connect){ return false; }
		$result = pg_query($this->connect, $query);
		$this->checkQueryResult($result, $errorMessage);
		return $result;
	}

	/**
	 * Gets a single result from an SQL query
	 * @param $query
	 * @return false|string
	 */
	protected function getBasicResult($query){
		return pg_fetch_result($this->query($query), 0, 0);
	}
}