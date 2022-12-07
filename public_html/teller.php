<?php

require_once "api/constants.php";
require_once "api/ClassFiles/DataBase.php";

try{
	$db = new DataBase();
}catch (PGException $exception){
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
<html lang="">
<head>
	<meta charset="UTF-8">
	<title>WCS Teller Home Page</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<style>
		body {
			font-family: Arial, Helvetica, sans-serif;
			box-sizing: border-box;
			background-color: rgb(128, 128, 128);
		}

		/* Full-width input fields */
		input[type=text], input[type=password], input[type=number] {
			width: 100%;
			padding: 15px;
			margin: 5px 0 22px 0;
			display: inline-block;
			border: none;
			box-sizing: border-box;
			background: #f1f1f1;
		}

		/* Add a background color when the inputs get focus */
		input[type=text]:focus, input[type=password]:focus, input[type=number]:focus {
			background-color: #ddd;
			outline: none;
		}

		/* Set a style for all buttons */
		button {
			background-color: #04AA6D;
			color: white;
			padding: 14px 20px;
			margin: 8px 0;
			border: none;
			cursor: pointer;
			width: 100%;
			opacity: 0.9;
		}

		button:hover {
			opacity:1;
		}

		/* Extra styles for the cancel button */
		.cancelbtn {
			padding: 14px 20px;
			background-color: #f44336;
		}

		/* Float cancel and signup buttons and add an equal width */
		.cancelbtn, .signupbtn {
			float: left;
			width: 50%;
		}

		/* Add padding to container elements */
		.container {
			padding: 16px;
		}

		/* The Modal (background) */
		.modal {
			display: none; /* Hidden by default */
			position: fixed; /* Stay in place */
			z-index: 1; /* Sit on top */
			left: 0;
			top: 0;
			width: 100%; /* Full width */
			height: 100%; /* Full height */
			overflow: auto; /* Enable scroll if needed */
			background-color: #434E4A;
			padding-top: 50px;
		}

		/* Modal Content/Box */
		.modal-content {
			background-color: #F0F0F0;
			margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
			border: 1px solid #888;
			width: 80%; /* Could be more or less, depending on screen size */
		}

		/* Style the horizontal ruler */
		hr {
			border: 1px solid #EAEAEA;
			margin-bottom: 25px;
		}

		/* The Close Button (x) */
		.close {
			position: absolute;
			right: 35px;
			top: 15px;
			font-size: 40px;
			font-weight: bold;
			color: #EAEAEA;
		}

		.close:hover,
		.close:focus {
			color: #f44336;
			cursor: pointer;
		}

		/* Clear floats */
		.clearfix::after {
			content: "";
			clear: both;
			display: table;
		}

		/* Change styles for cancel button and signup button on extra small screens */
		@media screen and (max-width: 300px) {
			.cancelbtn, .signupbtn {
				width: 100%;
			}
		}

		.form_label{
			font-weight: bold;
		}
	</style>
</head>
<body>
	<h2>Welcome Teller!</h2>
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
					<button type="submit" class="signupbtn">Add Employee</button>
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