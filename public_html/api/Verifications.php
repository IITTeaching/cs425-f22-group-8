<?php
require "VerificationConfig.php";

class Verifications
{
	protected PgSql\Connection $connect;
	private string $servername;
	private string $username;
	private string $password;
	private string $dbname;
	private string $port;

	/**
	 * @throws PGException
	 */
	public function __construct()
	{
		$dbc = new VerificationConfig();
		$this->servername = $dbc->servername;
		$this->username = $dbc->username;
		$this->password = $dbc->password;
		$this->dbname = $dbc->databasename;
		$this->port = $dbc->port;
		$this->dbConnect();
	}

	/**
	 * @throws PGException
	 */
	function dbConnect(): void
	{
		$connection_string = sprintf("host = %s port = %s dbname = %s user = %s password = %s", $this->servername, $this->port, $this->dbname, $this->username, $this->password);
		$this->connect = pg_pconnect($connection_string);
		if(!$this->connect){
			throw new PGException(pg_last_error());
		}
	}

	public function createVerificationCode($email, $name){
		$time = time();
		$sql = sprintf("INSERT INTO AwaitingVerification VALUES('%s','%s',%s)", $email, $name, $time);
		$result = pg_query($this->connect, $sql);
		return password_hash(sprintf("name=%s&time=%d&email=%s", $name, $time, $email), CRYPT_SHA512);
	}

	public function check_verification($email, $code): bool{
		$sql = sprintf("SELECT name, time_of_creation FROM AwaitingVerification WHERE email = '%s'", $email);
		$result = pg_query($this->connect, $sql);
		$name = pg_fetch_result($result, 0, 0);
		$time = pg_fetch_result($result, 0, 1);

		if($time < time()){
			return false;
		}

		$newly_generated = password_hash(sprintf("name=%s&time=%d&email=%s", $name, $time, $email), CRYPT_SHA512);

		if($newly_generated != $code){
			return false;
		}

		$sql = sprintf("DELETE FROM awaitingverification WHERE email = '%s'", $email);  // TODO: Make a verifybot role that has the necessary roles in PG
		if(!pg_query($this->connect, $sql)){
			header("Error: Your verification code was correct, but something happened when unlocking your account");
			return false;
		}

		$sql = sprintf("UPDATE Customers SET authenticated_email = TRUE WHERE email = '%s'", $email);
		if(!pg_query($this->connect, $sql)){
			header("Error: Your verification code was correct, but something happened when unlocking your account");
			return false;
		}

		return true;
	}

	public function send_verification_email($email, $name){
		$verification_code = $this->createVerificationCode($email, $name);
		$verification_link = "https://cs425.lenwashingtoniii.com/api/verify?code=";
		$subject = "WCS Account Creation";
		$message = sprintf("
			<html lang='en'>
				<div style=\"background-color:#ffffff;width:600px;margin-left:auto;margin-right:auto\">
				<hr style=\"color:grey\">
				<table style=\"background-color:#ffffff;width:600px;text-align:center\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<td style=\"padding-top:24px;padding-left:16px;padding-bottom:10px\">
						<tbody><tr style=\"margin:24px;margin-left:0px\">
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
		$headers .= 'From: <info@example.com>' . "\r\n";
		$headers .= 'Cc: sales@example.com' . "\r\n";

		mail($email, $subject, $message, $headers);
	}
}