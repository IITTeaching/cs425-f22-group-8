<?php

require_once("ClassFiles/Verifications.php");
require_once "constants.php";

if(!(isset($_GET["email"]) && isset($_GET["code"]))){
	http_response_code(400);
	header("Response: The given link was malformed");
	return;
}

$verify = new Verifications();

if(!$verify->check_verification($_GET["email"], $_GET["code"])){
	header("The code does not match what we provided.");
	http_response_code(400);
} else{
	header("Location: " . HTTPS_HOST . "/login");
	http_response_code(200);
}