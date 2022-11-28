<?php
require_once "api/constants.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CS425 Test login</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
</head>
<body>

<form name="form" action="/api/login" method="POST">
    <label for="username">Username: </label>
    <input type="text" id="username" name="username" value="" autocomplete="username" required><br>

    <label for="password">Password: </label>
    <input type="password" id="password" name="password" value="" autocomplete="current-password" required><br>

    <label for="2FA Code">Password: </label>
    <input type="number" id="auth_code" name="auth_code" value=""><br>

    <input type="submit" name="submit" value="Login">
</form>

</body>
</html>