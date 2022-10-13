<?php
require "DataBase.php";

try{
	echo
	$db = new DataBase();
} catch(PGException $pgException){
	http_response_code(500);
	echo "Error: Database connection";
	return;
}

if($db->checkCookie()){
	$db->_print("You have a proper cookie.");
} else{
	$db->_print("Your cookie is not proper.");
}
