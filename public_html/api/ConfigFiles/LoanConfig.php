<?php

require_once "Config.php";

class LoanConfig extends Config
{
	protected function getUserName(): string
	{
		return "loanbot";
	}

	protected function getPassword(): string
	{
		return "669e2e48e6abe564fda82128f42e15609c22778d1b47c22960bba05799bfdc7a";
	}
}
