<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>CS425 Test Signup</title>
</head>
<body>

<form name="form" action="/api/signup" method="POST">
	<label for="username">Username: </label>
	<input type="text" id="username" name="username" value=""><br>

	<label for="password">Password: </label>
	<input type="password" id="password" name="password" value=""><br>

	<label for="fullname">Fullname: </label>
	<input type="text" id="fullname" name="fullname" value=""><br>

	<label for="address">Address: </label>
	<input type="text" id="address" name="address" value=""><br>

	<label for="email">Email: </label>
	<input type="text" id="email" name="email" value=""><br>


	<input type="submit" name="submit" value="Sign up!">
</form>

</body>
</html>