<?php

include_once "../CS425Class.php";
include_once "EmployeeTypes.php";
include_once "../../ConfigFiles/Config.php";

abstract class Employee extends CS425Class
{
	protected readonly int $employee_id;
	private string|false $authcode;

	public function __construct(int $employee_id, Config $cfg){
		parent::__construct($cfg);
		$this->employee_id = $employee_id;
		//$this->ensureEmployeeType();
		$this->authcode = false;
	}

	public function getEmployeeType(): EmployeeTypes{
		$type = $this->getBasicResult(sprintf("SELECT role FROM Employee WHERE id = %d", $this->employee_id));
		return match ($type) {
			"Teller" => EmployeeTypes::Teller,
			"Loan Shark" => EmployeeTypes::LoanShark,
			"Manager" => EmployeeTypes::Manager,
			default => throw new InvalidArgumentException("Employee has an unknown role."),
		};
	}

	protected function ensureEmployeeType(): bool{
		if($this->getEmployeeType() == $this->employeeType()){
			return true;
		}
		echo sprintf("Even though you are logged in as a %s, the database does not recognize you as one.", $this->employeeType()->name);
		$this::__destruct();
		return false;
	}

	protected abstract function employeeType(): EmployeeTypes;

	protected function getEmployeeID(): int { return $this->employee_id; }

	public function setAuthCode(string $authcode){
		$this->authcode = $authcode;
	}

	public function getAuthCode(): string|false{ return $this->authcode; }
}