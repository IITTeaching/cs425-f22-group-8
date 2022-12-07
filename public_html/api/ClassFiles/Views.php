<?php

require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/DataBaseConfig.php");

class Views extends CS425Class
{
	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		parent::__construct(new DataBaseConfig());
	}

	/**
	 * @throws PGException
	 */
	private function getViewResults($query): array{
		$result = $this->query($query);
		$types = array();

		while($row = pg_fetch_row($result)) {
			$types[] = $row[0];
		}

		return $types;
	}

	/**
	 * @throws PGException
	 */
	public function getAccountTypes(): array { return $this->getViewResults("SELECT * FROM get_account_types"); }

	public function getBranchInfo(): array {
		$result = $this->query("SELECT * FROM branch_info");
		$branches = array();

		while($row = pg_fetch_array($result)) {
			$branches[] = array("name" => $row["name"], "address" => $row["address"]);
		}

		return $branches;
	}

	public function getStateOptions(): array {
		$result = $this->query("SELECT * FROM state_options");
		$states = array();

		while($row = pg_fetch_row($result)) {
			$states[] = $row[0];
		}

		return $states;
	}
}