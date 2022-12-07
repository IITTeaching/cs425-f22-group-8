<?php

require_once (dirname(__DIR__) . "/ConfigFiles/AddressConfig.php");

class Address extends CS425Class
{
	private readonly int $addressId;

	public function __construct(){
		parent::__construct(new AddressConfig());

		$argv = func_get_args();
		$argc = func_num_args();

		if($argc == 1) {
			call_user_func_array(array($this, "__init__"), $argv);
		} elseif ($argc == 4){
			call_user_func_array(array($this, "register"), $argv);
		} else{
			parent::__destruct(); //new InvalidArgumentException("The account constructor can only take 1 argument, the account number."));
		}
	}

	private function __init__($add_number){
		$this->addressId = $add_number;
	}

	/**
	 * @return int
	 */
	public function getAddressId(): int { return $this->addressId; }

	/**
	 * @throws PGException
	 */
	public static function createAddress($address_number, $direction, $streetName, $city, $state, $zipcode, $apt): Address
	{
		if(strlen($address_number) == 0) { throw new InvalidArgumentException("The address number cannot be empty."); }
		if(strlen($streetName) == 0) { throw new InvalidArgumentException("The street name cannot be empty."); }
		if(strlen($city) == 0) { throw new InvalidArgumentException("The city cannot be empty."); }
		if(strlen($state) == 0) { throw new InvalidArgumentException("The state cannot be empty."); }
		if(strlen($zipcode) == 0) { throw new InvalidArgumentException("The zipcode cannot be empty."); }

		return new Address($address_number, $direction, $streetName, $city, $state, $zipcode, $apt);
	}

	/**
	 * @throws PGException
	 */
	private function register($address_number, $direction, $streetName, $city, $state, $zipcode, $apt): int
	{
		$address_number = $this->prepareData($address_number);
		$direction = $this->prepareData($direction);
		$streetName = $this->prepareData($streetName);
		$city = $this->prepareData($city);
		$state = $this->prepareData($state);
		$zipcode = $this->prepareData($zipcode);
		$apt = $this->prepareData($apt);

		$sql = sprintf("SELECT id FROM Addresses WHERE number = %s AND UPPER(direction::TEXT) = '%s' AND UPPER(street_name) = '%s' AND UPPER(city) = '%s' AND UPPER(state) = '%s' AND zipcode = '%s' AND UPPER(unitnumber) = '%s'",
			$address_number, strtoupper($direction), strtoupper($streetName), strtoupper($city), strtoupper($state), $zipcode, strtoupper($apt));

		$result = parent::query($sql);
		$address_count = pg_num_rows($result);
		if($address_count == 1) { return pg_fetch_result($result, 0, 0); }

		if(strlen($apt) == 0){
			$sql = sprintf("INSERT INTO Addresses(number, direction, street_name, city, state, zipcode) VALUES(%s,'%s','%s','%s','%s',%s) RETURNING id",
				$address_number, $direction, $streetName, $city, $state, $zipcode);
		} else {
			$sql = sprintf("INSERT INTO Addresses(number, direction, street_name, city, state, zipcode, unitnumber) VALUES(%s,'%s','%s','%s','%s',%s,'%s') RETURNING id",
				$address_number, $direction, $streetName, $city, $state, $zipcode, $apt);
		}
		$result = parent::query($sql);
		if(pg_num_rows($result) == 0){
			throw new InvalidArgumentException("Something happened creating the address tuple");
		}

		return pg_fetch_result($result, 0, 0);
	}
}