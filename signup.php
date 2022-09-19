***REMOVED***
require "DataBase.php";

if (!(isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['address']) && isset($_POST['username']) && isset($_POST['password']))) ***REMOVED***
	http_response_code(400);
	echo "All fields are required";
	return;
***REMOVED***

try***REMOVED***
	$db = new DataBase();
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo "Error: Database connection";
	return;
***REMOVED***

try***REMOVED***
	if($db->usernameInUse($_POST['username']))***REMOVED***
		http_response_code(226);
		echo "Username is already taken. Try a different one.";
		return;
***REMOVED***
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo $pgException;
	return;
***REMOVED***

try ***REMOVED***
	if($db->emailInUse($_POST["email"]))***REMOVED***
		http_response_code(226);
		echo "Email-address is already in use, please use a different one.";
		return;
***REMOVED***
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo $pgException;
	return;
***REMOVED***

try ***REMOVED***
	if ($db->signUp($_POST['fullname'], $_POST['email'], $_POST['address'], $_POST['username'], $_POST['password'])) ***REMOVED***
		http_response_code(201);
		echo "Sign Up Success";
***REMOVED*** else ***REMOVED***
		http_response_code(500);
		echo "Sign up Failed";
***REMOVED***
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo $pgException;
***REMOVED***
