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
	<title>WCS Banking</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="https://fonts.cdnfonts.com/css/pepsi-cyr-lat" rel="stylesheet">
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
	body,h1,h2,h3,h4,h5,h6 {font-family: "Raleway", sans-serif}

	body, html {
 		height: 100%;
 		line-height: 1.8;
	}

	/* Full height image header */
	.bgimg-1 {
		background-position: center;
		background-size: cover;
		background-image: url("https://i.pinimg.com/originals/dc/85/19/dc851996a8570569a7b5f02584fc3add.jpg");
		min-height: 100%;
	}

	.w3-bar .w3-button {
		padding: 16px;
	}

	.img-nav {
    height: auto;
    width: auto;
    max-height: 72px;
    max-width: 72px;
}

</style>
</head>
<body>
	
<!-- Navbar (sit on top) -->
<div class="w3-top">
  <div class="w3-bar w3-white w3-card" id="myNavbar">
    <a href="/index" class="w3-bar-item w3-button w3-wide">
		<img class = "img-nav" src = "/images/wcs.png" alt = "WCS">
	</a>
  </div>
</div>

<!-- Header with full-height image -->
<header class="bgimg-1 w3-display-container w3-grayscale-min" id="home">
  <div class="w3-display-left w3-text-white" style="padding:48px">
		<span class="w3-jumbo w3-hide-small">Welcome to WCS Banking</span><br>
		<span class="w3-xxlarge w3-hide-large w3-hide-medium">Welcome to WCS Banking</span><br>
		<span class="w3-large">A banking system of the people, by the people, for the people!</span>
		<?php if(is_null($first_name)): ?>
		<p><a href="/signup" class="w3-button w3-white w3-padding-large w3-large w3-margin-top w3-opacity w3-hover-opacity-off">Learn more and start today</a></p>
		<p><a href="/login" class="w3-button w3-white w3-padding-large w3-large w3-margin-top w3-opacity w3-hover-opacity-off">Already a member? Login here</a></p>
		<?php else: ?>
		<p><a href="/profile" class="w3-button w3-white w3-padding-large w3-large w3-margin-top w3-opacity w3-hover-opacity-off">Check my profile</a></p>
		<p><a href="/api/logout" class="w3-button w3-white w3-padding-large w3-large w3-margin-top w3-opacity w3-hover-opacity-off">Logout</a></p>	
		<?php endif; ?>
	</div> 
</header>

<!-- About Section -->
<div class="w3-container" style="padding:128px 16px" id="about">
  <h3 class="w3-center">ABOUT WCS BANKING</h3>
  <p class="w3-center w3-large">Why choose us?</p>
  <div class="w3-row-padding w3-center" style="margin-top:64px">
	<p> WCS Banking is a peer-to-peer system of banking in which people pledge the value of their digital property
        as collateral to borrow from other users. In the world of WCS Banking, all depositors are credited with a
        login wallet they can use to enter their collateral for lending, and this collateral can be used to borrow
        from the depositors who have deposited it. Leveraging this system, the depositors then lend their
        collateral to the borrowers, who may be individuals, businesses, or governments. As collateral is repaid,
        the depositors get paid, and new collateral enters the system!</p>
  </div>
</div>
</body>
</html>
