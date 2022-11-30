<?php

require_once "Config.php";

class TellerConfig extends Config
{
	protected function getUserName(): string
	{
		return 'tellerbot';
	}

	protected function getPassword(): string
	{
		return "11c8f9062973b50f228286368332495df3938e8902c87a2a4d738d7755c32039";
	}
}
