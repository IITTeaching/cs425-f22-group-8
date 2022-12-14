<?php

require_once(dirname(__DIR__) . "/ConfigFiles/VerificationConfig.php");
require_once(dirname(__DIR__) . "/Exceptions/PGException.php");
require_once(dirname(__DIR__) . "/constants.php");
require_once "CS425Class.php";

class Verifications extends CS425Class
{
	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		parent::__construct(new VerificationConfig());
	}

	/**
	 * @throws PGException
	 */
	public function createVerificationCode($email, $name): string {
		$time = time();
		$this->query(sprintf("INSERT INTO AwaitingVerification VALUES('%s','%s',%s)", $email, $name, $time));
		return password_hash(sprintf("name=%s&time=%d&email=%s", $name, $time, $email), PASSWORD_DEFAULT);
	}

	/**
	 * @throws PGException
	 */
	public function check_verification($email, $code): bool{
		$email = $this->prepareData($email);
		$code = $this->prepareData($code);
		$result = $this->query(sprintf("SELECT name, time_of_creation FROM AwaitingVerification WHERE email = '%s'", $email));
		$name = pg_fetch_result($result, 0, 0);
		$time = pg_fetch_result($result, 0, 1);

		if($time > time()){
			return false;
		}

		if(!password_verify(sprintf("name=%s&time=%d&email=%s", $name, $time, $email), $code)){
			respond("Didn't work");
			return false;
		}

		if(!$this->query(sprintf("DELETE FROM AwaitingVerification WHERE email = '%s'", $email))){
			respond("Your verification code was correct, but something happened when unlocking your account");
			return false;
		}

		if(!$this->query(sprintf("UPDATE Customers SET authenticated_email = TRUE WHERE email = '%s'", $email))){
			respond(pg_last_error());
			return false;
		}

		try{
			$username = $this->getBasicResult("SELECT username FROM Logins WHERE id = (SELECT id FROM Customers WHERE email = '%s')", $email);
			$this->send2FALink($email, $name, $username);
		} catch(PGException $e){
			;
		}
		return true;
		// TODO: If its true, the system should send another 2 links to set_two_factor that will display the QR code for the user. The first link will have the activate param which will activate it, the other will have deactivate which will make sure the system doesn't create 2FA for them.
	}

	public function send2FALink(string $email, string $name, string $username){
		$auth_link = HTTPS_HOST . "/activate_auth?username=" . $username;
		$enable = $auth_link . "&enable=true";
		$subject = "WCS Account Activation";
		$message = sprintf("
			<html lang='en'>
				<div style=\"background-color:#ffffff;width:600px;margin-left:auto;margin-right:auto\">
				<hr style=\"color:grey\">
				<table style=\"background-color:#ffffff;width:600px;text-align:center\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<td style=\"padding-top:24px;padding-left:16px;padding-bottom:10px\">
						<tbody><tr style=\"margin:24px;margin-left:0\">
						<td align=\"left\">
							<p style=\"word-wrap:break-word;font-size:15px;margin:0px;padding:0px\">Hello %s,</p>
							<p style=\"word-wrap:break-word;font-size:13px;margin-top:24px;padding:0px;line-height:19px\">Thank you for registering with our bank. Our users have the option of enabling a Time-based One Time Password, more commonly known as 2-Factor Authentication (2FA). If you would like to set up 2FA, please click the button titled <i>Enable</i>, if not, click <i>Disable.</i></p>
									<table align=\"left\" style=\"text-align:center;vertical-align:center;color:#fff;display:block\">
										<tbody><tr>
											<td style=\"border-radius:4px 4px 4px 4px\">
												<a href=\"%s\" rel=\"nofollow\" target=\"_blank\" style=\"color:#fff!important;padding-left:28px;padding-top:12px;padding-bottom:12px;padding-right:28px;height:40px;width:160px;background-color:#0696d7;font-size:16px;text-decoration:none;text-transform:uppercase;border-radius:4px 4px 4px 4px\">
													DISABLE
												</a>
											</td>
											<td style=\"border-radius:4px 4px 4px 4px\">
												<a href=\"%s\" rel=\"nofollow\" target=\"_blank\" style=\"color:#fff!important;padding-left:28px;padding-top:12px;padding-bottom:12px;padding-right:28px;height:40px;width:160px;background-color:#0696d7;font-size:16px;text-decoration:none;text-transform:uppercase;border-radius:4px 4px 4px 4px\">
													ENABLE
												</a>
											</td>
										</tr>
										</tbody></table>
								</td>
							</tr>
							</tbody></table>
							<p style=\"word-wrap:break-word;display:block;font-size:12px;margin-top:15px\">
								Disable: <a href=\"%s\" rel=\"nofollow\" target=\"_blank\" >%s</a>
							</p>
							<p style=\"word-wrap:break-word;display:block;font-size:12px;margin-top:15px\">
								Enable: <a href=\"%s\" rel=\"nofollow\" target=\"_blank\" >%s</a>
							</p>
					<hr style=\"color:grey\">
				</div>
			</html>
			", $name, $auth_link, $enable, $auth_link, $auth_link, $enable, $enable);

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <cs425@lenwashingtoniii.com>' . "\r\n";

		mail($email, $subject, $message, $headers);
	}

	/**
	 * @throws PGException
	 */
	public function send_verification_email(string $email, string $name){
		$verification_code = $this->createVerificationCode($email, $name);
		$verification_link = sprintf(HTTPS_HOST . "/api/verify?email=%s&code=%s", $email, $verification_code);
		$subject = "WCS Account Creation";
		$message = sprintf("
			<html lang='en'>
				<div style=\"background-color:#ffffff;width:600px;margin-left:auto;margin-right:auto\">
				<hr style=\"color:grey\">
				<table style=\"background-color:#ffffff;width:600px;text-align:center\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<td style=\"padding-top:24px;padding-left:16px;padding-bottom:10px\">
						<tbody><tr style=\"margin:24px;margin-left:0\">
						<td align=\"left\">
							<p style=\"word-wrap:break-word;font-size:15px;margin:0px;padding:0px\">Hello %s,</p>
							<p style=\"word-wrap:break-word;font-size:13px;margin-top:24px;padding:0px;line-height:19px\">We have recently received a request to open an account with us registered to this email (<b><a href=\"mailto:%s\" target=\"_blank\">%s</a></b>). If this was you, please click the button below, or go to the link under it if the button doesn't work. If you did not make this request, please forward this email to us at <b><a href=\"mailto:cs425@lenwashingtoniii.com\">cs425@lenwashingtoniii.com</a></b> and we will handle it.</p>
									<table align=\"left\" style=\"text-align:center;vertical-align:center;color:#fff;display:block\">
										<tbody><tr>
											<td style=\"border-radius:4px 4px 4px 4px\">
												<a href=\"%s\" rel=\"nofollow\" target=\"_blank\" style=\"color:#fff!important;padding-left:28px;padding-top:12px;padding-bottom:12px;padding-right:28px;height:40px;width:160px;background-color:#0696d7;font-size:16px;text-decoration:none;text-transform:uppercase;border-radius:4px 4px 4px 4px\">
													VERIFY MY EMAIL
												</a>
											</td>
										</tr>
										</tbody></table>
								</td>
							</tr>
							</tbody></table>
							<p style=\"word-wrap:break-word;display:block;font-size:12px;margin-top:15px\">
								<a href=\"%s\" rel=\"nofollow\" target=\"_blank\" >%s</a>
							</p>
					<hr style=\"color:grey\">
				</div>
			</html>
			", $name, $email, $email, $verification_link, $verification_link, $verification_link);

		// It is mandatory to set the content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <cs425@lenwashingtoniii.com>' . "\r\n";

		mail($email, $subject, $message, $headers);
	}
}