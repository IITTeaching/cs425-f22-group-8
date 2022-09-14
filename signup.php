***REMOVED***
require "DataBase.php";
$db = new DataBase();

if (!(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['address']) && isset($_POST['username']) && isset($_POST['password']))) ***REMOVED***
	http_response_code(400);
	echo "All fields are required";
	return;
***REMOVED***

if (!$db->dbConnect()) ***REMOVED***
	http_response_code(500);
	echo "Error: Database connection";
	return;
***REMOVED***

if($db->usernameInUse($_POST['username']))***REMOVED***
	http_response_code(226);
	echo "Username is already taken. Try a different one.";
***REMOVED*** else if($db->emailInUse($_POST["email"]))***REMOVED***
	http_response_code(226);
	echo "Email-address is already in use, please use a different one.";
***REMOVED*** else if ($db->signUp($_POST['fullname'], $_POST['email'], $_POST['username'], $_POST['username'], $_POST['password'])) ***REMOVED***
	http_response_code(201);
	echo "Sign Up Success";
***REMOVED*** else ***REMOVED***
	http_response_code(500);
	echo "Sign up Failed";
***REMOVED***
