<?php

require_once(dirname(__DIR__) . "/ConfigFiles/VerificationConfig.php");
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

	public function createVerificationCode($email, $name){
		$time = time();
		$this->query(sprintf("INSERT INTO AwaitingVerification VALUES('%s','%s',%s)", $email, $name, $time));
		return password_hash(sprintf("name=%s&time=%d&email=%s", $name, $time, $email), PASSWORD_DEFAULT);
	}

	public function check_verification($email, $code): bool{
		$result = $this->query(sprintf("SELECT name, time_of_creation FROM AwaitingVerification WHERE email = '%s'", $email));
		$name = pg_fetch_result($result, 0, 0);
		$time = pg_fetch_result($result, 0, 1);

		if($time > time()){
			return false;
		}

		if(!password_verify(sprintf("name=%s&time=%d&email=%s", $name, $time, $email), $code)){
			echo "Didn't work";
			return false;
		}

		if(!$this->query(sprintf("DELETE FROM awaitingverification WHERE email = '%s'", $email))){
			header("Response: Your verification code was correct, but something happened when unlocking your account");
			return false;
		}

		if(!$this->query(sprintf("UPDATE Customers SET authenticated_email = TRUE WHERE email = '%s'", $email))){
			header("Response: " . pg_last_error());
			return false;
		}

		return true;
	}

	public function send_verification_email($email, $name){
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