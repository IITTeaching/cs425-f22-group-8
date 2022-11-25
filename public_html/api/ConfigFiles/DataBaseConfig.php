<?php

require "Config.php";

class DataBaseConfig extends Config
{
	public function getUserName(): string
	{
		return 'bankbot';
	}

	public function getPassword(): string
	{
		return "b299ecdcc0c02a319625205a804991255fdff2470dddbc7fa05c0c3934dbffae";
	}
}
