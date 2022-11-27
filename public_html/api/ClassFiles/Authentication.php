<?php

#include_once "CS425Class.php";
#require_once(dirname(__DIR__) . "/ConfigFiles/VerificationConfig.php");

class Authentication #extends CS425Class
{
	protected string $charset;
	public function __construct()
	{
		#parent::__construct(new VerificationConfig());
		$this->charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
	}

	private function utf8_char_code_at($str, $index)
	{
		$char = mb_substr($str, $index, 1, 'UTF-8');
		if (mb_check_encoding($char, 'UTF-8')) {
			$ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
			return hexdec(bin2hex($ret));
		} else {
			return null;
		}
	}

	private function mapped($value){
		$position = $this->utf8_char_code_at($this->charset, floor((mb_ord($value, "UTF-8")*strlen($charset))/256));
		return mb_chr($position);
	}

	public function createSecretKey($length=16){
		$token = openssl_random_pseudo_bytes($length);
		$array = array_map("mapped", str_split($token));
		return join("", $array);
	}

	# region I will not lie, I took some of this from https://github.com/lfkeitel/php-totp/blob/master/src/Hotp.php and made it work.
	public function GenerateToken($key, $time = null, $length = 6, $time_interval=30, $algo="sha1")
	{
		// Pad the key if necessary
		if ($algo === 'sha256') {
			$key = $key . substr($key, 0, 12);
		} elseif ($algo === 'sha512') {
			$key = $key . $key . $key . substr($key, 0, 4);
		}

		// Get the current unix timestamp if one isn't given
		if (is_null($time)) {
			$time = time();
		}

		// Calculate the count
		$count = floor($time / $time_interval);
		$convert = $this->convertFromSecret($key);

		// Generate a normal HOTP token

		$hex = str_pad(dechex($count), 16, "0", STR_PAD_LEFT);
		$cmd = sprintf("echo -n \"0x%s\" | xxd -r -p | openssl dgst -%s -mac HMAC -macopt hexkey:%s",
			$hex, $algo, $convert);
		exec($cmd, $output, $retval);

		# $hash = hash_hmac($algo, $hex, $convert);
		#echo "Hash: " . $hash . PHP_EOL;

		$code = $this->genHTOPValue(substr($output["0"], 13), $length);

		$code = str_pad($code, $length, "0", STR_PAD_LEFT);
		$code = substr($code, (-1 * $length));

		return $code;
	}

	private function genHTOPValue($hash, $length)
	{
		// store calculate decimal
		$hmac_result = [];
		echo $hash . PHP_EOL;
		// Convert to decimal  # FIXME: The output will has the (stdin)= in front of it, remove this.
		foreach (str_split($hash, 2) as $hex) {
			$hmac_result[] = hexdec($hex);
		}

		$offset = $hmac_result[count($hmac_result)-1] & 0xf;

		$code = (int)($hmac_result[$offset] & 0x7f) << 24
			| ($hmac_result[$offset+1] & 0xff) << 16
			| ($hmac_result[$offset+2] & 0xff) << 8
			| ($hmac_result[$offset+3] & 0xff);

		return $code % pow(10, $length);
	}
	# endregion
	public function convertFromSecret($secret){
		$array = str_split($secret);
		$lambda = function($value){
			return sprintf("%05d", decbin(strpos($this->charset, $value)));
		};
		$binary_string = join("", array_map($lambda, $array));
		$new_string = "";
		for($i = 0; $i < strlen($binary_string); $i+=4){
			$new_string = $new_string . dechex(bindec(substr($binary_string,$i,4)));
		}
		return $new_string;
	}

}

$totp = new Authentication();
echo $totp->GenerateToken("ACAHAACAAJGILAOC") . PHP_EOL;
# echo $totp->GenerateToken("XE7ZREYZTLXYK444", 1632741679) . PHP_EOL;
