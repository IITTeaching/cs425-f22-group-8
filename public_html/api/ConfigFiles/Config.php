<?php

abstract class Config
{
	public function __construct()
	{
		$this->servername = 'localhost';
		$this->username = 'bankbot';
		$this->password = 'b299ecdcc0c02a319625205a804991255fdff2470dddbc7fa05c0c3934dbffae';
		$this->databasename = 'cs425';
		$this->port = '5078';
	}

	abstract public function getUserName();
	abstract public function getPassword();

	public function getServerName(){
		return "localhost";
	}

	public function getDataBaseName(){
		return "cs425";
	}

	public function getPort(){
		return "5078";
	}
}
