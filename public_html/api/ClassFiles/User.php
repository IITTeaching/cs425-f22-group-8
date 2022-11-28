<?php

require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/UserConfig.php");
require_once "Account.php";
require_once "Loan.php";

class User extends CS425Class
{
	private int $id;

	/**
	 * @throws PGException
	 */
	public function __construct($id){
		parent::__construct(new UserConfig());
		$this->id = $id;
	}

	/**
	 * @throws PGException
	 */
	public function getName(): string|false{
		return $this->getBasicResult(sprintf("SELECT name FROM Customers WHERE id = '%s' LIMIT 1", $this->id));
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
	 * @throws PGException
	 */
	public function getAccounts(){
		$sql = sprintf("SELECT number FROM Account a WHERE holder = %d OR number = (SELECT account_number FROM AuthorizedUsers WHERE owner_number = %d)", $this->getUserId(), $this->getUserId());
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		$accounts = array();
		if(pg_affected_rows($result) != 0){
			while ($row = pg_fetch_array($result)) {
				$accounts[] = new Account($row["number"]);
			}
		}
		return $accounts;
	}

	/**
	 * @return Loan[]
	 * @throws PGException
	 */
	public function getLoans(){
		$sql = sprintf("SELECT loan_number FROM LoanApprovals WHERE customer_id = %d", $this->getUserId());
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		$loans = array();
		if(pg_affected_rows($result) != 0){
			while($row = pg_fetch_array($result)){
				$loans[] = new Loan($row["number"]);
			}
		}
		return $loans;
	}

	public function getNumberOfAccounts(): int{
		$owned = $this->getBasicResult(sprintf("SELECT COUNT(*) FROM Account WHERE holder = %d", $this->id));
		$authorized_user = $this->getBasicResult(sprintf("SELECT COUNT(*) FROM AuthorizedUsers WHERE owner_number = %d", $this->id));
		return $owned + $authorized_user;
	}

	public function getNumberOfLoans(): int{
		$this->getBasicResult(sprintf("SELECT COUNT(loan_number) FROM LoanApprovals WHERE customer_id = %d", $this->id));
	}
}