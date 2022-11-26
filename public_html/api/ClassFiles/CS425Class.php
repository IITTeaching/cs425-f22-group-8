<?php

require_once (dirname(__DIR__) . "/ConfigFiles/Config.php");

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
	private function dbConnect(Config $cfg): void
	{
		$connection_string = sprintf("host = %s port = %d dbname = %s user = %s password = %s", $cfg->getHost(), $cfg->getPort(), $cfg->getDataBaseName(), $cfg->getUserName(), $cfg->getPassword());
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

	/**
	 * Gets a single result from an SQL query
	 * @param $query
	 * @return false|string
	 */
	protected function getBasicResult($query){
		$result = pg_query($this->connect, $query);
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0, 0);
	}
}