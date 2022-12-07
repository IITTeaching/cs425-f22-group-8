<?php

require_once (dirname(__DIR__) . "/api/constants.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Account.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");

if(!( isset($_POST["statement_month"]) && isset($_POST["account_number"]))){
	http_response_code(400);
	respond("All fields are required.");
	return;
}

$cookie = new CookieManager();
$username = $cookie->getCookieUsername();
if(!$username){
	respond("You are registered as logged in, but there is no user attached to this session.");
	http_response_code(500);
	$cookie->deleteCookie();
	return;
}

if(!isset($_POST["authorizer_type"])){
	User::fromUsername($username);
} elseif ($_POST["authorizer_type"] == "Manager"){
	$authorizer = Manager::fromUsername($username);
} else{
	http_response_code(400);
	respond("The system could not find the user to authorize this transaction.");
	return;
}

if(preg_match("/(\d{4})-(\d{2})/", $_POST["statement_month"], $matches) != 0){
	http_response_code(400);
	respond("The month provided does not match the format mm-yyyy.");
	return;
}

$month = (int)$matches[2];
$year = (int)$matches[1];
if($month <= 0 || $month > 13){
	http_response_code(400);
	respond("The month should be between 1 and 12 inclusive.");
	return;
}

try {
	$account = new Account($_POST["account_number"]);
	http_response_code(200);
	echo json_encode($account->getMonthlyStatement($month, $year));
} catch(PGException | InvalidArgumentException $e){
	http_response_code(500);
	respond($e->getMessage());
	return;
}