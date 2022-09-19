***REMOVED***

class PGException extends Exception
***REMOVED***
	public function __construct($exmsg="", $val = 0, Exception $old = null) ***REMOVED***
		if($exmsg == "")***REMOVED***
			$exmsg = pg_last_error();
	***REMOVED***
		parent::__construct($exmsg, $val, $old);
***REMOVED***

	public function __toString() ***REMOVED***
		return __CLASS__ . ": [***REMOVED***$this->code***REMOVED***]: ***REMOVED***$this->message***REMOVED***\n";
***REMOVED***
***REMOVED***