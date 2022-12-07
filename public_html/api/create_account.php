<?php

require_once (dirname(__DIR__) . "/api/constants.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Account.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");

if(!( isset($_POST["account_name"]) && isset($_POST["account_type"]) )){
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
	$authorizer = User::fromUsername($username);
} elseif ($_POST["authorizer_type"] == "Manager"){
	$authorizer = Manager::fromUsername($username);
} elseif ($_POST["authorizer_type"] == "Teller"){
	$authorizer = Teller::fromUsername($username);
} else{
	http_response_code(400);
	respond("The system could not find the user to authorize this transaction.");
	return;
}

$balance = ((float)$_POST["initial_balance"]) ?? 0;
$account = Account::createAccount(, $_POST["account_name"], $_POST["account_type"], $balance);