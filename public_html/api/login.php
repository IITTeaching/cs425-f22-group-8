<?php
require("ClassFiles/DataBase.php");
require "constants.php";

if (!(isset($_POST['username']) && isset($_POST['password']))) {
	http_response_code(400);
	header("Response: All fields are required.");
	return;
}

try{
	$db = new DataBase();
} catch(PGException $exception){
	http_response_code(500);
	header("Response: Internal Database Response, please try again later: " . $exception->getMessage());
	header("Location: " . HTTPS_HOST);
	return;
}

try{
	$result = $db->logIn($_POST['username'], $_POST['password']);
} catch(PGException $pgException){
	http_response_code(500);
	header("Response: " . $pgException->getMessage());
	header("Location: " . HTTPS_HOST . "/login");
	return;
} catch(InvalidArgumentException $argumentException){
	http_response_code(401);
	header("Response: " . $argumentException->getMessage());
	header("Location: " . HTTPS_HOST . "/login");
	return;
}

if (gettype($result) == 'boolean') {
	http_response_code(401);
	header("Location: " . HTTPS_HOST . "/login");
	return;
}

else{
	http_response_code(200);
	header("Response: " . $result);
	header("Location: " . HTTPS_HOST);
}

