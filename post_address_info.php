***REMOVED***
require "DataBase.php";

try***REMOVED***
	$db = new DataBase();
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(501);
	echo $pgException;
	return;
***REMOVED***

if (!isset($_POST['auth_code'])) ***REMOVED***
	http_response_code(400);
	echo "The authorization code must be given.";
	return;
***REMOVED***

if (!($_POST['streetNumber'] || $_POST['direction'] || $_POST['streetName'] || $_POST['city'] || $_POST['state'] || $_POST['zipcode'])) ***REMOVED***
	http_response_code(400);
	echo "Information must be given.";
	return;
***REMOVED***

$streetNumber = $_POST['streetNumber'] ?? null;
$direction = $_POST['direction'] ?? null;
$streetName = $_POST['streetName'] ?? null;
$city = $_POST['city'] ?? null;
$state = $_POST['state'] ?? null;
$zipcode = $_POST['zipcode'] ?? null;

try ***REMOVED***
	$result = $db->postAddress($_POST['id'], $streetNumber, $direction, $streetName, $city, $state, $zipcode);
***REMOVED*** catch (Exception $e)***REMOVED***
	http_response_code(409);
	echo $e->getMessage();
***REMOVED***

if ($result) ***REMOVED***
	http_response_code(200);
	echo "Address successfully updated. \n " . $result;
	return;
***REMOVED***
else***REMOVED***
	http_response_code(400);
	echo "Username or password wrong is wrong";
***REMOVED***