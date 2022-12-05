<?php

require_once (dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once (dirname(__DIR__) . "/api/ClassFiles/User.php");
require_once "constants.php";

$account_number = (int)$_POST["account_number"];

$cookie = new CookieManager();

if(!$cookie->isValidCookie()){
	http_response_code(400);
	respond("You must be logged in to receive this information.");
}

$user = User::fromUsername($cookie->getCookieUsername());

foreach($user->getAccounts() as $account){
	if($account_number == $account->getAccountNumber()){
		http_response_code(200);
		$data = array();
		$data["Name"] = $account->getName();
		$data["Balance"] = $account->getBalance();
		$data["Type"] = $account->getType();
		$data["Interest"] = $account->getInterest();
		$data["Monthly Fee"] = $account->getMonthlyFee();
		$data["Overdrawn"] = $account->canGoNegative();
		echo json_encode($data);
		return;
	}
}

http_response_code(400);
respond("Could not find account numbered: " . $account_number);