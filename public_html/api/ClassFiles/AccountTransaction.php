<?php

require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/TransactionsConfig.php");
require_once (dirname(__DIR__) . "/Exceptions/PGException.php");


class AccountTransaction extends CS425Class
{
	public function __construct()
	{
		parent::__construct(new TransactionsConfig());
	}

	private function checkAccountExists(Account $account): bool{
		$result = $this->query(sprintf("SELECT COUNT(number) FROM Account WHERE number = %d", $account->getAccountNumber()));
		return pg_fetch_result($result, 0) == 0;
	}

	private function checkAuthorization(User|Teller|Manager $authorizer, Account $account): bool{
		if(!$this->checkAccountExists($account)){
			return false;
		}

		if(!($authorizer instanceof User) && !($authorizer instanceof Manager) && !($authorizer instanceof Teller)){
			throw new InvalidArgumentException("Only a teller, manager, and user (for their accounts) can withdrawal, not %s", $authorizer::class);
		}

		if($authorizer instanceof User){
			$result = $this->query(sprintf("SELECT (d.owner_count + f.auth_count) AS count FROM
    			(SELECT COUNT(balance) AS owner_count FROM Account WHERE number = %d AND holder = %d) d,
    			(SELECT COUNT(*) AS auth_count FROM AuthorizedUsers WHERE account_number = %d AND owner_number = %d) f",
				$account->getAccountNumber(), $authorizer->getUserId(), $account->getAccountNumber(), $authorizer->getUserId()));
			if(pg_fetch_result($result, 0) == 0){
				return false;
			}
		}

		return true;
	}

	/**
	 * @throws PGException
	 */
	private function runTransactionFunction(string $query){
		$result = $this->query($query);
		$notice = pg_last_notice($this->connect);
		if(strlen($notice) != 0){
			pg_last_notice($this->connect, PGSQL_NOTICE_CLEAR);
			throw new PGException($notice);
		}

		return (float)pg_fetch_result($result, 0);
	}

	/**
	 * @throws PGException
	 */
	public function withdrawal(User|Teller|Manager $authorizer, Account $account, float $amount, string $description=""): float|false{
		if($amount == 0){
			return 0;
		}

		if(!$this->checkAuthorization($authorizer, $account)){
			return false;
		}

		$query = sprintf("SELECT withdrawal(%d,%f,'%s')", $account->getAccountNumber(), abs($amount), $description);
		return $this->runTransactionFunction($query);
	}

	/**
	 * @throws PGException
	 */
	public function deposit(User|Teller|Manager $authorizer, Account $account, float $amount, string $description=""): float|false{
		if($amount == 0){
			return 0;
		}

		if(!$this->checkAuthorization($authorizer, $account)){
			return false;
		}

		$query = sprintf("SELECT deposit(%d,%f,'%s')", $account->getAccountNumber(), abs($amount), $description);
		return $this->runTransactionFunction($query);
	}

	public function transfer(User|Teller|Manager $authorizer, float $amount, Account $from, Account $to): float|false {
		$withdrawal = $this->withdrawal($authorizer, $from, $amount, "From"); // TODO: Fill these in
		if(!$withdrawal || $withdrawal == 0){
			return false;
		}
		$query = sprintf("SELECT deposit(%d,%f,'%s')", $to->getAccountNumber(), abs($withdrawal), "TO");
		return $this->runTransactionFunction($query);
	}
}