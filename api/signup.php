<?php
require "DataBase.php";

if (!(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['address']) && isset($_POST['username']) && isset($_POST['password']))) {
	http_response_code(400);
	echo "All fields are required";
	return;
}

try{
	$db = new DataBase();
} catch(PGException $pgException){
	http_response_code(500);
	echo "Error: Database connection";
	return;
}

try{
	if($db->usernameInUse($_POST['username'])){
		http_response_code(226);
		echo "Username is already taken. Try a different one.";
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
	return;
}

try {
	if($db->emailInUse($_POST["email"])){
		http_response_code(226);
		echo "Email-address is already in use, please use a different one.";
		return;
	}
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
	return;
}

try {
	if ($db->signUp($_POST['fullname'], $_POST['email'], $_POST['address'], $_POST['username'], $_POST['password'])) {
		http_response_code(201);
		echo "Sign Up Success";
	} else {
		http_response_code(500);
		echo "Sign up Failed";
	}
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
}
