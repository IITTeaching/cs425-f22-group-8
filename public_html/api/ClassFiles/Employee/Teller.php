<?php

class Teller extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new TellerConfig());
	}

	protected function employeeType():EmployeeTypes { return EmployeeTypes::Teller; }
}