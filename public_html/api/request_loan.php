<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once(dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once(dirname(__DIR__) . "/api/ClassFiles/LoanRequest.php");
require_once(dirname(__DIR__) . "/api/Exceptions/PGException.php");
require_once(dirname(__DIR__) . "/api/tools.php");

if (!( isset($_POST["amount"]) && isset($_POST["compounding_per_year"]) && isset($_POST["apr"]) && isset($_POST["n"])
	&& isset($_POST["loan_name"]) )) {
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

try{
	$user = User::fromUsername($username);
} catch(PGException $e){
	http_response_code(500);
	respond($e->getMessage());
	return;
}

try {
	$request = LoanRequest::requestLoan($user, $_POST["amount"], $_POST["compounding_per_year"],
		$_POST["apr"], $_POST["n"], $_POST["loan_name"]);
	respond("Loan request has been submitted. Pending approval. (It will not be visible until a Loan Manager accepts it.");
	header("Loan-Request-Number: " . $request->getNumber());
	header("Loan-Request-Payment: " . $request->getPayment());
	http_response_code(200);
	return;
} catch (PGException | InvalidArgumentException $pgError) {
	http_response_code(500);
	respond($pgError->getMessage());
	return;
}

