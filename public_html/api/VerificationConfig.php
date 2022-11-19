<?php

class VerificationConfig
{
	public string $servername;
	public string $username;
	public string $password;
	public string $databasename;
	public string $port;

	public function __construct()
	{
		$this->servername = 'localhost';
		$this->username = 'verifybot';
		$this->password = 'a12dd3a7fd3203a452eb34d91a9be20569d5e337a3384347068895c07f3e0c5a';
		$this->databasename = 'cs425';
		$this->port = '5078';
	}
}
