<?php

require_once (dirname(__DIR__) . "/ClassFiles/CS425Class.php");
require_once (dirname(__DIR__) . "/ConfigFiles/LoanConfig.php");
require_once (dirname(__DIR__) . "/finance_tools.php");

class LoanRequest extends CS425Class
{
	private readonly int $loan_request_id;

	public function __construct(){
		parent::__construct(new LoanConfig());

		$argv = func_get_args();
		$argc = func_num_args();

		if($argc == 1) {
			call_user_func_array(array($this, "__init__"), $argv);
		} elseif ($argc == 7){
			call_user_func_array(array($this, "register"), $argv);
		} else{
			parent::__destruct(); //new InvalidArgumentException("The account constructor can only take 1 argument, the account number."));
		}
	}

	private function __init__(int $loan_req_id)
	{
		$this->loan_request_id = $loan_req_id;
	}

	private function register(User $user, $amount, Compound $compounding_per_year, $apr, $n, $loan_name){
		$amount = (float)$this->prepareData($amount);
		$compounding_per_year = $this->prepareData($compounding_per_year);  // String representing Monthly, quarterly, Annually, etc...
		$apr = (float)$this->prepareData($apr);
		$n = (int)$this->prepareData($n);
		$loan_name = $this->prepareData($loan_name);

		$payment = uniform_capital_recovery($apr / $compounding_per_year->value, $n, $amount);

		$result = $this->query(sprintf("INSERT INTO LoanRequests(customer_id, loan_name, amount, apr, payment, n, compounding_per_year) VALUES(%d,'%s',%f,%f,%f,%d,%d) RETURNING loan_request_id",
			$user->getUserId(), $loan_name, $amount, $apr, $payment, $n, $compounding_per_year->value));

		$this->loan_request_id = pg_fetch_result($result, 0, 0);
	}

	public function getNumber(): int { return $this->loan_request_id; }

	public function getPayment(): int { return $this->getBasicResult(sprintf("SELECT payment FROM loanrequests WHERE loan_request_id = %d", $this->loan_request_id)); }

	/**
	 * @throws PGException
	 */
	public static function requestLoan($amount, $compounding_per_year, $apr, $n, $loan_name): LoanRequest {
		foreach(Compound::cases() as $case){
			if(strtolower($compounding_per_year) == strtolower($case->name)){
				return new LoanRequest($amount, $case, $apr, $n, $loan_name);
			}
		}
		throw new InvalidArgumentException("We don't allow a compounding type named: \"" . $compounding_per_year ."\"");

	}

	/**
	 * @throws PGException
	 */
	public function getCustomer(): User{
		$result = $this->query(sprintf("SELECT customer_id FROM ApprovedLoans WHERE loan_number = %d", $this->loan_request_id));
		return new User(pg_fetch_result($result, 0));
	}
}