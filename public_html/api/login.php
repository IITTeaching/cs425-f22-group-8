<?php
require "DataBase.php";

if (!(isset($_POST['username']) && isset($_POST['password']))) {
	http_response_code(400);
	echo "All fields are required.";
	return;
}

try{
	$db = new DataBase();
} catch(PGException $exception){
	http_response_code(500);
	echo "Internal Database Error, please try again later: " . $exception->getMessage();
	header("Location: https://cs425.lenwashingtoniii.com");
	return;
}

try{
	$result = $db->logIn($_POST['username'], $_POST['password']);
} catch(PGException $pgException){
	http_response_code(500);
	echo $pgException->getMessage();
	header("Location: https://cs425.lenwashingtoniii.com\login.php");
	return;
} catch(InvalidArgumentException $argumentException){
	http_response_code(401);
	echo $argumentException->getMessage();
	header("Location: https://cs425.lenwashingtoniii.com\login.php");
	return;
}

if (gettype($result) == 'boolean') {
	http_response_code(401);
	echo "Username or password wrong is wrong.";
	header("Location: https://cs425.lenwashingtoniii.com\login.php");
	return;
}

else{
	http_response_code(200);
	echo $result;
	header("Location: https://cs425.lenwashingtoniii.com");
}

