<?php

namespace cs425\api\Exceptions;

class PGException extends Exception
{
	public function __construct($message="", $val = 0, Exception $old = null) {
		if($message == ""){
			$message = pg_last_error();
		}
		parent::__construct($message, $val, $old);
	}

	public function __toString(): string {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}