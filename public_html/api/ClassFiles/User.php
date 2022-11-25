<?php

require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ProfileConfig.php");

class User extends CS425Class
{
	private int $id;

	/**
	 * @throws PGException
	 */
	public function __construct($id){
		parent::__construct(new ProfileConfig());
		$this->id = $id;
	}

	/**
	 * @throws PGException
	 */
	public function getName(): string|false{
		$sql = sprintf("SELECT name FROM Customers WHERE id = '%s' LIMIT 1", $this->id);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0);
	}

	/**
	 * @throws PGException
	 */
	public function getFirstName(): string|false{
		$name = $this->getName();
		if(!$name){ return false; }
		return explode(" ", $name)[0];
	}

	public function getAccounts(): array{
		# TODO: Create a holder class for Accounts
	}

	/**
	 * @throws PGException
	 */
	public function getNumberOfAccounts(): bool|int{
		$result = pg_query($this->connect, sprintf("SELECT COUNT(*) FROM Account WHERE holder = %s", $this->id));
		$this->checkQueryResult($result);
		return pg_fetch_result($result, 0);
	}
}