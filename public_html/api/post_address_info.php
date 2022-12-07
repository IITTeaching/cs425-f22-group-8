<?php

require_once(dirname(__DIR__) . "/api/ClassFiles/Address.php");
require_once(dirname(__DIR__) . "/api/ClassFiles/CookieManager.php");
require_once(dirname(__DIR__) . "/api/Exceptions/PGException.php");
require_once(dirname(__DIR__) . "/api/constants.php");
require_once(dirname(__DIR__) . "/api/tools.php");

if (!(isset($_POST["address_number"])  && isset($_POST["direction"]) && isset($_POST["streetname"]) && isset($_POST["city"])
	&& isset($_POST["state"]) && isset($_POST["zipcode"]) && isset($_POST['apt']))) {
	http_response_code(400);
	respond("All fields are required");
	header("Location: " . HTTPS_HOST . "/signup");
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

try {
	$address = Address::createAddress($_POST["address_number"], $_POST["direction"], $_POST["streetname"], $_POST["city"], $_POST["state"], $_POST["zipcode"], $_POST['apt']);
	respond("Address added successfully");
	http_response_code(200);
	header("Address-ID: " . $address->getAddressId());
	return;
} catch (PGException | InvalidArgumentException $pgError) {
	http_response_code(500);
	respond($pgError->getMessage());
	return;
}

