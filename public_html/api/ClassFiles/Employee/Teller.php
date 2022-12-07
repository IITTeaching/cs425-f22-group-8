<?php

require_once "Employee.php";
require_once (dirname(__DIR__, 2) . "/tools.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/Account.php");
require_once (dirname(__DIR__, 2) . "/ConfigFiles/TellerConfig.php");

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
	 * @throws PGException
	 */
	public function getAccountBalance(Account $account): float|false{
		$result = $this->query(sprintf("SELECT balance FROM Account WHERE number = %d", $account->getAccountNumber()));
		return pg_fetch_result($result, 0);
	}

	/**
	 * @throws PGException
	 */
	public static function fromUsername(string $username):  Teller|int{
		$id = parent::fromUsername($username);
		if(!$id) { return false; }
		return new Teller($id);
	}
}