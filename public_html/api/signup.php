<?php
require "DataBase.php";
require "tools.php";

if (!(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['username']) &&
	isset($_POST['password']) && isset($_POST['phone']) && isset($_POST["address_number"])  && isset($_POST["direction"]) &&
	isset($_POST["streetname"]) && isset($_POST["city"]) && isset($_POST["state"]) && isset($_POST["zipcode"])
	&& isset($_POST['apt']) && isset($_POST["branch"])
)) {
	http_response_code(400);
	header("Error: All fields are required");
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

try{
	$db = new DataBase();
} catch(PGException $pgException){
	http_response_code(500);
	header("Error: Internal Database connection");
	header("Location: https://cs425.lenwashingtoniii.com");
	return;
}

try{
	if($db->usernameInUse($_POST['username'])){
		http_response_code(226);
		header("Error: Username is already taken. Try a different one.");
		header("Location: https://cs425.lenwashingtoniii.com/signup");
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	header("Error: " . $pgException->getMessage());
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

if(!isValidEmail($_POST["email"])){
	http_response_code(406);
	header("Error: You must input a valid email address.");
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

try {
	if($db->emailInUse($_POST["email"])){
		http_response_code(226);
		header("Error: Email-address is already in use, please use a different one.");
		header("Location: https://cs425.lenwashingtoniii.com/signup");
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	header("Error: " . $pgException->getMessage());
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

if(strlen($_POST["state"]) != 2){
	http_response_code(400);
	header("Error: The state should be the 2 letter US state abbreviation, not: " . $_POST["state"]);
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

$result = $db->query(sprintf("SELECT COUNT(abbreviation) FROM States WHERE abbreviation = UPPER('%s')", $_POST["state"]));
if(pg_fetch_result($result, 0, 0) == 0){
	http_response_code(400);
	header("Error: I wasn't aware we had a US state abbreviated: " . $_POST["state"] . ".");
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

try {
	if($db->signUp($_POST['fullname'], $_POST["email"], $_POST["username"], $_POST["password"], $_POST["phone"],
		$_POST["address_number"], $_POST["direction"], $_POST["streetname"], $_POST["city"], $_POST["state"],
		$_POST["zipcode"], $_POST["apt"], $_POST["branch"])) {
		http_response_code(201);
		header("Error: Sign Up Success");
		header("Location: https://cs425.lenwashingtoniii.com/profile");
	} else {
		http_response_code(500);
		header("Error: Sign up Failed");
		header("Location: https://cs425.lenwashingtoniii.com/signup");
	}
} catch(PGException $pgException){
	http_response_code(500);
	header("Error: " . $pgException->getMessage());
	header("Location: https://cs425.lenwashingtoniii.com");
}
