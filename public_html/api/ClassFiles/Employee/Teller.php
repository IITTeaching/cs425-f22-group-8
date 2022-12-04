<?php

require_once (dirname(__DIR__, 2) . "/ConfigFiles/TellerConfig.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/Account.php");
require_once (dirname(__DIR__, 2) . "/tools.php");

class Teller extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new TellerConfig());
	}

	protected function employeeType():EmployeeTypes { return EmployeeTypes::Teller; }

	/**
	 * Returns the balance of the account, or false if the account doesn't exist.
	 *
	 * @param Account $account
	 * @return float|false
	 */
	public function getAccountBalance(Account $account): float|false{
		if(!$this->checkAccountExists($account)){
			return false;
		}
		$result = $this->query(sprintf("SELECT balance FROM Account WHERE number = %d", $account->getAccountNumber()));
		return pg_fetch_result($result, 0);
	}

	public static function fromUsername(string $username): false|Teller{
		$id = parent::fromUsername($username);
		if(!$id) { return false; }
		return new Teller($id);
	}
}