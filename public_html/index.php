<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/ClassFiles/User.php";
require_once "api/Exceptions/PGException.php";
require_once "api/constants.php";

try{
	$db = new DataBase();
	$user = $db->getCurrentUserId();
} catch(PGException | InvalidArgumentException $e){
	http_response_code(500);
	respond($e->getMessage());
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
    <link href="/css/index.css" type="text/css" rel="stlyesheet"/>
    <link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
    <link href="https://fonts.cdnfonts.com/css/pepsi-cyr-lat" rel="stylesheet">
</head>
<body>
	<div id="content">
        <div class="WCS-header">
            <img src="/images/wcsbanner.png" alt="WCS banking logo">
        </div>
        <h1 class="title">Welcome to WCS Banking</h1>
        <h2>A banking system of the people, by the people, for the people!
        <br>
        <br>
            WCS Banking is a peer-to-peer system of banking in which people pledge the value of their digital property
            as collateral to borrow from other users. In the world of WCS Banking, all depositors are credited with a
            login wallet they can use to enter their collateral for lending, and this collateral can be used to borrow
            from the depositors who have deposited it. Leveraging this system, the depositors then lend their
            collateral to the borrowers, who may be individuals, businesses, or governments. As collateral is repaid,
            the depositors get paid, and new collateral enters the system!
        </h2>
		<nav class="floating-menu">
			<?php if(!$db->isLoggedIn()): ?>
			<h3>We sold you?</h3>
			<a href="/login">Log In</a>
			<a href="/signup">Sign Up</a>
			<?php else: ?>
			<h3>Hello <?php try {
					echo $user->getFirstName();
				} catch (PGException $e) {
					echo $e->getMessage();
			} ?></h3>
			<a href="/profile">Check My Profile</a>
			<a href="/api/logout">Logout</a>
			<?php endif; ?>

		</nav>
	</div>
</body>
</html>
