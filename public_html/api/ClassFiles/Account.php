<?php

require_once "User.php";
require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ProfileConfig.php");
require_once (dirname(__DIR__) . "/tools.php");

class Account extends CS425Class
{
	private readonly int $account_number;

	public function __construct(){
		parent::__construct(new ProfileConfig());

		$argv = func_get_args();
		$argc = func_num_args();

		if($argc == 1) {
			call_user_func(array($this, "__init__"), $argv);
		} elseif ($argc == 4){
			call_user_func(array($this, "register"), $argv);
		} else{
			parent::__destruct(new InvalidArgumentException("The account constructor can only take 1 argument, the account number."));
		}
	}
	private function __init__($account_number){
		$this->account_number = (int)$this->prepareData($account_number);
	}

	private function register(User $creator, string $name, string $type, float $initial_balance=0){
		$name = $this->prepareData($name);
		$type = $this->prepareData($type);

		$possible_account_types = $this->query("SELECT * FROM get_account_types");
		$is_possible_account_type = false;
		while($account_type = pg_fetch_row($possible_account_types)){
			if(strtolower($account_type[0]) == strtolower($type)){
				$is_possible_account_type = true;
				break;
			}
		}
		if(!$is_possible_account_type){
			throw new InvalidArgumentException("The given account type was not valid.");
		}

		$result = $this->query(sprintf("INSERT INTO Account(holder, account_name, type, balance) VALUES(%d,'%s','%s',%f);",
			$creator->getUserId(), $name, $type, $initial_balance));
		// TODO: Add Result checking for register
	}

	/**
	 * @param User $user The user trying to set the balance.
	 * @param float $balance The new balance.
	 */
	public function setBalance(User $user, float $balance): float | false
	{
		$own_result = $this->query(sprintf("SELECT COUNT(*) FROM Account WHERE number = %d AND holder = %d", $this->account_number, $user->getUserId()));
		$auth_result = $this->query(sprintf("SELECT COUNT(*) FROM AuthorizedUsers WHERE account_number = %d AND owner_number = %d", $this->account_number, $user->getUserId()));

		if(pg_fetch_result($auth_result, 0, 0) == 0 and pg_fetch_result($own_result, 0, 0)){ return false; }

		$result = $this->query(sprintf("UPDATE Account SET balance = %f WHERE number = %d RETURNING balance", $balance, $this->account_number));
		return pg_fetch_result($result, 0, 0);
	}

	public function getBalance(): float{ return $this->getBasicResult(sprintf("SELECT balance FROM Account WHERE number = %d", $this->account_number)); }

	/**
	 * @return int
	 */
	public function getAccountNumber(): int { return $this->account_number; }

	public function setOwner(User $owner): bool {
		$result = $this->query(sprintf("UPDATE Account SET holder = %d WHERE number = %d RETURNING holder", $owner->getUserId(), $this->account_number));
		return pg_fetch_result($result, 0, 0) == $owner->getUserId();
	}

	public function getOwner(): User {
		$result = $this->query(sprintf("SELECT holder FROM Account WHERE number = %d", $this->account_number));
		return new User(pg_fetch_result($result, 0, 0));
	}

	public function setName(string $name){ $this->query(sprintf("UPDATE Account SET account_name = '%s' WHERE number = %d", $this->prepareData($name), $this->account_number)); }

	/**
	 * @return string|false
	 * @throws PGException
	 */
	public function getName(): string|false { return $this->getBasicResult(sprintf("SELECT account_name FROM Account WHERE number = %d", $this->account_number)); }

	public function getType(): string {	return $this->getBasicResult(sprintf("SELECT type FROM Account WHERE number = %d", $this->account_number)); }

	public function getInterest(): float { return $this->getBasicResult(sprintf("SELECT interest FROM Account WHERE number = %d", $this->account_number)); }

	public function getMonthlyFee(): float { return $this->getBasicResult(sprintf("SELECT monthly_fee FROM Account WHERE number = %d", $this->account_number)); }

	public function canGoNegative(): bool { return $this->getBasicResult(sprintf("SELECT can_go_negative FROM Account WHERE number = %d", $this->account_number)); }

	public function deleteAccount() { return $this->getBasicResult(sprintf("DELETE FROM Account WHERE number = %d", $this->getAccountNumber())); }

	/**
	 * @throws PGException
	 * @throws InvalidArgumentException
	 */
	public static function createAccount(User $creator, string $name, string $type, float $initial_balance=0): Account
	{
		if(strlen($name) > 30 || strlen($name) == 0){
			throw new InvalidArgumentException("The account name must be between 0 and 30 characters.");
		}

		if($initial_balance < 0){
			throw new InvalidArgumentException("An account can not be created with an immediate negative balance.");
		}

		return new Account($creator, $name, $type, $initial_balance);
	}
}