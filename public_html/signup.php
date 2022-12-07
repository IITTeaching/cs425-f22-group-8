<?php
require_once "api/ClassFiles/Views.php";
require_once "api/constants.php";

try{
	$views = new Views();
}catch (PGException $exception){
	http_response_code(500);
	echo $exception->getMessage();
	return;
}

try{
	$branches = $views->getBranchInfo();
} catch (PGException $e){
	http_response_code(500);
	respond(error_get_last());
	return;
}

try{
	$states = $views->getStateOptions();
} catch (PGException $e){
	http_response_code(500);
	respond(error_get_last());
	return;
}
// TODO: Check if there is a way to make the form less aggressive, it forces its way from one element to the next, skipping non-required ones, and not allowing you to go back until everything required has something in it.
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Account Signup</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="/css/ring_indicator.css" type="text/css" rel="stylesheet"/>
	<link href="/css/signup.css" type="text/css" rel="stylesheet"/>
    <link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
    <link href="/css/back_button.css" type="text/css" rel="stylesheet"/>
    <script type="text/javascript" src="/scripts/buttons.js"></script>

	<script type="text/javascript">
		function onOpen(){
			document.getElementById("username").addEventListener("input", checkUsername);
			document.getElementById("fullname").addEventListener("input", checkName);
			document.getElementById("email").addEventListener("input", checkEmail);
			document.getElementById("phone").addEventListener("input", checkPhoneNumber);
			document.getElementById("password").addEventListener("input", checkPassword);
			document.getElementById("zipcode").addEventListener("input", checkZipcode);
			//document.getElementById("direction").input("keyup", checkDirection);
			document.getElementById("address_number").addEventListener("input", checkAddressNumber);
			document.getElementById("streetname").addEventListener("input", checkStreetNumber);
			document.getElementById("state").addEventListener("input", checkState);
			document.getElementById("city").addEventListener("input", checkState);
			document.getElementById("branch").addEventListener("input", checkBranch);
		}

		function checkUsername(){
			let username = document.forms["signup_form"]["username"];
			if(username.value.length === 0){
				username.setCustomValidity("The username cannot be empty.");
				username.reportValidity();
				return false;
			}
			username.setCustomValidity("");
			return true;
		}

		function checkName(){
			let name = document.forms["signup_form"]["fullname"];
			if(name.value.length === 0){
				name.setCustomValidity("Your name is required.");
				name.reportValidity();
				return false;
			}
			name.setCustomValidity("");
			return true;
		}

		function checkEmail(){
			let email = document.forms["signup_form"]["email"];
			let email_regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

			if(!email_regex.test(email.value.toLowerCase()) || email.validity.typeMismatch){
				email.setCustomValidity("Your email must be valid.");
email.reportValidity();
				return false;
			}
			email.setCustomValidity("");
			return true;
		}

		function checkPhoneNumber(){
			let phone = document.forms["signup_form"]["phone"];

			let phone_number_regex = /\(?(\d{3})\)?-?(\d{3})-?(\d{4})/;
			if(!phone_number_regex.test(phone.value)){
				phone.setCustomValidity("Please enter a valid phone number.");
				phone.reportValidity();
				return false;
			}
			phone.setCustomValidity("");
			return true;
		}

		function checkPassword(){
			let password = document.forms["signup_form"]["password"];
			let value = password.value;

			if(value.length < 8){
				password.setCustomValidity("Your password must be at least 8 characters long.");
				password.reportValidity();
				return false;
			}

			let password_number_regex = /.*\d.*/;
			if(!password_number_regex.test(value)){
				password.setCustomValidity("Your password must contain a number.");
				password.reportValidity();
				return false;
			}

			let upper_regex = /.*[A-Z].*/;
			if(!upper_regex.test(value)){
				password.setCustomValidity("Your password must have at least one upper case letter.");
				password.reportValidity();
				return false;
			}

			let lower_regex = /.*[a-z].*/;
			if(!lower_regex.test(value)){
				password.setCustomValidity("Your password must have at least one lower case letter.");
				password.reportValidity();
				return false;
			}

			let symbol_regex = /.*[!#$@%()^&;:-].*/;
			if(!symbol_regex.test(value)){
				password.setCustomValidity("Your password must have one of the following characters in it `!#$@%()^&;:-`.");
				return false;
			}

			password.setCustomValidity("");
			return true;
		}

		function checkZipcode(){
			let zipcode = document.forms["signup_form"]["zipcode"];

			if(zipcode.value < 10000 || zipcode.value > 99999){
				zipcode.setCustomValidity("Please enter a valid zipcode.");
				zipcode.reportValidity();
				return false;
			}
			zipcode.setCustomValidity("");
			return true;
		}

		function checkAddressNumber(){
			let address = document.forms["signup_form"]["address_number"];

			if(address.value <= 0){
				address.setCustomValidity("Please enter an address number.");
				address.reportValidity();
				return false;
			}
			address.setCustomValidity("");
			return true;
		}

		function checkStreetNumber(){
			let streetname = document.forms["signup_form"]["streetname"];

			if(streetname.value.length === 0){
				streetname.setCustomValidity("The street number cannot be empty.");
				streetname.reportValidity();
				return false;
			}
			streetname.setCustomValidity("");
			return true;
		}

		function checkCity(){
			let city = document.forms["signup_form"]["city"];

			if(city.value.length === 0){
				city.setCustomValidity("You must provide your city.");
				city.reportValidity();
				return false;
			}
			city.setCustomValidity("");
			return true;
		}

		function checkState(){
			let state = document.forms["signup_form"]["state"];

			if(state.value.length !== 2){
				state.setCustomValidity("You must choose the proper state.");
				state.reportValidity();
				return false;
			}
			state.setCustomValidity("");
			return true;
		}

		function checkBranch(){
			let branch = document.forms["signup_form"]["branch"];

			if(branch.value.length === 0){ // TODO: Check if there is a way to see if branch is in the branches datalist.
				branch.setCustomValidity("You must choose the location of one of our branches.");
				branch.reportValidity();
				return false;
			}
			branch.setCustomValidity("");
			return true;
		}

		function validate(){
			if(!checkUsername()) { return false; }
			if(!checkPassword()) { return false; }
			if(!checkName()) { return false; }
			if(!checkEmail()) { return false; }
			if(!checkPhoneNumber()) { return false; }
			if(!checkAddressNumber()) { return false; }
			if(!checkStreetNumber()) { return false; }
			if(!checkCity()) { return false; }
			if(!checkState()) { return false; }
			if(!checkZipcode()) { return false; }
			if(!checkBranch()) { return false; }

			return true;
		}

		function checkInfo(){
			if(!validate()){
				missingInfo();
			} else{
				allGood();
			}
		}
	</script>

</head>
<body onload="onOpen()">

<!-- NAVIGATION STARTS HERE -->
	<nav>
		<ul class="navigation">
			<div class="brand"><img src="/images/wcs.png" alt="LOGO" /> 
<!-- Making menu icon clickable to display the navigation menu on smaller screens -->
				<i onclick="navToggle()" id="nav-icon" class="fa fa-navicon" style="font-size:24px"></i> 
			</div>
			 <!-- Your Logo Goes Here -->
			<div id="toggle" class="nav-container">
				<a class="left" href="/index"><li>Go Back Home</li></a>
			</div>
		</ul>
	</nav>

<section class="form">
<div class="center">

	<form name="signup_form" id="signup_form" action="/api/signup" method="POST" onsubmit="return validate()">
		<input type="text" id="username" name="username" value="" oninput="checkInfo()" required autocomplete="username"><br>
		<input type="password" id="password" name="password" value="" oninput="checkInfo()" required autocomplete="new-password" minlength="8"><br>
		<input type="text" id="fullname" name="fullname" value="" oninput="checkInfo()" required autocomplete="name"><br>
		<input type="email" id="email" name="email" value="" oninput="checkInfo()" required autocomplete="email" pattern="^(([^<>()\[\]\\.,;:\s@]+(\.[^<>()\[\]\\.,;:\s@]+)*)|(.+))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$"><br>
		<input name="phone" id="phone" value="" type="tel" oninput="checkInfo()" required autocomplete="tel" pattern="\(?[0-9]{3}\)?-?[0-9]{3}-?[0-9]{4}"><br>
		<input type="number" id="address_number" name="address_number" placeholder="3301" oninput="checkInfo()" min="0" inputmode="decimal" required>
		<input type="text" class="input1" id="direction" name="direction" pattern="[N|E|S|W]?" list="directions" placeholder="Direction">
		<datalist id="directions">
			<option>N</option>
			<option>E</option>
			<option>S</option>
			<option>W</option>
		</datalist>
		<input type="text" name="streetname" id="streetname" placeholder="Street Name" oninput="checkInfo()" required>
		<input type="text" name="city" id="city" placeholder="City" oninput="checkInfo()" required><br>
		<input class = "input1" name = "state" id="state" oninput="checkInfo()" list="states" placeholder="State" required>
			<datalist id="states">
				<?php foreach($states as $state) {?>
					<?php echo $state . PHP_EOL; ?>
				<?php }?>
			</datalist>
		<input type="number" name="zipcode" id="zipcode" placeholder="Zipcode" oninput="checkInfo()" required min="10000" max="99999" autocomplete="postal-code" inputmode="decimal"><br>
		<input type="text" name="apt" id="apt" placeholder="Apt/Unit # (Optional)" value=""><br>
		<input name="branch" id="branch" oninput="checkInfo()" list="branches" placeholder="Branch" required>
				<datalist id="branches">
					<?php foreach($branches as $key => $value) { ?>
					<option value="<?php echo $key?>"><?php echo $value ?></option>
					<?php } ?>
				</datalist><br>
		<button type="submit" name="submit" id="submit" form="signup_form" hidden>Sign Up!</button>
		<p>Already have an account? <a href="/login">Login Here</a></p>
	</form>
</div>
</section>
</body>
</html>