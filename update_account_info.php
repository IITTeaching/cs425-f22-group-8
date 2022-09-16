***REMOVED***
require "DataBase.php";
$db = new DataBase();

if (isset($_POST['auth_code'])) ***REMOVED***
	http_response_code(400);
	echo "The auth_code must be given.";
	return;
***REMOVED***

if (!$db->dbConnect()) ***REMOVED***
	http_response_code(500);
	echo "Error: Database connection";
	return;
***REMOVED***

$streetNumber = $_POST['streetNumber'] ?? null;
$direction = $_POST['direction'] ?? null;
$streetName = $_POST['streetName'] ?? null;
$city = $_POST['city'] ?? null;
$state = $_POST['state'] ?? null;
$zipcode = $_POST['zipcode'] ?? null;

$result = $db->postAddress($_POST['id'], $streetNumber, $direction, $streetName, $city, $state, $zipcode);
if ($result) ***REMOVED***
	http_response_code(200);
	echo "Login Success";
	return;
***REMOVED***
else***REMOVED***
	http_response_code(400);
	echo "Username or password wrong is wrong";
***REMOVED***