<?php

require "DataBase.php";

try{
	$db = new DataBase();
}catch (PGException $exception){
	http_response_code(500);
	header("Response: " . $exception->getMessage());
	return;
}

$result = $db->query("SELECT subquery.name, address FROM ( SELECT name, cast(a.number AS TEXT) || ' ' || a.direction || ' ' || a.street_name || ', ' || a.city || ', ' || a.state || ', ' || a.zipcode AS address FROM branch JOIN addresses a on a.id = branch.address) subquery;");
$numRows = pg_affected_rows($result);
if(!$result){
	http_response_code(500);
	header("Response: " . pg_last_error());
	return;
}

$dct = array();

while($row = pg_fetch_array($result)){
	$dct[] = array($row["name"] => $row["address"]);
}

http_response_code(200);
echo json_encode($dct);