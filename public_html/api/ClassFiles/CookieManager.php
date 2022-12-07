<?php

class CookieManager
{
	private string $cookieName, $key;
	private int $expire_time, $employee_expire_time;

	public function __construct()
	{
		$this->cookieName = "authCookie";
		$this->expire_time = 3600; //seconds
		$this->employee_expire_time = 43200;
		$this->key = " Somekey";
	}

	function createCookie($username,  $isEmployee=false): void
	{
		setcookie($this->cookieName, $this->createCookieValue($username, $isEmployee),
			time() + (($isEmployee) ? $this->employee_expire_time : $this->expire_time),
			"/",
			"cs425.lenwashingtoniii.com");
	}

	function isValidCookie(): bool{
		$cookie_data = $this->getCookieData();
		if(!$cookie_data){
			return false;
		}

		if($cookie_data["expires"] < time()){
			$this->deleteCookie();
			return false;
		}

		return true;
	}

	public function isEmployee(): bool{
		return $this->getCookieData()["isEmployee"];
	}

	private function getCookieData(): array|bool{
		if(!isset($_COOKIE[$this->cookieName])){ return false; }
		$data = json_decode($_COOKIE[$this->cookieName], true);

		if($data["expires"] < time()){
			$this->deleteCookie();
			return array();
		}

		$data["isEmployee"] = isset($data["usage"]);
		return $data;
	}

	function getCookieUsername(): string|false {
		$data = $this->getCookieData();
		if(!$data) { return false; }
		return $data["username"];
	}

	function getExpireTime(): int{
		return $this->expire_time;
	}

	private function createCookieValue($username, $isEmployee=false): string{
		$expires = time() + (($isEmployee) ? $this->employee_expire_time : $this->expire_time);
		$data = array("username" => $username,
			"expires" => $expires,
			"encryption" => hash("sha256", sprintf("%s-%s", $username, $this->key)));
		if($isEmployee){
			$data["usage"] = "86";
		}
		return json_encode((object) $data);
	}

	public function deleteCookie(): void
	{
		setcookie($this->cookieName, "", time() - $this->expire_time, "/", "cs425.lenwashingtoniii.com");
	}
}