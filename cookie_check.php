***REMOVED***
require "DataBase.php";

try***REMOVED***
	echo
	$db = new DataBase();
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo "Error: Database connection";
	return;
***REMOVED***

if($db->checkCookie())***REMOVED***
	$db->_print("You have a proper cookie.");
***REMOVED*** else***REMOVED***
	$db->_print("Your cookie is not proper.");
***REMOVED***
