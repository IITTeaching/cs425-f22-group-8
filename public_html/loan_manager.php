<?php

require_once "api/constants.php";
require_once "api/ClassFiles/CookieManager.php";
require_once "api/ClassFiles/Views.php";
require_once "api/ClassFiles/Employee/LoanShark.php";
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
	$shark = LoanShark::fromUsername($username);
} catch (PGException | InvalidArgumentException $e){
	respond($e->getMessage());
	http_response_code(500);
	return;
}

try{
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

try{
	$loans = $shark->getRequestedLoans();
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
	<title>WCS Loan Manager Home Page</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/employee_pages.css" type="text/css" rel="stylesheet"/>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>

	<script>
		function getLoanListener(){
			let json = JSON.parse(this.responseText);
			// TODO: Populate the information table with the json data.
		}

		function getLoanRequest(){
			let loan_id = document.getElementById("loan_num");

			const req = new XMLHttpRequest();
			req.addEventListener("load", getLoanListener);
			req.open("POST", "https://cs425.lenwashingtoniii.com/api/get_loan_request");
			req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			req.send(`request_id=${loan_id}`);
		}
	</script>
</head>
<body class="employee">
<h2>Welcome <?php echo $shark->getName()?>::Loan Manager!</h2>
<h3>This is your homepage.</h3>

<button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Approve/Deny Loan Request</button>

<div id="id01" class="modal">
	<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
	<form class="modal-content" method="post" action="/api/hire">
		<div class="container">
			<h1>Find A Requested Loan</h1>
			<p>Please fill in the following form with the Customer's information.</p>
			<hr>
			<label class="form_label" for="loan_num">Loan Number</label>
			<input type="text" placeholder="Loan Request Number" name="loan_num" id="loan_num" onclick="getLoanRequest()" pattern="(\d+)" required>

			<div class="clearfix">
				<button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
				<button onclick="document.getElementById('id02').style.display='block'" style="width:auto;" class="signupbtn">View Loan Info</button>

				<div id="id02" class="modal">
				<span onclick="document.getElementById('id02').style.display='none'" class="close" title="Close Modal">&times;</span>
					<div class="container">
						<!-- figure out how to display loan info -->
					</div>
				</div>
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