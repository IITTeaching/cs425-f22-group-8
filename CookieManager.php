***REMOVED***

class CookieManager
***REMOVED***
	private string $key;
	private string $cookieName;

	public function __construct($key)
	***REMOVED***
		$this->key = $key;
		$this->cookieName = "authCookie";
***REMOVED***

	function createCookie($username): void
	***REMOVED***
		$expire_time = 3600;  //seconds
		setcookie($this->cookieName, $this->createCookieValue($username), time() + $expire_time, "/", "cs425.lenwashingtoniii.com");
***REMOVED***

	function isValidCookie(): bool***REMOVED***
		if(!isset($_COOKIE[$this->cookieName]))***REMOVED***
			return false;
	***REMOVED***

		$cookieValue = $_COOKIE[$this->cookieName];
		$username = preg_split("]", $cookieValue)[0];
		return $this->createCookieValue($username) == $cookieValue;
***REMOVED***

	private function createCookieValue($username): string***REMOVED***
		return $username . "]" . hash("sha256", sprintf("%s-%s", $username, $this->key));
***REMOVED***
***REMOVED***