<?php
require "DataBase.php";

try{
	$db = new DataBase();
	$db->logout();
} catch(PGException $exception){
	http_response_code(500);
	header("Error: Internal Database Error, please try again later: " . $exception->getMessage());
}

header("Location: https://cs425.lenwashingtoniii.com");
return;