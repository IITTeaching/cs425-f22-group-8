<?php

require_once (dirname(__DIR__) . "/api/constants.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Account.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");

$cookie = new CookieManager();
$username = $cookie->getCookieUsername();
if(!$username){
	respond("You are registered as logged in, but there is no user attached to this session.");
	http_response_code(500);
	$cookie->deleteCookie();
	return;
}

if(!isset($_POST["authorizer_type"])){
	$user = User::fromUsername($username);
} elseif ($_POST["authorizer_type"] == "Manager"){
	$authorizer = Manager::fromUsername($username);
	if(!isset($_POST["user_id"])){
		http_response_code(400);
		respond("The id of the user needs to be provided");
		return;
	}
	$user = new User($_POST["user_id"]);
} else{
	http_response_code(400);
	respond("The system could not find the user to authorize this transaction.");
	return;
}

try {
	http_response_code(200);
	$accounts = array();
	foreach($user->getAccounts() as $account){
		$accounts[] = $account->getAccountNumber();
	}
	echo json_encode($accounts);
} catch(PGException | InvalidArgumentException $e){
	http_response_code(500);
	respond($e->getMessage());
	return;
}