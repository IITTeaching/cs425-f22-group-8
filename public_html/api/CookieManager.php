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
		$username = $this->getCookieUsername();
		if(!$username){
			return false;
		}
		return $this->createCookieValue($username) == $_COOKIE[$this->cookieName]; // TODO: Check expiration time, as well
	}

	function getCookieUsername(): string|bool{
		if(!isset($_COOKIE[$this->cookieName])){
			return false;
		}

		$cookieValue = $_COOKIE[$this->cookieName];
		return explode("]", $cookieValue)[0];
	}

	private function createCookieValue($username): string{
		return $username . "]" . hash("sha256", sprintf("%s-%s", $username, $this->key));
	}
}