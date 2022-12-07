<?php

require_once "Config.php";

class AuthConfig extends Config
{
	protected function getUserName(): string
	{
		return 'authbot';
	}

	protected function getPassword(): string
	{
		return "bdf49c3c3882102fc017ffb661108c63a836d065888a4093994398cc55c2ea2f";
	}
}
