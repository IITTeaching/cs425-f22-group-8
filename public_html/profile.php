<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/constants.php";
require_once "api/constants.php";

try{
	$db = new DataBase();
	$user = $db->getCurrentUser();
} catch(PGException $PGException){
	http_response_code(500);
	header("Response: " . $PGException->getMessage());
	return;
}

if(!$db->isLoggedIn()){
	header("Location: " . HTTPS_HOST . "/");
}

$accounts = $user->getAccounts();
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Banking</title>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
</head>
<body>
<div id="content">
	<h1 class="title">Welcome to WCS Banking</h1>
	<table>

	</table>
	<nav class="floating-menu">
		<?php if(!$db->isLoggedIn()): ?>
			<h3>We sold you?</h3>
			<a href="/login">Log In</a>
			<a href="/signup">Sign Up</a>
		<?php else: ?>
			<h3>Hello <?php try {
					echo $user->getFirstName();
				} catch (PGException $e) {
					echo "Internal Server Error";
				} ?></h3>
			<a href="/profile">Check My Profile</a>
			<a href="/api/logout">Logout</a>
		<?php endif; ?>

	</nav>
</div>
</body>
</html>
