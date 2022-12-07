<?php
require_once("api/ClassFiles/DataBase.php");
require_once("api/ClassFiles/CookieManager.php");
require_once("api/Exceptions/PGException.php");
require_once("api/constants.php");

try{
	$db = new DataBase();
	$cookies = new CookieManager();
} catch (PGException | InvalidArgumentException $pgError){
	http_response_code(500);
	respond($pgError->getMessage());
	header("Location: " . HTTPS_HOST);
	return;
}

if(!$cookies->isEmployee()){
	respond("You are not an employee.");
	header("Location: " . HTTPS_HOST . "/profile");
	return;
}

$username = $cookies->getCookieUsername();
$result = $db->query(sprintf("SELECT role FROM Employee WHERE id = (SELECT id FROM EmployeeLogins WHERE username = '%s')", $username), "Error seeing what kind of employee you are.");
switch(pg_fetch_result($result, 0)){
	case "Teller":
		http_response_code(307);
		header("Location: " . HTTPS_HOST . "/teller");
		break;
	case "Loan Shark":
		http_response_code(307);
		header("Location: " . HTTPS_HOST . "/loan_manager");
		break;
	case "Manager":
		http_response_code(307);
		header("Location: " . HTTPS_HOST . "/manager");
		break;
	default:
		http_response_code(500);
		respond(sprintf("Employee named '%s' has an unknown role, and cannot be redirected.", $username));
		return;
}