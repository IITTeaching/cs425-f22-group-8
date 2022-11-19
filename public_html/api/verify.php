<?php

require "Verifications.php";

if(!(isset($_GET["email"]) && isset($_GET["code"]))){
	http_response_code(400);
	header("Error: The given link was malformed");
	return;
}

$verify = new Verifications();

if(!$verify->check_verification($_GET["email"], $_GET["code"])){
	header("The code does not match what we provided.");
	http_response_code(400);
} else{
	header("Location: https://lenwashingtoniii.com/login");
	http_response_code(200);
}