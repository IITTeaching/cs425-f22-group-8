<?php

include_once "Employee.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ManagerConfig.php");
require_once (dirname(__DIR__) . "/ClassFiles/Address.php");

class Manager extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new ManagerConfig());
	}

	protected function addEmployee($name, EmployeeTypes $role, Address $address, int $ssn, Address $branch, float $salary,
									string $username, string $password): Employee{
		$name = $this->prepareData($name);
		$ssn = hash_hmac("sha256", $ssn, "A super duper secret key");
		$sql = sprintf("INSERT INTO Employee(name, role, address, ssn, branch, salary) VALUES('%s','%s',%d,'%s',%d,%f) RETURNING id",
			$name, $role->name, $address->getAddressId(), $ssn, $branch->getAddressId(), $salary);
		$result = $this->query($sql);
		$this->checkQueryResult($result);
		$employee = new Employee(pg_fetch_result($result, 0)); // TODO: Add a switch case to create the proper employee

		$_2fa = (new Authentication())->createSecretKey();
		$employee->setAuthCode($_2fa);

		$this->query(sprintf("INSERT INTO EmployeeLogins VALUES(%d, '%s', '%s','%s')",
			$employee->getEmployeeID(), $username, $password, $employee->getAuthCode()));

		return $employee;
	}
}