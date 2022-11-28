<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/constants.php";

try{
	$db = new DataBase();
}catch (PGException $exception){
	http_response_code(500);
	echo $exception->getMessage();
	return;
}

$result = $db->query("SELECT subquery.name, address FROM ( SELECT name, cast(a.number AS TEXT) || ' ' || a.direction || ' ' || a.street_name || ', ' || a.city || ', ' || a.state || ', ' || a.zipcode AS address FROM branch JOIN addresses a on a.id = branch.address) subquery;");
if(!$result){
	http_response_code(500);
	header("Response: " . error_get_last());
	return;
}

$numRows = pg_affected_rows($result);
$dct = array();

while($row = pg_fetch_array($result)){
	$dct[$row["name"]] = $row["address"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Account Signup</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
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
	<input type="text" id="username" name="username" value="" required autocomplete="username"><br>

	<label for="password">Password: </label>
	<input type="password" id="password" name="password" value="" required autocomplete="new-password" minlength="8"><br>

	<label for="fullname">Fullname: </label>
	<input type="text" id="fullname" name="fullname" value="" required autocomplete="name"><br>

	<label for="email">Email: </label>
	<input type="email" id="email" name="email" value="" required autocomplete="email" pattern="^(([^<>()\[\]\\.,;:\s@]+(\.[^<>()\[\]\\.,;:\s@]+)*)|(.+))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$"><br>

	<label for="phone">Telephone Number:</label>
	<input name="phone" id="phone" value="" type="tel" required autocomplete="tel" pattern="\([0-9]{3}\)?-?[0-9]{3}-?[0-9]{4}"><br>

	<label for="address_number">Address: </label>
	<input type="number" id="address_number" name="address_number" placeholder="3301" required>

	<select name="direction" id="direction">
		<option value="None"></option>
		<option value="N">North</option>
		<option value="E">East</option>
		<option value="S">South</option>
		<option value="W">West</option>
	</select>
	<input type="text" name="streetname" id="streetname" placeholder="Streetname" required>,
	<input type="text" name="city" id="city" placeholder="City" required>,
	<input type="text" name="state" id="state" placeholder="State Abbreviation" required maxlength="2">,
	<input type="number" name="zipcode" id="zipcode" placeholder="Zipcode" required min="10000" max="99999" autocomplete="postal-code"><br>
	<label for="apt">Apt/Unit: </label><input type="text" name="apt" id="apt" value=""><br>

	<label for="branch">Your favorite (or closest) branch: </label><select name="branch" id="branch" required>
		<?php foreach($dct as $key => $value) { ?>
			<option value="<?php echo $key?>"><?php echo $value ?></option>
		<?php } ?>
	</select><br>

	<input type="submit" name="submit" value="Sign up!">
</form>

</body>
</html>