<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Account Signup</title>
</head>
<body>

<script>
	function validate(){
		let form = document.forms["signup_form"];
		let password = form["password"].value;
		let email = form["email"].value;

		if(password.length < 8){
			alert("Your password must be at least 8 characters long.");
			return false;
		}

		let email_regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		if(!email_regex.test(email.toLowerCase())){
			alert("Your email must be valid.");
			return false;
		}

		let number_regex = /.*\d.*/;
		if(!number_regex.test(password)){
			alert("Your password must have at least one number in it.");
			return false;
		}

		let upper_regex = /.*[A-Z].*/;
		if(!upper_regex.test(password)){
			alert("Your password must have at least one upper case letter.");
			return false;
		}

		let lower_regex = /.*[a-z].*/;
		if(!lower_regex.test(password)){
			alert("Your password must have at least one lower case letter.");
			return false;
		}

		let symbol_regex = /.*[!#$@%()^&;:-].*/;
		if(!symbol_regex.test(password)){
			alert("Your password must have one of the following characters in it `!#$@%()^&;:-`.");
			return false;
		}

		return true;
	}
</script>

<form name="signup_form" action="/api/signup" method="POST" onsubmit="return validate()">
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