<?php

require_once("ClassFiles/DataBase.php");
require_once "Exceptions/PGException.php";
require_once "constants.php";

try{
	$db = new DataBase();
}catch (PGException $exception){
	http_response_code(500);
	respond($exception->getMessage());
	return;
}

$result = $db->query("SELECT * FROM branch_info");
$numRows = pg_affected_rows($result);
if(!$result){
	http_response_code(500);
	respond(pg_last_error());
	return;
}

$dct = array();

while($row = pg_fetch_array($result)){
	$dct[] = array($row["name"] => $row["address"]);
}

http_response_code(200);
echo json_encode($dct);