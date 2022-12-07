<?php

require_once "api/ClassFiles/Authentication.php";

$username = $_GET["username"];
$enable = isset($_GET["enable"]) && ($_GET["enable"] == "true");

if($enable){
	$auth = new Authentication();
	$key = $auth->createSecretKey();
	$qrcode = $auth->generateQRCode($username, $key);
	$auth->setTOTP($username, $key);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes" />
	<title>WCS Authentication</title>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
</head>
<body>
<?php if(!$enable): ?>
<p>We understand that you don't want 2FA, and we have not added it for you. When you log in, don't put anything in the Auth Code box.</p>
<?php else: ?>
	<h1>Your 2FA Code</h1>
	<p>Scan Me</p>
	<div class="container">
		<img src='<?= $qrcode ?>' alt='QR Code' width='400' height='400'>
	</div>
	<p>If you cannot scan me, enter the code manually</p>
	<code>
		<?php echo $key?>
	</code>
	<button onclick="add()">Click Me When You Have Your Code</button>
<?php endif ?>
</body>
</html>
