<?php

require_once"CS425Class.php";
require_oncedirname(__DIR__) . "/ConfigFiles/LoanConfig.php");
require_oncedirname(__DIR__) . "/constants.php");

class Loans extends CS425Class
{
	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		parent::__construct(new LoanConfig());
	}

}