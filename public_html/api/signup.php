<?php
require_once("ClassFiles/DataBase.php");
require_once "tools.php";
require_once "constants.php";

if (!(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['username']) &&
	isset($_POST['password']) && isset($_POST['phone']) && isset($_POST["address_number"])  && isset($_POST["direction"]) &&
	isset($_POST["streetname"]) && isset($_POST["city"]) && isset($_POST["state"]) && isset($_POST["zipcode"])
	&& isset($_POST['apt']) && isset($_POST["branch"])
)) {
	http_response_code(400);
	respond("All fields are required");
	header("Location: " . HTTPS_HOST . "/signup");
	return;
}

try{
	$db = new DataBase();
} catch(PGException $pgException){
	http_response_code(500);
	respond("Internal Database connection");
	header("Location: " . HTTPS_HOST);
	return;
}

try{
	if($db->usernameInUse($_POST['username'])){
		http_response_code(226);
		respond("Username is already taken. Try a different one.");
		header("Location: " . HTTPS_HOST . "/signup");
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	respond($pgException->getMessage());
	header("Location: " . HTTPS_HOST . "/signup");
	return;
}

if(!isValidEmail($_POST["email"])){
	http_response_code(406);
	respond("You must input a valid email address.");
	header("Location: " . HTTPS_HOST . "/signup");
	return;
}

try {
	if($db->emailInUse($_POST["email"])){
		http_response_code(226);
		respond("Email-address is already in use, please use a different one.");
		header("Location: " . HTTPS_HOST . "/signup");
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	respond($pgException->getMessage());
	header("Location: " . HTTPS_HOST . "/signup");
	return;
}

if(strlen($_POST["state"]) != 2){
	http_response_code(400);
	respond("The state should be the 2 letter US state abbreviation, not: " . $_POST["state"]);
	header("Location: " . HTTPS_HOST . "/signup");
	return;
}

$result = $db->query(sprintf("SELECT COUNT(abbreviation) FROM States WHERE abbreviation = UPPER('%s')", $_POST["state"]));
if(pg_fetch_result($result, 0, 0) == 0){
	http_response_code(400);
	respond("I wasn't aware we had a US state abbreviated: " . $_POST["state"] . ".");
	header("Location: " . HTTPS_HOST . "/signup");
	return;
}

try {
	if($db->signUp($_POST['fullname'], $_POST["email"], $_POST["username"], $_POST["password"], $_POST["phone"],
		$_POST["address_number"], $_POST["direction"], $_POST["streetname"], $_POST["city"], $_POST["state"],
		$_POST["zipcode"], $_POST["apt"], $_POST["branch"])) {
		http_response_code(303);
		respond("Sign Up Success");
		header("Location: " . HTTPS_HOST . "/login");
	} else {
		http_response_code(500);
		respond("Sign up Failed");
		header("Location: " . HTTPS_HOST . "/signup");
	}
} catch(PGException $pgException){
	http_response_code(500);
	respond($pgException->getMessage());
	header("Location: " . HTTPS_HOST);
}
