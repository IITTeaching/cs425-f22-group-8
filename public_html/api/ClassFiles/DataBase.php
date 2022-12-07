<?php

use PgSql\Result;

require_once (dirname(__DIR__) . "/ConfigFiles/DataBaseConfig.php");
require_once (dirname(__DIR__) . "/Exceptions/PGException.php");
require_once (dirname(__DIR__) . "/constants.php");
require_once (dirname(__DIR__) . "/tools.php");
require_once "CookieManager.php";
require_once "User.php";
require_once "Verifications.php";
require_once "CS425Class.php";
require_once "Authentication.php";
require_once "Address.php";

class DataBase extends CS425Class
{
	private CookieManager $cookieManager;
	private Authentication $authenticator;

	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		parent::__construct(new DataBaseConfig());
		$this->cookieManager = new CookieManager();
		$this->authenticator = new Authentication();
	}

	/**
	 * @throws PGException
	 */
	function usernameInUse($username): bool{
		$result = parent::query(sprintf("SELECT username_in_use('%s')", $this->prepareData($username)));
		return convert_to_bool(pg_fetch_result($result, 0, 0));
	}

	/**
	 * @throws PGException
	 */
	function emailInUse(string $email): bool{
		$result = parent::query(sprintf("SELECT email_in_use('%s')", $this->prepareData($email)));
		return convert_to_bool(pg_fetch_result($result, 0, 0));
	}

	/**
	 * @throws PGException
	 * @throws InvalidArgumentException
	 */
	function logIn($username, $password, $authcode=""): string|false
	{
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$authcode = $this->prepareData($authcode);
		$defaultErrorMessage = "Incorrect username/password.";
		# region Checks if there is a valid cookie
		if($this->cookieManager->isValidCookie()) {
			$isEmployee = $this->cookieManager->isEmployee();
			$result = parent::query(sprintf("SELECT * FROM %s WHERE username = '%s'",
				$isEmployee? "EmployeeLogins" : "Logins", $username));

			if (pg_affected_rows($result) != 0) {
				if($isEmployee){
					header("Location: " . HTTPS_HOST . "/employee_login");
				}
				return "Logged In Successfully";
			}
			$this->cookieManager->deleteCookie();
			//throw new PGException(pg_last_error());
		}
		# endregion
		# region If there is no cookie, checks the (Customer) login table to try and log the person in
		$result = parent::query(sprintf("SELECT * FROM Logins WHERE username = '%s'", $username), $defaultErrorMessage);

		$affected_rows = pg_affected_rows($result);
		if ($affected_rows == 0) { // If there weren't any affected rows, then the system checks to see if the user is an employee;
			$pg_error = pg_last_error();
			if(strlen($pg_error) == 0){
				if(!$this->employeeLogin($username, $password, $authcode)){
					$pg_error = $defaultErrorMessage;
				} else{
					return "Employee Logged In Successfully";
				}
			}
			throw new PGException($pg_error);
		}

		if ($affected_rows > 1){ // Look into blocking this IP
			throw new InvalidArgumentException("Don't even try to inject this.");
		}
		# endregion
		# region Checks first if the user has an authenticated email.
		$row = pg_fetch_assoc($result);

		$user_id = $row["id"];
		$result = parent::query(sprintf("SELECT authenticated_email FROM Customers WHERE id = %s", $user_id));

		if (pg_affected_rows($result) == 0) {
			// The login is for an employee
			respond("You do not have an account with us, please create one at " . HTTPS_HOST . "/signup.");
			return false;
		}

		if(!convert_to_bool(pg_fetch_result($result, 0, 0))){
			respond("You must verify your email before you log in.");
			return false;
		}
		# endregion
		# Checks the username and password
		$dbusername = $row['username'];
		$dbpassword = $row['password'];

		if (!($dbusername == $username && password_verify($password, $dbpassword))) {
			throw new InvalidArgumentException($defaultErrorMessage);
		}
		# endregion
		# region If the user has 2FA enabled, checks the codes
		$totp = $row["totp_secret"];
		if(!is_null($totp) && false){ // TODO: Remove false when customers need 2FA to login.
			$valid_code = $this->authenticator->checkTOTP($username, $authcode, false);
			if(!$valid_code){
				throw new InvalidArgumentException("Response: Invalid 2FA code");
			}
		}
		# endregion

		$this->cookieManager->createCookie($username);
		return "Logged In Successfully";
	}

	function employeeLogin($username, $password, $authcode): string|false{
		$username = $this->prepareData($username);
		$password = $this->prepareData($password);
		$authcode = (int)$this->prepareData($authcode);
		$result = parent::query(sprintf("SELECT username, password, totp_secret FROM EmployeeLogins WHERE username = '%s'", $username));
		$affected_rows = pg_affected_rows($result);
		if($affected_rows == 0){
			return false;
		}
		$row = pg_fetch_assoc($result, 0);
		if (!(($row["username"] == $username) && password_verify($password, $row["password"]))) {
			throw new InvalidArgumentException("Invalid username or password.");
		}
		if(!$this->authenticator->checkTOTP($username, $authcode, true) && false){  // TODO: Remove and false when employees need 2FA again.
			respond("Incorrect 2 Factor Authentication.");
			return false;
		}
		$this->cookieManager->createCookie($username, true);
		header("Location: " . HTTPS_HOST . "/employee_login");
		return "Employee Logged In";
	}

	/**
	 * @throws PGException
	 */
	function signUp($fullname, $email, $username, $password, $phone, $address_number, $direction, $streetname, $city, $state, $zipcode, $apt, $branch) : bool
	{
		#region Data preparation
		$fullname = $this->prepareData($fullname);
		$password = $this->prepareData($password);
		$email = $this->prepareData($email);
		$username = $this->prepareData($username);
		$password = password_hash($this->prepareData($password), CRYPT_SHA512);
		$phone = $this->prepareData($phone);
		$branch = $this->prepareData($branch);
		$address = Address::createAddress($address_number, $direction, $streetname, $city, $state, $zipcode, $apt);
		#endregion

		# region Getting the branch id
		$result = parent::query(sprintf("SELECT id FROM Branch WHERE name = '%s'", $branch));
		if(pg_num_rows($result) == 0){
			throw new InvalidArgumentException(sprintf("The branch with the name \"%s\" could not be found", $branch));
		}
		$branch_id = pg_fetch_result($result, 0, 0);

		# endregion

		$result = parent::query(sprintf("INSERT INTO Customers(name,email,phone,home_branch,address) VALUES ('%s','%s','%s',%s,%s) RETURNING id",
			$fullname, $email, $phone, $branch_id, $address->getAddressId()));
		$user_id = pg_fetch_result($result, 0, 0);

		parent::query(sprintf("INSERT INTO Logins VALUES (%s,'%s','%s')", $user_id, $username, $password));

		$verification = new Verifications();
		$verification->send_verification_email($email, $fullname);
		return true;
	}

	public function query($query, $errorMessage=""): bool|Result
	{
		if(!str_starts_with($query, "SELECT")){ return false; }
		return parent::query($query, $errorMessage);
	}
}
