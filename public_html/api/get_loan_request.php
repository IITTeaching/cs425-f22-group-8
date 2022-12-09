<?php

require_once (dirname(__DIR__) . "/api/constants.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Employee/LoanShark.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/Exceptions/PGException.php");

if(!( isset($_POST["statement_month"]) && isset($_POST["account_number"]))){
	http_response_code(400);
	respond("All fields are required.");
	return;
}

$cookie = new CookieManager();
if(!$cookie->isEmployee()){
	respond("Only Loan Managers can access this.");
	http_response_code(400);
	return;
}

$username = $cookie->getCookieUsername();
if(!$username){
	respond("You are registered as logged in, but there is no user attached to this session.");
	http_response_code(500);
	$cookie->deleteCookie();
	return;
}

try {
	$shark = LoanShark::fromUsername($username);
} catch(PGException $e){
	http_response_code(400);
	respond("The system could not find the user to authorize this transaction.");
	return;
}


try {

	http_response_code(200);

} catch(PGException | InvalidArgumentException $e){
	http_response_code(500);
	respond($e->getMessage());
	return;
}