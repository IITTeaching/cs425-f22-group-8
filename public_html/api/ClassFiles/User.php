<?php

require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ProfileConfig.php");
require_once (dirname(__DIR__) . "/ConfigFiles/VerificationConfig.php");
require_once "Account.php";
require_once "Loan.php";

class User extends CS425Class
{
	private int $id;

	/**
	 * @throws PGException
	 */
	public function __construct($id){
		parent::__construct(new ProfileConfig());

		$result = $this->query(sprintf("SELECT COUNT(*) FROM Customers WHERE id = %d", $id));
		if(pg_fetch_result($result, 0) == 0){
			throw new InvalidArgumentException(sprintf("No user exists ID'd %d.", $id));
		}

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
		$result = $this->query(sprintf("SELECT number FROM Account a WHERE holder = %d OR number = (SELECT account_number FROM AuthorizedUsers WHERE owner_number = %d)", $this->getUserId(), $this->getUserId()));
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
		$result = $this->query(sprintf("SELECT loan_number FROM LoanApprovals WHERE customer_id = %d", $this->getUserId()));
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
		return $this->getBasicResult(sprintf("SELECT COUNT(loan_number) FROM LoanApprovals WHERE customer_id = %d", $this->id));
	}

	/**
	 * @throws PGException
	 */
	public static function fromUsername(string $username): User|false{
		$db = new CS425Class(new VerificationConfig());
		$username = $db->prepareData($username);
		$result = $db->query(sprintf("SELECT id FROM Logins WHERE username = '%s'", $username));
		if(pg_affected_rows($result) == 0){
			return false;
		}
		return new User(pg_fetch_result($result, 0));
	}
}