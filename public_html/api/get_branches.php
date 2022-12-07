<?php

require_once("ClassFiles/Views.php");
require_once "Exceptions/PGException.php";
require_once "constants.php";

try{
	$view = new Views();
}catch (PGException $exception){
	http_response_code(500);
	respond($exception->getMessage());
	return;
}

try{
	$branches = $view->getBranchInfo();
	http_response_code(200);
	echo json_encode($branches);
} catch (PGException $e){
	http_response_code(500);
	respond(error_get_last());
	return;
}