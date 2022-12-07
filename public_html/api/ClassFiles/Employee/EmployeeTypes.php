<?php

enum EmployeeTypes : string {
	case Teller = "Teller";
	case LoanShark = "Loan Shark";
	case Manager = "Manager";
}

function getEmployeeType(string $type): EmployeeTypes | false {
	return match ($type) {
		"Manager" => EmployeeTypes::Manager,
		"Loan Manager", "Loan Shark" => EmployeeTypes::LoanShark,
		"Teller" => EmployeeTypes::Teller,
		default => false,
	};
}