***REMOVED***
require "DataBase.php";

if (!(isset($_POST['username']) && isset($_POST['password']))) ***REMOVED***
	http_response_code(400);
	echo "All fields are required.";
	return;
***REMOVED***

try***REMOVED***
	$db = new DataBase();
***REMOVED*** catch(PGException $exception)***REMOVED***
	http_response_code(500);
	echo "Internal Database Error, please try again later: " . $exception->getMessage();
	return;
***REMOVED***

try***REMOVED***
	$result = $db->logIn($_POST['username'], $_POST['password']);
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo $pgException->getMessage();
	return;
***REMOVED*** catch(InvalidArgumentException $argumentException)***REMOVED***
	http_response_code(401);
	echo $argumentException->getMessage();
	return;
***REMOVED***

if (gettype($result) == 'boolean') ***REMOVED***
	http_response_code(401);
	echo "Username or password wrong is wrong.";
	return;
***REMOVED***

else***REMOVED***
	http_response_code(200);
	echo $result;
***REMOVED***

