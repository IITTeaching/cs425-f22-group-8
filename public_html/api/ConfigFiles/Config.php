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
}
