<?php

require_once (dirname(__DIR__) . "/api/ClassFiles/AccountTransaction.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Employee/Manager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/Employee/Teller.php");
require_once (dirname(__DIR__) . "/api/Exceptions/PGException.php");
require_once (dirname(__DIR__) . "/api/tools.php");

if(!isset($_POST["transaction_type"])){
	http_response_code(400);
	respond("The transaction type must be provided");
	return;
}

if(!in_array($_POST["transaction_type"], array("Deposit", "Transfer", "Withdrawal"))){
	http_response_code(400);
	respond("The only transactions are Deposit, Transfer, and Withdrawals, not: " . $_POST["transaction_type"]);
	return;
}

if($_POST["transaction_type"] == "Transfer"){
	if(!( isset($_POST["initial_account"]) && isset($_POST["final_account"]) && isset($_POST["amount"]) )){
		http_response_code(400);
		echo "All fields required";
		return;
	}
} else{
	if(!( isset($_POST["account_number"]) && isset($_POST["amount"]) )){
		http_response_code(400);
		echo "All fields required";
		return;
	}
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
	switch ($_POST["transaction_type"]){
		case "Deposit":
			$deposit = $trans->deposit($authorizer, new Account($_POST["account_number"]), $_POST["amount"]);
			if($deposit == (float)$_POST["amount"]){
				http_response_code(200);
				header("Response: Amount deposit successfully.");
			}
			return;
		case "Transfer":
			$deposit = $trans->transfer($authorizer, $_POST["amount"], new Account($_POST["initial_account"]), new Account($_POST["final_account"]) );
			if($deposit == (float)$_POST["amount"]){
				http_response_code(200);
				header("Response: Transfer successful.");
			}
			return;
		case "Withdrawal":
			$withdrawn = $trans->withdrawal($authorizer, new Account($_POST["account_number"]), $_POST["amount"]);
			if($withdrawn == (float)$_POST["amount"]){
				http_response_code(200);
				header("Response: Amount withdrawn successfully.");
			}
			return;
	}
} catch (PGException $pgError) {
	http_response_code(500);
	header("Response: " . $pgError->getMessage());
	return;
}
