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

	public function getUserId(): int{
		return $this->id;
	}

	/**
	 * @return Account[]
	 */
	public function getAccounts(){
		$sql = sprintf("SELECT * FROM Account a WHERE holder = %d OR EXISTS(SELECT account_number FROM AuthorizedUsers WHERE owner_number = a.holder)");  # TODO: Once accounts have been generated, test to make sure this works.
		$result = pg_query($this->connect, $sql);
	}

	public function getNumberOfAccounts(): int{
		$result = pg_query($this->connect, sprintf("SELECT COUNT(*) FROM Account WHERE holder = %d", $this->id));
		$this->checkQueryResult($result);
		$owned = pg_fetch_result($result, 0, 0);
		$result = pg_query($this->connect, sprintf("SELECT COUNT(*) FROM AuthorizedUsers WHERE owner_number = %d", $this->id));
		$this->checkQueryResult($result);
		$authorized_user = pg_fetch_result($result, 0, 0);
		return $owned + $authorized_user;
	}
}