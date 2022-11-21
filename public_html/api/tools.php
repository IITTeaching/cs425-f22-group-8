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
function convert_to_bool($pg_result): bool{
	if($pg_result == "t"){
		return true;
	} else if ($pg_result == "f"){
		return false;
	}
	throw new PGException(sprintf("The given result does not map to a boolean: %s", $pg_result));
}