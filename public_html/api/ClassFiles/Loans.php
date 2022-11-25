<?php

require "CS425Class.php";
require(dirname(__DIR__) . "/ConfigFiles/LoanConfig.php");
require(dirname(__DIR__) . "/constants.php");

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