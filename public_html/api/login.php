<?php
require_once("ClassFiles/DataBase.php");
require_once "constants.php";
require_once "tools.php";

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
	header("Location: " . HTTPS_HOST);  // TODO: Figure out how to check if a location header has already been set, so this doesn't override the location from emplLogin.
	return;
}

$auth_code = $_POST["auth_code"] ?? "";

try{
	$result = $db->logIn($_POST['username'], $_POST['password'], $auth_code);
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
	if(!is_header_set("Location")){
		header("Location: " . HTTPS_HOST); // FIXME: The client can see the location response header, and the page exists, but it stays at /api/login
	}
}

