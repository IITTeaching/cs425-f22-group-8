<?php

class CookieManager
{
	private string $key;
	private string $cookieName;
	private int $expire_time;

	public function __construct($key)
	{
		$this->key = $key;
		$this->cookieName = "authCookie";
		$this->expire_time = 3600;
	}

	function createCookie($username): void
	{
		$expire_time = 3600;  //seconds
		setcookie($this->cookieName, $this->createCookieValue($username), time() + $expire_time, "/", "cs425.lenwashingtoniii.com");
	}

	function isValidCookie(): bool{
		$cookie_data = $this->getCookieData();
		if(!$cookie_data){
			return false;
		}

		if($cookie_data->expires < time()){
			$this->deleteCookie();
			return false;
		}

		return true;
	}

	private function getCookieData(): array|bool{
		if(!isset($_COOKIE[$this->cookieName])){
			return false;
		}
		return json_decode($_COOKIE[$this->cookieName]);
	}

	function getCookieUsername(): string|bool{
		return $this->getCookieData()->username;
	}

	function getExpireTime(): int{
		return $this->expire_time;
	}

	private function createCookieValue($username): string{
		$expires = time() + $this->expire_time;
		return json_encode((object) array("username" => $username, "expires"=>$expires, "encryption"=>hash("sha256", sprintf("%s-%s", $username, $this->key))));
	}

	public function deleteCookie(): void
	{
		setcookie($this->cookieName, "", time() - $this->expire_time, "/", "cs425.lenwashingtoniii.com");
	}
}