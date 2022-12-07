<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/ClassFiles/User.php";
require_once "api/Exceptions/PGException.php";
require_once "api/constants.php";

try{
	$cookie = new CookieManager();
	if($cookie->isValidCookie()){
		if($cookie->isEmployee()){
			$first_name = $cookie->getCookieUsername(); // TODO: If there's time, get the employee's name to put on the floating menu
		} else{
			$first_name = User::fromUsername($cookie->getCookieUsername())->getFirstName();
		}
	} else {
		$first_name = null;
	}
} catch(PGException | InvalidArgumentException $e){
	respond($e->getMessage());
	$first_name = null;
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
			<?php if(is_null($first_name)): ?>
			<h3>We sold you?</h3>
			<a href="/login">Log In</a>
			<a href="/signup">Sign Up</a>
			<?php else: ?>
			<h3>Hello <?php echo $first_name?></h3>
			<a href="/profile">Check My Profile</a> <!-- TODO: Switch with the respective employee type if the user is an employee. -->
			<a href="/api/logout">Logout</a>
			<?php endif; ?>

		</nav>
	</div>
</body>
</html>
