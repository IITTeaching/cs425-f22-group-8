<?php

abstract class Config
{
	public function __construct()
	{
	}

	abstract protected function getUserName() : string;
	abstract protected function getPassword() : string;

	private function getHost(): string {
		return "localhost";
	}

	private function getDataBaseName(): string {
		return "cs425";
	}

	private function getPort(): int {
		return 5078;
	}
	
	public function getConnectionString(): string{
		return sprintf("host = %s port = %d dbname = %s user = %s password = %s",
			$this->getHost(), $this->getPort(), $this->getDataBaseName(), $this->getUserName(), $this->getPassword());
	}
}
