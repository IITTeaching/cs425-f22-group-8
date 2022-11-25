<?php

require_once "User.php"; // # FIXME: Beware, this may cause circular dependency errors
require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ProfileConfig.php");

class Account extends CS425Class
{
	private int $account_number;  // # TODO: Make this const
	private User $owner;
	private float $balance, $interest, $monthly_fee;
	private bool $can_go_negative;
	private string $name;
	private array $authorized_users;

	public function __construct($account_number, $owner, $balance, $name, $interest, $monthly_fee, $can_go_negative, $authorized_users){
		parent::__construct(new ProfileConfig());

		$this->account_number = $account_number;
		$this->owner = $owner;
		$this->balance = $balance;
		$this->name = $name;
		$this->interest = $interest;
		$this->monthly_fee = $monthly_fee;
		$this->can_go_negative = $can_go_negative;
		$this->authorized_users = $authorized_users;
	}

	/**
	 * @param User $user The user trying to set the balance.
	 * @param float $balance The new balance.
	 */
	public function setBalance(User $user, float $balance): void
	{
		$own_result = pg_query($this->connect, sprintf("SELECT COUNT(*) FROM Account WHERE number = %d AND holder = %d", $this->account_number, $user->getUserId()));
		$this->checkQueryResult($own_result);

		$auth_result = pg_query($this->connect, sprintf("SELECT COUNT(*) FROM AuthorizedUsers WHERE account_number = %d AND owner_number = %d", $this->account_number, $user->getUserId()));
		$this->checkQueryResult($auth_result);

		if(pg_result($auth_result, 0, 0) == 0 and pg_result($own_result, 0, 0)){ return; }

		$sql = sprintf("UPDATE Account SET balance = %f WHERE number = %d RETURNING balance", $balance, $this->account_number);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		$this->balance = pg_result($result, 0, 0);
	}

	public function getBalance(): float{
		$sql = sprintf("SELECT balance FROM Account WHERE number = %d", $this->account_number);
		$result = pg_query($this->connect, $sql);
		$this->checkQueryResult($result);
		return pg_result($result, 0, 0);
	}
}