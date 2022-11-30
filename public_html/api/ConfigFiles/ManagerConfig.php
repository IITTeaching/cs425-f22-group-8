<?php

require_once "Config.php";

class ManagerConfig extends Config
{
	protected function getUserName(): string
	{
		return 'managerbot';
	}

	protected function getPassword(): string
	{
		return "987bfb848c2403e638bf794771a1c3ffcda8cb1a312b07f38a376a6be35e9feb";
	}
}
