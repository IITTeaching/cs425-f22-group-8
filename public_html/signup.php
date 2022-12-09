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
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Account Signup</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="/css/ring_indicator.css" type="text/css" rel="stylesheet"/>
	<link href="/css/signup.css" type="text/css" rel="stylesheet"/>
	<link href="/css/navigation.css" type="text/css" rel="stylesheet"/>
    <link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
    <link href="/css/back_button.css" type="text/css" rel="stylesheet"/>
    <script type="text/javascript" src="/scripts/buttons.js"></script>
    <script type="text/javascript" src="/scripts/join.js"></script>

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
			document.getElementById("city").addEventListener("input", checkCity);
			document.getElementById("branch").addEventListener("input", checkBranch);
		}
		
		function checkUsername(){
			let username = document.forms["signup_form"]["username"];
			let error_field = document.getElementById("username_error");
			if(username.value.length === 0){
				_setError(error_field, "The username cannot be empty.\r\n");
				return false;
			} else{
				_removeError(error_field)
				return true;
			}
		}

		function checkName(){
			let name = document.forms["signup_form"]["fullname"];
			let error_field = document.getElementById("fullname_error");
			if(name.value.length === 0){
				_setError(error_field, "Your name is required.")
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkEmail(){
			let email = document.forms["signup_form"]["email"];
			let email_regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			let error_field = document.getElementById("email_error");

			if(!email_regex.test(email.value.toLowerCase()) || email.validity.typeMismatch){
				_setError(error_field, "Your email must be valid.");
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkPhoneNumber(){
			let phone = document.forms["signup_form"]["phone"];
			let error_field = document.getElementById("phone_error");

			let phone_number_regex = /\(?(\d{3})\)?-?(\d{3})-?(\d{4})/;
			if(!phone_number_regex.test(phone.value)){
				_setError(error_field, "Please enter a valid phone number.");
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkPassword(){
			let password = document.forms["signup_form"]["password"];
			let value = password.value;
			let error_field = document.getElementById("password_error");
			
			if(value.length < 8){
				_setError(error_field, "Your password must be at least 8 characters long.");
				return false;
			}

			let password_number_regex = /.*\d.*/;
			if(!password_number_regex.test(value)){
				_setError(error_field, "Your password must contain a number.");
				return false;
			}

			let upper_regex = /.*[A-Z].*/;
			if(!upper_regex.test(value)){
				_setError(error_field, "Your password must have at least one upper case letter.");
				return false;
			}

			let lower_regex = /.*[a-z].*/;
			if(!lower_regex.test(value)){
				_setError(error_field, "Your password must have at least one lower case letter.");
				return false;
			}

			let symbol_regex = /.*[!#$@%()^&;:-].*/;
			if(!symbol_regex.test(value)){
				_setError(error_field, "Your password must have one of the following characters in it `!#$@%()^&;:-`.");
				return false;
			}

			_removeError(error_field);
			return true;
		}

		function checkZipcode(){
			let zipcode = document.forms["signup_form"]["zipcode"];
			let error_field = document.getElementById("zipcode_error");

			if(zipcode.value < 10000 || zipcode.value > 99999) {
				_setError(error_field, "Please enter a valid zipcode.");
				return false;
			}
			
			_removeError(error_field);
			return true;
		}

		function checkAddressNumber(){
			let address = document.forms["signup_form"]["address_number"];
			let error_field = document.getElementById("address_number_error");
			
			if(address.value <= 0){
				_setError(error_field, "Please enter an address number.");
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkStreetNumber(){
			let streetname = document.forms["signup_form"]["streetname"];
			let error_field = document.getElementById("streetname_error");

			if(streetname.value.length === 0){
				_setError(error_field, "The street number cannot be empty.");
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkCity(){
			let city = document.forms["signup_form"]["city"];
			let error_field = document.getElementById("city_error");

			if(city.value.length === 0){
				_setError(error_field, "You must provide your city.");
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkState(){
			let state = document.forms["signup_form"]["state"];
			let error_field = document.getElementById("state_error");

			if(state.value.length !== 2){
				_setError(error_field, "You must choose the proper state.");
				return false;
			}
			_removeError(error_field);
			return true;
		}

		function checkBranch(){
			let branch = document.forms["signup_form"]["branch"];
			let error_field = document.getElementById("branch_error");

			if(branch.value.length === 0){ // TODO: Check if there is a way to see if branch is in the branches datalist.
				_setError(error_field, "You must choose the location of one of our branches.");
				return false;
			}
			_removeError(error_field);
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

	<form name="signup_form" id="signup_form" action="/api/signup" method="POST" onsubmit="return validate()">
		<input type="text" id="username" name="username" placeholder="Username" value="" oninput="checkInfo()" required autocomplete="username">
		<div id="username_error" class="emsg"></div>
		<input type="password" id="password" name="password" placeholder="Password" value="" oninput="checkInfo()" required autocomplete="new-password" minlength="8">
		<div id="password_error" class="emsg"></div>

		<input type="text" id="fullname" name="fullname" placeholder="Full Name" value="" oninput="checkInfo()" required autocomplete="name">
		<div id="fullname_error" class="emsg"></div>
		<input type="email" id="email" name="email" value="" placeholder="Email Address" oninput="checkInfo()" required autocomplete="email" pattern="^(([^<>()\[\]\\.,;:\s@]+(\.[^<>()\[\]\\.,;:\s@]+)*)|(.+))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$">
		<div id="email_error" class="emsg"></div>
		<input name="phone" id="phone" value="" type="tel" placeholder="Phone No." oninput="checkInfo()" required autocomplete="tel" pattern="\(?[0-9]{3}\)?-?[0-9]{3}-?[0-9]{4}">
		<div id="phone_error" class="emsg"></div>
		<input class ="name-surname" type="number" id="address_number" name="address_number" placeholder="3301" oninput="checkInfo()" min="0" inputmode="decimal" required>
		<input class ="name-surname" type="text" class="input1" id="direction" name="direction" pattern="[N|E|S|W]?" list="directions" placeholder="Direction">
		<div id="address_number_error" class="emsg"></div>
		<div id="direction_error" class="emsg"></div>
		<datalist id="directions">
			<option>N</option>
			<option>E</option>
			<option>S</option>
			<option>W</option>
		</datalist>
		<input type="text" name="streetname" id="streetname" placeholder="Street Name" oninput="checkInfo()" required>
		<div id="streetname_error" class="emsg"></div>
		<input type="text" name="city" id="city" placeholder="City" oninput="checkInfo()" required>
		<div id="city_error" class="emsg"></div>
		<input class ="name-surname" name = "state" id="state" oninput="checkInfo()" list="states" placeholder="State" required>
			<datalist id="states">
				<?php foreach($states as $state) {?>
					<?php echo $state . PHP_EOL; ?>
				<?php }?>
			</datalist>
		<input class ="name-surname" type="number" name="zipcode" id="zipcode" placeholder="Zipcode" oninput="checkInfo()" required min="10000" max="99999" autocomplete="postal-code" inputmode="decimal">
		<div id="state_error" class="emsg"></div>
		<div id="zipcode_error" class="emsg"></div>
		<input type="text" name="apt" id="apt" placeholder="Apt/Unit # (Optional)" value="">
		<div id="apt_error" class="emsg"></div>
		<input name="branch" id="branch" oninput="checkInfo()" list="branches" placeholder="Branch" required>
		<div id="branch_error" class="emsg"></div>
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