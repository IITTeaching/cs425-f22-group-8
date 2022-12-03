<?php

require_once "Employee.php";
require_once "LoanShark.php";
require_once "Teller.php";
require_once (dirname(__DIR__, 2) . "/ConfigFiles/ManagerConfig.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/Address.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/Authentication.php");

class Manager extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new ManagerConfig());
	}

	public function addEmployee(string $name, EmployeeTypes $role, Address $address, int $ssn, Address $branch, float $salary,
									string $password): Employee{
		$name = $this->prepareData($name);
		$ssn = hash_hmac("sha256", $ssn, "A super duper secret key");
		$username = explode(" ", strtolower(str_replace(".", "", $name)));
		$password = password_hash($this->prepareData($password), CRYPT_SHA512);
		$username = $username[0][0] . $username[count($username)-1]; // TODO: Add middle names or numbers in case there is another person with a similar name

		$sql = sprintf("INSERT INTO Employee(name, role, address, ssn, branch, salary) VALUES('%s','%s',%d,'%s',%d,%f) RETURNING id",
			$name, $role->value, $address->getAddressId(), $ssn, $branch->getAddressId(), $salary);
		$result = $this->query($sql);
		$this->checkQueryResult($result);
		$id = pg_fetch_result($result, 0);
		switch ($role){
			case EmployeeTypes::LoanShark:
				$employee = new LoanShark($id);
				break;
			case EmployeeTypes::Teller:
				$employee = new Teller($id);
				break;
			case EmployeeTypes::Manager:
				$employee = new Manager($id);
				break;
		}

		$_2fa = (new Authentication())->createSecretKey();
		$employee->setAuthCode($_2fa);

		$this->query(sprintf("INSERT INTO EmployeeLogins VALUES(%d, '%s', '%s','%s')",
			$employee->getEmployeeID(), $username, $password, $employee->getAuthCode()));

		return $employee;
	}

	protected function employeeType(): EmployeeTypes { return EmployeeTypes::Manager; }

	public static function fromUsername(string $username): false|Manager{
		$id = parent::fromUsername($username);
		if(!$id) { return false; }
		return new Manager($id);
	}
}