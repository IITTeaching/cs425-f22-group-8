<?php
require_once ("ClassFiles/DataBase.php");

try{
	$db = new DataBase();
} catch(PGException $pgException){
	http_response_code(501);
	echo $pgException->getMessage();
	return;
}

if (!isset($_POST['auth_code'])) {
	http_response_code(400);
	echo "The authorization code must be given.";
	return;
}

if (!($_POST['streetNumber'] || $_POST['direction'] || $_POST['streetName'] || $_POST['city'] || $_POST['state'] || $_POST['zipcode'])) {
	http_response_code(400);
	echo "Information must be given.";
	return;
}

$streetNumber = $_POST['streetNumber'] ?? null;
$direction = $_POST['direction'] ?? null;
$streetName = $_POST['streetName'] ?? null;
$city = $_POST['city'] ?? null;
$state = $_POST['state'] ?? null;
$zipcode = $_POST['zipcode'] ?? null;

try {
	$result = $db->postAddress($_POST['id'], $streetNumber, $direction, $streetName, $city, $state, $zipcode);
} catch (Exception $e){
	http_response_code(409);
	echo $e->getMessage();
	return;
}

if ($result) {
	http_response_code(200);
	echo "Address successfully updated. \n " . $result;
	return;
}
else{
	http_response_code(400);
	echo "Username or password wrong is wrong";
}