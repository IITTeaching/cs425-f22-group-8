<?php

require_once(dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once(dirname(__DIR__) . "/api/Exceptions/PGException.php");
require_once(dirname(__DIR__) . "/api/constants.php");
require_once(dirname(__DIR__) . "/api/tools.php");

if (!( $_POST["name"] && $_POST["role"] && $_POST["address_id"] && $_POST["ssn"] && $_POST["work_branch"] && $_POST["float"]) && $_POST["salary"] && $_POST["password"]) {
	http_response_code(400);
	respond("All fields are required.");
	return;
}

$cookie = new CookieManager();
$username = $cookie->getCookieUsername();
if (!$username) {
	respond("You are registered as logged in, but there is no user attached to this session.");
	http_response_code(500);
	$cookie->deleteCookie();
	return;
}

$role = getEmployeeType($_POST["role"]);
if(!$role){
	http_response_code(400);
	respond("The role must be `Loan Manager`, `Teller` or `Manager`");
	return;
}

try {
	$manager = Manager::fromUsername($username);
	$new_employee = $manager->addEmployee($_POST["name"], $role, new Address(), $_POST["ssn"], new Address($_POST["work_branch"]), (float)$_POST["salary"], $_POST["password"]);
	http_response_code(200);
	respond("Employee was successfully added");
	header("New-Employee-Id: " . $new_employee->getEmployeeID());
	return;
} catch (PGException | InvalidArgumentException $pgError) {
	http_response_code(500);
	respond($pgError->getMessage());
	return;
}

