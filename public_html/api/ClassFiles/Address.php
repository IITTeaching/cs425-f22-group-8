<?php

require_once (dirname(__DIR__) . "/ConfigFiles/AddressConfig.php");

class Address extends CS425Class
{
	private readonly int $addressId;

	public function __construct($addressId){
		parent::__construct(new AddressConfig());
		$this->addressId = $addressId;
	}

	/**
	 * @return int
	 */
	public function getAddressId(): int { return $this->addressId; }
}