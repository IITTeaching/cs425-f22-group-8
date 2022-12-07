<?php

function isValidEmail($email):bool{
	return true;
}

function _print($statement): void
{
	echo $statement . PHP_EOL;
}

/**
 * @throws PGException
 */
function convert_to_bool(string $pg_result): bool{
	if($pg_result == "t"){
		return true;
	} else if ($pg_result == "f"){
		return false;
	}
	throw new PGException(sprintf("The given result does not map to a boolean: %s", $pg_result));
}

function is_header_set(string $header): bool{
	foreach(headers_list() as $hdr){
		if(str_contains($hdr, $header)){
			return true;
		}
	}
	return false;
}

function respond(string $message): void {
	echo $message . PHP_EOL;
	header("Response: " . $message);
}