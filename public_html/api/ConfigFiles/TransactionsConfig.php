<?php

require_once "Config.php";

class TransactionsConfig extends Config
{
	protected function getUserName(): string
	{
		return 'transactionsbot';
	}

	protected function getPassword(): string
	{
		return "81dc075c3d55230215300137991a25f90be4c243a55580fe2af7538774147bd6";
	}
}
