<?php

require_once "tools.php";
require_once "ClassFiles/Views.php";

try{
	$views = new Views();
	echo json_encode($views->getAccountTypes());
} catch (PGException $e){
	respond($e->getMessage());
	http_response_code(400);
}