<?php

require_once "User.php";
require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ProfileConfig.php");
require_once (dirname(__DIR__) . "/tools.php");

class Account extends CS425Class
{
	private readonly int $account_number;

	public function __construct($account_number){
		parent::__construct(new ProfileConfig());
		$this->account_number = (int)$this->prepareData($account_number);
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

	public static function createAccount(User $creator, string $type, float $initial_balance, string $name="") {
		# TODO: Write create account
	}
}