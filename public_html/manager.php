<?php

require_once "api/constants.php";
require_once "api/ClassFiles/CookieManager.php";
require_once "api/ClassFiles/DataBase.php";
require_once "api/ClassFiles/Employee/Manager.php";
require_once "api/Exceptions/PGException.php";


$cookie = new CookieManager();
$username = $cookie->getCookieUsername();
if (!$username) {
	respond("You are registered as logged in, but there is no user attached to this session.");
	http_response_code(500);
	$cookie->deleteCookie();
	return;
}

if(!$cookie->isEmployee()){
	header("Location: " . HTTPS_HOST . "/profile");
	respond("You should not be accessing this page.");
	http_response_code(301);
	return;
}

try{
	$manager = Manager::fromUsername($username);
} catch (PGException | InvalidArgumentException $e){
	respond($e->getMessage());
	http_response_code(500);
	return;
}

try{
	$db = new DataBase();
	$views = new Views();
}catch (PGException | InvalidArgumentException $exception){
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Manager Home Page</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/employee_pages.css" type="text/css" rel="stylesheet"/>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
</head>
<body>
	<h2>Welcome <?php echo $manager->getName()?>::Manager!</h2>
	<h3>This is your homepage.</h3>

	<button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Add New Employee</button>

	<div id="id01" class="modal">
		<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
		<form class="modal-content" action="/api/hire">
			<div class="container">
				<h1>Add Employee</h1>
				<p>Please fill in the following form with the Employee's information.</p>

				<hr>
				<label class="form_label" for="fullname">Name</label>
				<input type="text" placeholder="Enter Full Name" name="fullname" id="fullname" required>

				<label class="form_label" for="role">Role</label>
				<input type="text" placeholder="Enter Role (Teller, Loan Manager, Manager)" name="role" id="role" pattern="[Teller|Loan Manager|Manager]" required>

				<label class="form_label" for="address_num">Address</label>
				<input type="number" placeholder="3301" name="address_num" id="address_num" min="0" required>

				<label class="form_label" for="direction">Direction</label>
				<input type="text" name="direction" id="direction" pattern="[N|E|S|W]?" list="directions" placeholder="Direction">
				<datalist id="directions">
					<option>N</option>
					<option>E</option>
					<option>S</option>
					<option>W</option>
				</datalist>

				<label class="form_label" for="streetname">Street</label>
				<input type="text" name="streetname" id="streetname" placeholder="Street Name" required>

				<label class="form_label" for="city">City</label>
				<input type="text" name="city" id="city" placeholder="City" required>

				<label class="form_label" for="state">State</label>
				<input type="text" name="state" id="state" placeholder="State" list="states" required>
				<datalist id="states">
					<?php foreach($states as $state) {?>
						<?php echo $state . PHP_EOL; ?>
					<?php }?>
				</datalist>

				<label class="form_label" for="zipcode">Zipcode</label>
				<input type="text" name="zipcode" id="zipcode" placeholder="Zipcode" required>

				<label class="form_label" for="apt">Apt/Unit</label>
				<input type="text" name="apt" id="apt" placeholder ="Apt/Unit #" value="">

				<label class="form_label" for="ssn">Social Security Number</label>
				<input type="number" name="ssn" id="ssn" placeholder="Social Security Number" pattern="\d{9}" value="" required>

				<label class="form_label" for="branch">Branch</label>
				<input type="text" name="branch" id="branch" list="branches" placeholder="Branch" required>
				<datalist id="branches">
					<?php foreach($branches as $key => $value) { ?>
						<option value="<?php echo $key?>"><?php echo $value ?></option>
					<?php } ?>
				</datalist>

				<div class="clearfix">
					<button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
					<button type="submit" class="signupbtn">Add Employee</button>
				</div>
			</div>
		</form>
	</div>

	<nav class="floating-menu">
		<h3>Hello <?php echo $username?></h3>
		<a href="/api/logout">Logout</a>
	</nav>

	<script>
	// Get the modal
	let modal = document.getElementById('id01');

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	  if (event.target === modal) {
		modal.style.display = "none";
	  }
	}
	</script>
</body>
</html> 