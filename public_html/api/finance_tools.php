<?php

/**
 * Finds the future value of some given present value. [F/P] Assumes there are no payments (A).
 *
 * @param $interest float The rate per period, in percentage form (I).
 * @param $periods int The number of periods (N).
 * @param $present_sum float|int The present value. This is not required if you are trying to find the rate. (P)
 * @return float The future value (F).
 */
function single_compound_payment(float $interest, int $periods, float|int $present_sum=1): float{
	$interest /= 100;
	$exponent = ((1+$interest) ** $periods);
	return -$present_sum * $exponent;
}

/**
 * Finds the present value given a known future value. [P/F] Assumes there are no payments (A).
 *
 * @param $interest float The rate per period, in percentage form (I).
 * @param $periods int The number of periods (N).
 * @param $future_value float|int The future value (f).
 * @return float The present value (P).
 */
function present_value(float $interest, int $periods, float|int $future_value=1): float{
	$interest /= 100;
	$exponent = 1 / ((1+$interest) ** $periods);
	return -$future_value * $exponent;
}

/**
 * Finds the future value given a uniform set of payments. [F/A] Assumes there is no present value (P).
 *
 * @param $interest float The rate per period, in percentage form (I).
 * @param $periods int The number of periods (N).
 * @param $payments float|int The number of payments (A).
 * @return float The future value (F).
 */
function uniform_compound(float $interest, int $periods, float|int $payments=1): float{
	$interest /= 100;
	$numerator = ((1+$interest)**$periods)-1;
	return -$payments * $numerator / $interest;
}

/**
 * Finds the amount per payment to get the given future value. [A/F] Assumes there is no present value (P).
 *
 * @param $interest float The rate per period, in percentage form (I).
 * @param $periods int The number of periods (N).
 * @param $future_value float|int The future value (F).
 * @return float The amount per payment (A).
 */
function uniform_sinking_fund(float $interest, int $periods, float|int $future_value=1): float{
	$interest /= 100;
	$denominator = ((1+$interest)**$periods) - 1;
	return -$future_value * $interest / $denominator;
}

/**
 * Finds the amount of payments to that can be taken out given a predetermined present value [A/P] Assumes there is no future value (F).
 *
 * @param $interest float The rate per period, in percentage form (I).
 * @param $periods int The number of periods (N).
 * @param $present_value int|float The present value (P).
 * @return float The amount per payment (A).
 */
function uniform_capital_recovery(float $interest, int $periods, int|float $present_value=1): float{
	$interest /= 100;

	$exponent = (1 + $interest) ** $periods;
	$numerator = $interest * $exponent;
	$denominator = $exponent - 1;

	return -$present_value * $numerator / $denominator;
}

/**
 *  [P/A].
 *
 * @param $interest float The rate per period, in percentage form (I).
 * @param $periods int The number of periods (N).
 * @param int|float $payments The amount per payments (A).
 * @return float The present value (P).
 */
function uniform_present_worth(float $interest, int $periods, int|float $payments=1): float{
	if($interest == 0){
		return $payments * $periods;
	}

	$interest /= 100;
	$exponent = (1+$interest) ** $periods;
	$numerator = $exponent - 1;
	$denominator = $interest * $exponent;
	return -$payments * $numerator / $denominator;
}

$_function_map = array(
	"F/P" => "single_compound_payment",
	"P/F" => "present_value",

	"F/A" => "uniform_compound",
	"A/F" => "uniform_sinking_fund",

	"A/P" => "uniform_capital_recovery",
	"P/A" => "uniform_present_worth"
);

//echo $_function_map["A/P"](8,12,50) . PHP_EOL;