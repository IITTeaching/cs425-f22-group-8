<?php

require_once "api/constants.php";
require_once "api/ClassFiles/CookieManager.php";
require_once "api/ClassFiles/Employee/Teller.php";
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
	$teller = Teller::fromUsername($username);
} catch (PGException | InvalidArgumentException $e){
	respond($e->getMessage());
	http_response_code(500);
	return;
}

try{
	$db = new DataBase();
}catch (PGException | InvalidArgumentException $exception){
	http_response_code(500);
	echo $exception->getMessage();
	return;
}

$result = $db->query("SELECT * FROM branch_info;");
if(!$result){
	http_response_code(500);
	respond(error_get_last());
	return;
}

$dct = array();

while($row = pg_fetch_array($result)){
	$dct[$row["name"]] = $row["address"];
}
$state_result = $db->query("SELECT * FROM state_options");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Teller Home Page</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/employee_pages.css" type="text/css" rel="stylesheet"/>
</head>
<body>
	<h2>Welcome <?php echo $teller->getName()?>::Teller!</h2>
	<h3>This is your homepage.</h3>

	<button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Find an Account Balance</button>

	<div id="id01" class="modal">
		<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
		<form class="modal-content" action="/action_page.php">
			<div class="container">
				<h1>Find the balance of a customer's account</h1>
				<p>Please fill in the following form with the Customer's information.</p>

				<hr>
				
				<div class="clearfix">
					<button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
					<button type="submit" class="signupbtn">Find Balance</button>
				</div>
			</div>
		</form>
	</div>

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