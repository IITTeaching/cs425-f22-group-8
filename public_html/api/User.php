<?php

require "DataBase.php";

class User
{
	private DataBase $db;
	private int $id;

	/**
	 * @throws PGException
	 */
	public function __construct($db){
		$this->db = $db;
		$this->id = $this->db->getCurrentUserId();
	}

	/**
	 * @throws PGException
	 */
	public function getName(): bool|string
	{
		return $this->db->getName();
	}

	public function getNumberOfAccounts(): bool|int{
		$result = $this->db->query(sprintf("SELECT COUNT(*) FROM Account WHERE holder = %s", $this->id));
		return pg_fetch_result($result, 0);
	}
}