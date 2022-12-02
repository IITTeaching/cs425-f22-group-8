<?php

class LoanShark extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new LoanConfig());
	}

	/**
	 * @return Loan[]
	 * @throws PGException
	 */
	public function getRequestedLoans(): array
	{
		$result = $this->query("SELECT loan_request_number FROM LoanRequests");
		$loan_requests = array();
		while($loan_request = pg_fetch_assoc($result)){
			$loan_requests[] = new LoanRequest($loan_request["loan_request_number"]);
		}
		return $loan_requests;
	}

	protected function employeeType():EmployeeTypes { return EmployeeTypes::LoanShark; }

	public function approveLoan(LoanRequest $loan): Loan{
		$row = $this->query("DELETE FROM LoanRequests WHERE loan_request_number = 2 RETURNING *;");
		$row = pg_fetch_assoc($row);

		$result = $this->query(sprintf("INSERT INTO LoanApprovals(approver_id, customer_id, initial_amount, amount_remaining, payback_period, compounding_per_year, apr) VALUES(%d,%d,%f,%f,%d,%d,%f) RETURNING loan_number",
					$this->getEmployeeID(), $row["customer_id"], $row["amount"], $row["amount"], $row["payback_period"], $row["compounding_per_year"], $row["apr"]),
			sprintf("An error occurred when accepting LoanRequest: %d from Customer id: %d", $loan->getNumber(), $loan->getCustomer()->getUserId()));
		return new Loan(pg_fetch_assoc($result, 0));
	}
	// TODO: Create a home page for employees and add the redirect in the DataBase.emplLogin, make sure this isn't overriden in api/login.
	public function denyLoan(LoanRequest $loan){ # TODO: Maybe add a reason why the request was denied
		return $this->query("DELETE FROM LoanRequests WHERE loan_request_number = %d", $loan->getNumber());
	}
}