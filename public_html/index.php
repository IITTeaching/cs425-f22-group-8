<?php
require_once"api/ClassFiles/DataBase.php";
require_once"api/constants.php";

try{
	$db = new DataBase();
} catch(PGException $exception){
	http_response_code(500);
	header("Response: There was an internal database error, try again later. If this problem persists, contact the system administrator.");
	return;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Banking</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
</head>
<body>
	<div id="content">
		<h1 class="title">Welcome to WCS Banking</h1>
		<h2>A banking system of the people, by the people, for the people, EAGLE!</h2>
		<nav class="floating-menu">
			<?php if(!$db->isLoggedIn()): ?>
			<h3>We sold you?</h3>
			<a href="/login">Log In</a>
			<a href="/signup">Sign Up</a>
			<?php else: ?>
			<h3>Hello <?php try {
					echo $db->getFirstName();
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
