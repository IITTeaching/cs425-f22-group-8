<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Create a test address</title>
</head>
<body>

<form name="form" action="/api/post_address_info.php" method="POST">
	<input type="text" name="auth_code" value="4630">
	<input type="text" name="streetNumber" value="4630">
	<input type="text" name="direction" value="S">
	<input type="text" name="streetName" value="Greenwood">
	<input type="text" name="city" value="Chicago">
	<input type="text" name="state" value="IL">
	<input type="text" name="zipcode" value="60653">


	<input type="submit" name="submit" value="Choose Letter!">
</form>

</body>
</html>