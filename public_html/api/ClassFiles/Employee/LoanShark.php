<?php

require_once "Employee.php";
require_once (dirname(__DIR__, 2) . "/ConfigFiles/LoanConfig.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/LoanRequest.php");

class LoanShark extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new LoanConfig());
	}

	/**
	 * @return LoanRequest[]
	 * @throws PGException
	 */
	public function getRequestedLoans(): array
	{
		$result = $this->query("SELECT loan_request_id FROM LoanRequests");
		$loan_requests = array();
		while($loan_request = pg_fetch_assoc($result)){
			$loan_requests[] = new LoanRequest($loan_request["loan_request_id"]);
		}
		return $loan_requests;
	}

	protected function employeeType():EmployeeTypes { return EmployeeTypes::LoanShark; }

	/**
	 * @throws PGException
	 */
	public function approveLoan(LoanRequest $loan): Loan{
		$row = $this->query(sprintf("DELETE FROM LoanRequests WHERE loan_request_id = %d RETURNING *;", $loan->getNumber()));
		$row = pg_fetch_assoc($row);

		$result = $this->query(sprintf("INSERT INTO LoanApprovals(loan_name, approver_id, customer_id, initial_amount, amount_remaining, n, payment, compounding_per_year, apr) VALUES('%s',%d,%d,%f,%f,%d,%f,%d,%f) RETURNING loan_number",
					$row["name"], $this->getEmployeeID(), $row["customer_id"], $row["amount"], $row["amount"], $row["n"], $row["payment"], $row["compounding_per_year"], $row["apr"]),
			sprintf("An error occurred when accepting LoanRequest: %d from Customer id: %d", $loan->getNumber(), $loan->getCustomer()->getUserId()));
		return new Loan(pg_fetch_result($result, 0));
	}

	/**
	 * @throws PGException
	 */
	public function denyLoan(LoanRequest $loan): \PgSql\Result|bool{ # TODO: Maybe add a reason why the request was denied
		return $this->query("DELETE FROM LoanRequests WHERE loan_request_id = %d", $loan->getNumber());
	}

	public static function fromUsername(string $username): LoanShark|false {
		$id = parent::fromUsername($username);
		if(!$id) { return false; }
		return new LoanShark($id);
	}
}