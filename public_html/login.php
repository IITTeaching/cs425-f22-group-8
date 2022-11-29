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
	<link href="/css/ring_indicator.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="/scripts/buttons.js"></script>
	<script type="text/javascript">
		function checkInfo(){
			let username = document.getElementById("username");
			let password = document.getElementById("password");

			if(username.value.length === 0 || password.value.length < 8){
				missingInfo();
			} else{
				allGood();
			}
		}
	</script>
</head>
<body>
<form name="form" id="form" action="/api/login" method="POST">
    <label for="username">Username: </label>
    <input type="text" id="username" name="username" value="" autocomplete="username" onblur="checkInfo()" required><br>

    <label for="password">Password: </label>
    <input type="password" id="password" name="password" value="" autocomplete="current-password" onblur="checkInfo()" required><br>

    <label for="auth_code">2FA Code: </label>
    <input type="number" id="auth_code" name="auth_code" value=""><br>

	<div class="" id="submit_wrapper">
		<button type="submit" name="submit" id="submit" form="form" hidden>LOGIN</button>
	</div>
</form>

</body>
</html>