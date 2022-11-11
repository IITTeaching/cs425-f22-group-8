<?php
require "DataBase.php";

if (!(isset($_POST['username']) && isset($_POST['password']))) {
	http_response_code(400);
	echo "All fields are required.";
	return;
}