<?php

class CookieManager
{
	private string $key;
	private string $cookieName;

	public function __construct($key)
	{
		$this->key = $key;
		$this->cookieName = "authCookie";
	}

	function createCookie($username): void
	{
		$expire_time = 3600;  //seconds
		setcookie($this->cookieName, $this->createCookieValue($username), time() + $expire_time, "/", "cs425.lenwashingtoniii.com");
	}

	function isValidCookie(): bool{
		if(!isset($_COOKIE[$this->cookieName])){
			return false;
		}

		$cookieValue = $_COOKIE[$this->cookieName];
		$username = explode("]", $cookieValue)[0];
		return $this->createCookieValue($username) == $cookieValue;
	}

	private function createCookieValue($username): string{
		return $username . "]" . hash("sha256", sprintf("%s-%s", $username, $this->key));
	}
}