***REMOVED***
require "DataBase.php";
$db = new DataBase();

if (!(isset($_POST['username']) && isset($_POST['password']))) ***REMOVED***
	http_response_code(400);
	echo "All fields are required";
	return;
***REMOVED***

if (!$db->dbConnect()) ***REMOVED***
	http_response_code(500);
	echo "Error: Database connection";
	return;
***REMOVED***

$result = $db->logIn($_POST['username'], $_POST['password']);
if (gettype($result) == 'boolean') ***REMOVED***
	http_response_code(400);
	echo "Username or password wrong is wrong";
	return;
***REMOVED***

else***REMOVED***
	http_response_code(200);
	echo $result;
***REMOVED***

