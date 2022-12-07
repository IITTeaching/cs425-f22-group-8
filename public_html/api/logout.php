<?php
require_once("ClassFiles/DataBase.php");
require_once "constants.php";

try{
	$db = new DataBase();
	$db->logout();
} catch(PGException $exception){
	http_response_code(500);
	respond("Internal Database Error, please try again later: " . $exception->getMessage());
}

header("Location: " . HTTPS_HOST);
return;