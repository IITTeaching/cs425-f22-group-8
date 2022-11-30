<?php

declare(strict_types=1);

include_once "CS425Class.php";
require_once(dirname(__DIR__) . "/ConfigFiles/VerificationConfig.php");
require_once(dirname(__DIR__) . "/constants.php");

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once (dirname(__DIR__, 2) . "/vendor/autoload.php");

class Authentication extends CS425Class
{
	protected string $charset;
	#private string $db_cipher;

	public function __construct()
	{
		parent::__construct(new VerificationConfig());
		$this->charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
		#$this->db_cipher = "";
	}

	# region Creating TOTP secret key
	public function checkTOTP(string $username, string $authcode, bool $isEmployee) : bool
	{
		$username = $this->prepareData($username);
		$table_name = $isEmployee ? "EmployeeLogins" : "Logins";
		$key = $this->getBasicResult(sprintf("SELECT totp_secret FROM %s WHERE username = '%s'", $table_name, $username));
		$totps = $this->GenerateCloseTokens($key);
		return in_array($authcode, $totps);
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
		$position = $this->utf8_char_code_at($this->charset, floor((mb_ord($value, "UTF-8")*strlen($this->charset))/256));
		return mb_chr($position);
	}

	public function createSecretKey($length=16){
		$token = openssl_random_pseudo_bytes($length);
		$array = array_map("mapped", str_split($token));
		return join("", $array);
	}

	public function generateQRCode($username, $key, $length=6, $period=30){
		$data = sprintf("otpauth://totp/WCS%%20Banking:%s?secret=%s&issuer=WCS%%20Banking&digits=%d&period=%d",
			$username, $key, $length, $period);
		$options = new QROptions(
			[
				'eccLevel' => QRCode::ECC_L,
				'outputType' => QRCode::OUTPUT_MARKUP_SVG,
				'version' => 5,
			]
		);
		# http://www1.auth.iij.jp/smartkey/en/uri_v1.html
		return (new QRCode($options))->render($data);  # TODO: Add logo (https://www.twilio.com/blog/create-qr-code-in-php)
	}
	# endregion

	# region Generate TOTP
	# region I will not lie, I took some of this from https://github.com/lfkeitel/php-totp/blob/master/src/Hotp.php and made it work.
	/**
	 * Generates a Time based One Time Password (TOTP).
	 *
	 * @param string $key The secret key.
	 * @param float|int|null $time The time the code should be generated.
	 * @param int $length The length of the code. Usually 6 or 8.
	 * @param int $time_interval The length in between code generation.
	 * @param string $algo The algorithm being used. Default is sha1.
	 * @return string
	 */
	public function GenerateToken($key, $time = null, $length = 6, $time_interval=30, $algo="sha1") : string
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
		elseif($time < 0){
			$time = time() + $time;
		}

		// Calculate the count
		$count = (int)floor($time / $time_interval);
		$convert = $this->convertFromSecret($key);

		// Generate a normal HOTP token

		$hex = str_pad(dechex($count), 16, "0", STR_PAD_LEFT);
		$cmd = sprintf("echo -n \"0x%s\" | xxd -r -p | openssl dgst -%s -mac HMAC -macopt hexkey:%s",
			$hex, $algo, $convert);
		exec($cmd, $output, $retval);

		# $hash = hash_hmac($algo, $hex, $convert);
		#echo "Hash: " . $hash . PHP_EOL;

		$code = $this->genHTOPValue(substr($output["0"], 13), $length);

		$code = str_pad((string)$code, $length, "0", STR_PAD_LEFT);
		return substr($code, (-1 * $length));
	}

	private function genHTOPValue($hash, $length)
	{
		// store calculate decimal
		$hmac_result = [];
		echo $hash . PHP_EOL;
		// Convert to decimal
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

	/**
	 * Generates several OTPs around a given time.
	 *
	 * @param string $key The secret key.
	 * @param float|int|null $time The time the code should be generated.
	 * @param int $length The length of the code. Usually 6 or 8.
	 * @param int $time_interval The length in between code generation.
	 * @param string $algo The algorithm being used. Default is sha1.
	 * @param int $before The number of codes before this time to be generated. (Should be positive, inclusive).
	 * @param int $after The number of codes after this time to be generated. (Should be positive, inclusive).
	 * @return array
	 */
	public function GenerateCloseTokens(string $key, float|int $time = null, int $length = 6, int $time_interval=30,
										string $algo="sha1", int $before=1, int $after=1): array
	{
		$otps = array();
		if(is_null($time)){
			$time = time();
		}
		for($i=-$before; $i<=$after; $i++){
			$otps[] = $this->GenerateToken($key, $time + ($time_interval * $i), $length, $time_interval, $algo);
		}
		return $otps;
	}
	# endregion

	# region Encryption/Decryption
	protected function encrypt($data, $cipher=null): string|false{
		if (!in_array($cipher, openssl_get_cipher_methods()))
		{
			return false;
		}
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivlen);
		return openssl_encrypt($data, $cipher, $key, $options=0, $iv, $tag);
	}

	protected function decrypt($data, $cipher=null): string|false{ # TODO: If there is time, encrypt all of the secret keys in the database.
		return false;#return openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);
	}
	# endregion
}

#$totp = new Authentication();
# echo $totp->GenerateToken("ACAHAACAAJGILAOC") . PHP_EOL;
#echo $totp->generateQRCode("employee_username1","ACAHAACAAJGILAOC") . PHP_EOL;
# echo $totp->GenerateToken("XE7ZREYZTLXYK444", 1632741679) . PHP_EOL;
