<?php
require "api/DataBase.php";

try{
	$db = new DataBase();
} catch(PGException $exception){
	http_response_code(500);
	echo "There was an internal database error, try again later. If this problem persists, contact the system administrator.";
	return;
}
# TODO: Find a way to grab the user info (name, balance) quickly.
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Banking</title>
	<link href="menu_style.css" type="text/css" rel="stylesheet"/>
</head>
<body>
	<div id="content">
		<h1 class="title">Welcome to WCS Banking</h1>
		<h2>A banking system of the people, by the people, for the people, EAGLE!</h2>
		<nav class="floating-menu">
			<?php if($db->isLoggedIn()): ?>
			<h3>We sold you?</h3>
			<a href="login.php">Log In</a>
			<a href="signup.php">Sign Up</a>
			<?php else: ?>
			<h3>Hello Name</h3>
			<a href="profile">Check My Profile</a>
			<?php endif; ?>

		</nav>
	</div>
</body>
</html>
