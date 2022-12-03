<?php

require_once (dirname(__DIR__) . "/api/ClassFiles/AccountTransaction.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Employee/Manager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Employee/Teller.php");
require_once (dirname(__DIR__) . "/api/Exceptions/PGException.php");

if(!( isset($_POST["account_number"]) && isset($_POST["amount"]) )){
	http_response_code(400);
	echo "All fields required";
	return;
}

$cookie = new CookieManager();
$username = $cookie->getCookieUsername();
if(!$username){
	http_response_code(500);
	header("Response: You are registered as logged in, but there is no user attached to this session.");
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
	header("Response: The system could not find the user to authorize this transaction.");
	return;
}

$trans = new AccountTransaction();
try {
	$withdrawn = $trans->withdrawal($authorizer, new Account($_POST["account_number"]), $_POST["amount"]);
	if($withdrawn == (float)$_POST["amount"]){
		http_response_code(200);
		header("Response: Amount withdrawn successfully.");
		return;
	}
} catch (PGException $pgError) {
	http_response_code(500);
	header("Response: " . $pgError->getMessage());
	return;
}
