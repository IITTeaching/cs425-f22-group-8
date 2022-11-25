<?php

require_once"Config.php";

class VerificationConfig extends Config
{
	public function getUserName(): string
	{
		return 'verifybot';
	}

	public function getPassword(): string
	{
		return "a12dd3a7fd3203a452eb34d91a9be20569d5e337a3384347068895c07f3e0c5a";
	}
}
