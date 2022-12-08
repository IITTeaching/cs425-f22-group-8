<?php
require_once "api/constants.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WCS Login</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="/css/login.css" type="text/css" rel="stylesheet"/>
	<link href="/css/ring_indicator.css" type="text/css" rel="stylesheet"/>
	<link href="/css/navigation.css" type="text/css" rel="stylesheet"/>
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
	<nav>
		<ul class="navigation">
			<div class="brand"> 
	<!-- Making menu icon clickable to display the navigation menu on smaller screens -->
				<i onclick="navToggle()" id="nav-icon" class="fa fa-navicon" style="font-size:24px"></i> 
			</div>
			<!-- Your Logo Goes Here -->
			<a href="/" class="w3-bar-item w3-button w3-wide">
				<img class="img-nav" src="/images/logo_square.png" alt="WCS">
			</a>
		</ul>
	</nav>
	<div class="wrapper fadeInDown">
		<div id="formContent">
			<h2 class="active">Sign In</h2>
		</div>
		<div class = "fadeIn first"></div>
		<div class="content">
			<form name="form" id="form" action="/api/login" method="POST">
				<input class = "fadeIn second" type="text" id="username" name="username" value="" autocomplete="username" placeholder = "login" oninput="checkInfo()" required>
				<input class = "fadeIn second" type="password" id="password" name="password" value="" autocomplete="current-password" placeholder = "password" oninput="checkInfo()" onkeyup="checkInfo()" required>
				<input class = "fadeIn second" type="number" id="auth_code" name="auth_code" value="" placeholder = "2FA code">
				<input type="submit" id="submit" name="submit" class="fadeIn fifth" value = "Log In">
			</form>
		</div>
	</div>
</body>
</html>