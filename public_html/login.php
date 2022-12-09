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
	<link href="/css/signup.css" type="text/css" rel="stylesheet"/>
	<link href="/css/ring_indicator.css" type="text/css" rel="stylesheet"/>
	<link href="/css/navigation.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="/scripts/buttons.js"></script>
	<script type="text/javascript" src="/scripts/join.js"></script>
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
<!-- NAVIGATION STARTS HERE -->
	<nav>
		<ul class="navigation">
			<div class="brand"> 
	<!-- Making menu icon clickable to display the navigation menu on smaller screens -->
				<i onclick="navToggle()" id="nav-icon" class="fa fa-navicon" style="font-size:24px"></i> 
			</div>
			 <a href="/" class="w3-bar-item w3-button w3-wide">
				<img class="img-nav" src="/images/logo_square.png" alt="WCS">
			</a>
		</ul>
	</nav>

	<section class="form">
	<div class="center">
		<form name="form" id="form" action="/api/login" method="POST">
			<input type="text" id="username" name="username" value="" autocomplete="username" placeholder = "Username" oninput="checkInfo()" required>
			<input type="password" id="password" name="password" value="" autocomplete="current-password" placeholder = "Password" oninput="checkInfo()" onkeyup="checkInfo()" required>
			<input type="number" id="auth_code" name="auth_code" value="" placeholder = "2FA code"><br>
			<button type="submit" name="submit" id="submit" form="form" hidden>Login</button>
			<p>Not with us yet?<a href="/signup"> Sign Up Here</a></p>
		</form>
	</div>
	</section>
</body>
</html>