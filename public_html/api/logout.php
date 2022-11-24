<?php
require("ClassFiles/DataBase.php");

try{
	$db = new DataBase();
	$db->logout();
} catch(PGException $exception){
	http_response_code(500);
	header("Response: Internal Database Error, please try again later: " . $exception->getMessage());
}

header("Location: " . HTTPS_HOST . "");
return;