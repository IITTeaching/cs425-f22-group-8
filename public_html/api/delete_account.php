<?php

require_once (dirname(__DIR__) . "/api/constants.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Account.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Employee/Manager.php");
require_once (dirname(__DIR__) . "/api/Exceptions/PGException.php");

if(!isset($_POST["account_number"])) {
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

try{
	if(!isset($_POST["authorizer_type"])){
		$user = User::fromUsername($username);
	} elseif ($_POST["authorizer_type"] == "Manager"){
		$authorizer = Manager::fromUsername($username);
	} else{
		http_response_code(400);
		respond("The system could not find the user to authorize this transaction.");
		return;
	}
} catch(PGException $e){
	http_response_code(500);
	respond($e->getMessage());
	return;
}

try {
	$account = new Account($_POST["account_number"]);
	$account->deleteAccount();

	http_response_code(200);
	respond("Account deleted successfully");
	header("Deleted-Account-Number: " . $account->getAccountNumber());
	return;
} catch (PGException|InvalidArgumentException $e) {
	http_response_code(500);
	respond($e->getMessage());
	return;
}