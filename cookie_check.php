***REMOVED***
require "DataBase.php";

try***REMOVED***
	$db = new DataBase();
***REMOVED*** catch(PGException $pgException)***REMOVED***
	http_response_code(500);
	echo "Error: Database connection";
	return;
***REMOVED***

$db->_print($db->checkCookie());