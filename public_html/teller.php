<?php

require_once "api/constants.php";
require_once "api/ClassFiles/CookieManager.php";
require_once "api/ClassFiles/Views.php";
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Teller Home Page</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/employee_pages.css" type="text/css" rel="stylesheet"/>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
</head>
<body class="employee">
	<h2>Welcome <?php echo $teller->getName()?>::Teller!</h2>
	<h3>This is your homepage.</h3>

	<button class="employee_forms" onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Find an Account Balance</button>

	<div id="id01" class="modal">
		<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
		<form class="modal-content" method="post" action="/action_page.php">
			<div class="container">
				<h1>Find the balance of a customer's account</h1>
				<p>Please fill in the following form with the Customer's information.</p>

				<hr>
				
				<div class="clearfix">
					<button type="button" onclick="document.getElementById('id01').style.display='none'" class="employee_forms cancelbtn">Cancel</button>
					<button type="submit" class="employee_forms signupbtn">Find Balance</button>
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