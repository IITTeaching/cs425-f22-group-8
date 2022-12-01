<?php

class LoanRequest extends CS425Class
{
	private readonly int $loan_request_id;

	public function __construct(int $loan_request_id)
	{
		parent::__construct(new LoanConfig());  // Consider making this class have its own Config File, since it only needs SELECT on LoanRequests
		$this->loan_request_id = $loan_request_id;
	}

	public function getNumber(): int { return $this->loan_request_id; }

	public function getCustomer(): User{
		$result = $this->query(sprintf("SELECT customer_id FROM LoanApprovals WHERE loan_number = %d", $this->loan_request_id));
		return new User(pg_fetch_result($result, 0));
	}
}