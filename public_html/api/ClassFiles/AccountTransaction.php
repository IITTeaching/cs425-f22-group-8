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
	public function withdrawal(User|Teller|Manager $authorizer, Account $account, float $amount, string $description=""): float|false{
		if(!$this->checkAuthorization($authorizer, $account)){
			return false;
		}

		$result = $this->query(sprintf("SELECT withdrawal(%d,%f,'%s')", $account->getAccountNumber(), abs($amount), $description));
		$notice = pg_last_notice($this->connect);
		if(strlen($notice) != 0){
			pg_last_notice($this->connect, PGSQL_NOTICE_CLEAR);
			throw new PGException($notice);
		}

		return (float)pg_fetch_result($result, 0);
	}

	public function deposit(User|Teller|Manager $authorizer, Account $account, float $amount): float|false{
		if(!$this->checkAuthorization($authorizer, $account)){
			return false;
		}

		return $this->_depositWithoutCheck($account, $amount);
	}

	private function _depositWithoutCheck(Account $account, float $amount): float|false{
		$result = pg_fetch_assoc($this->query(sprintf("SELECT balance FROM Account WHERE number = %d", $account->getAccountNumber())));

		$result = pg_fetch_assoc($this->query(sprintf("UPDATE Account SET balance = %f WHERE number = %d RETURNING balance", $result["balance"] + $amount, $account->getAccountNumber())));
		$this->query(sprintf("INSERT INTO Transactions(account_number, type, amount, description) VALUES(%d,'Deposit',%f,'')", $account->getAccountNumber(), $amount));
		return $result["balance"];
	}

	public function transfer(User|Teller|Manager $authorizer, float $amount, Account $from, Account $to): float|false {
		$withdrawal = $this->withdrawal($authorizer, $from, $amount);
		if(!$withdrawal){
			return false;
		}
		return $this->_depositWithoutCheck($to, $withdrawal);
	}
}