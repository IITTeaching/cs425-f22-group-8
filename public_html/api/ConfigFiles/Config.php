<?php

abstract class Config
{
	public function __construct()
	{
	}

	abstract public function getUserName() : string;
	abstract public function getPassword() : string;

	public function getHost(): string {
		return "localhost";
	}

	public function getDataBaseName(): string {
		return "cs425";
	}

	public function getPort(): int {
		return 5078;
	}
	
	public function getConnectionString(): string{
		return sprintf("host = %s port = %d dbname = %s user = %s password = %s",
			$this->getHost(), $this->getPort(), $this->getDataBaseName(), $this->getUserName(), $this->getPassword());
	}
}
