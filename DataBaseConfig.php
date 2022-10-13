<?php

class DataBaseConfig
{
	public string $servername;
	public string $username;
	public string $password;
	public string $databasename;
	public string $port;

	public function __construct()
	{
		$this->servername = 'localhost';
		$this->username = 'bankbot';
		$this->password = 'b299ecdcc0c02a319625205a804991255fdff2470dddbc7fa05c0c3934dbffae';
		$this->databasename = 'cs425';
		$this->port = '5078';
	}
}
