<?php
require "DataBase.php";
require "tools.php";

if (!(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['address']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['ssn']))) {
	http_response_code(400);
	echo "All fields are required";
	header("Location: https://cs425.lenwashingtoniii.com\signup");
	return;
}

try{
	$db = new DataBase();
} catch(PGException $pgException){
	http_response_code(500);
	echo "Error: Database connection";
	header("Location: https://cs425.lenwashingtoniii.com");
	return;
}

try{
	if($db->usernameInUse($_POST['username'])){
		http_response_code(226);
		echo "Username is already taken. Try a different one.";
		header("Location: https://cs425.lenwashingtoniii.com/signup");
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

try {
	if($db->emailInUse($_POST["email"])){
		http_response_code(226);
		echo "Email-address is already in use, please use a different one.";
		header("Location: https://cs425.lenwashingtoniii.com/signup");
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

if(!isValidEmail($_POST["email"])){
	http_response_code(4711);
	echo "You must input a valid email address.";
	header("Location: https://cs425.lenwashingtoniii.com/signup");
	return;
}

try {
	if ($db->signUp($_POST['fullname'], $_POST['email'], $_POST['address'], $_POST['username'], $_POST['password'], $_POST['ssn'])) {
		http_response_code(201);
		echo "Sign Up Success";
		header("Location: https://cs425.lenwashingtoniii.com/profile");
	} else {
		http_response_code(500);
		echo "Sign up Failed";
		header("Location: https://cs425.lenwashingtoniii.com/signup");
	}
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
	header("Location: https://cs425.lenwashingtoniii.com");
}
