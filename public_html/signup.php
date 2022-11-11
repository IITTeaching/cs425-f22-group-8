<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>CS425 Test Signup</title>
</head>
<body>

<form name="form" action="/api/signup.php" method="POST">
	<label for="username">Username: </label>
	<input type="text" id="username" name="username" value=""><br>

	<label for="password">Password: </label>
	<input type="password" id="password" name="password" value=""><br>

	<label for="fullname">Email: </label>
	<input type="text" id="fullname" name="fullname" value="">

	<label for="address">Email: </label>
	<input type="text" id="address" name="address" value="">

	<label for="email">Email: </label>
	<input type="text" id="email" name="email" value="">


	<input type="submit" name="submit" value="Sign up!">
</form>

</body>
</html>