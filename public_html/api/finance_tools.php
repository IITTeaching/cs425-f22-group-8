<?php

function single_compound_payment($interest, $periods, $present_sum=1): float{
	$interest /= 100;
	$exponent = ((1+$interest) ** $periods);
	return -$present_sum * $exponent;
}

function present_value($interest, $periods, $future_value=1): float{
	$interest /= 100;
	$exponent = 1 / ((1+$interest) ** $periods);
	return -$future_value * $exponent;
}

function uniform_compound($interest, $periods, $payments=1): float{
	$interest /= 100;
	$numerator = ((1+$interest)**$periods)-1;
	return $payments * $numerator / $interest;
}

function uniform_sinking_fund($interest, $periods, $future_value=1): float{
	$interest /= 100;
	$denominator = ((1+$interest)**$periods) - 1;
	return $future_value * $interest / $denominator;
}

function uniform_capital_recovery($interest, $periods, $present_value): float{
	$interest /= 100;

	$exponent = (1 + $interest) ** $periods;
	$numerator = $interest * $exponent;
	$denominator = $exponent - 1;

	return $present_value * $numerator / $denominator;
}

function uniform_present_worth($interest, $periods, $payments): float{
	if($interest == 0){
		return $payments * $periods;
	}

	$interest /= 100;
	$exponent = (1+$interest) ** $periods;
	$numerator = $exponent - 1;
	$denominator = $interest * $exponent;
	return $payments * $numerator / $denominator;
}

$_function_map = array(
	"F/P" => "single_compound_payment",
	"P/F" => "present_value",

	"F/A" => "uniform_compound",  # FIXME: This code doesn't work for PHP or Python
	"A/F" => "uniform_sinking_fund",

	"A/P" => "uniform_capital_recovery",
	"P/A" => "uniform_present_worth"
);

echo $_function_map["F/P"](8,12,50) . PHP_EOL;