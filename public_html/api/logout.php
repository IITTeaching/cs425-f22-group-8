<?php
require_once "ClassFiles/CookieManager.php";
require_once "constants.php";

try{
	$cookie = new CookieManager();
	$cookie->deleteCookie();
} catch(InvalidArgumentException $exception){
	http_response_code(500);
	respond($exception->getMessage());
}

header("Location: " . HTTPS_HOST);
return;