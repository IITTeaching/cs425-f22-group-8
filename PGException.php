***REMOVED***

class PGException extends Exception
***REMOVED***
	public function __construct($message="", $val = 0, Exception $old = null) ***REMOVED***
		if($message == "")***REMOVED***
			$message = pg_last_error();
	***REMOVED***
		parent::__construct($message, $val, $old);
***REMOVED***

	public function __toString(): string ***REMOVED***
		return __CLASS__ . ": [***REMOVED***$this->code***REMOVED***]: ***REMOVED***$this->message***REMOVED***\n";
***REMOVED***
***REMOVED***