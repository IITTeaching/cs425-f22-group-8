<?php

require_once "Config.php";

class ProfileConfig extends Config
{
	protected function getUserName(): string
	{
		return 'profilebot';
	}

	protected function getPassword(): string
	{
		return "1900eab6c028483d7126599ee6f50de0d27907b5c65fa90524580b4b0f9852b0";
	}
}
