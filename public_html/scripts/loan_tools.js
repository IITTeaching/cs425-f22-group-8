/**
 * Finds the future value of some given present value. [F/P] Assumes there are no payments (A).
 *
 * @param {float} interest The rate per period, in percentage form (I).
 * @param {int} periods The number of periods (N).
 * @param {float|int} present_sum The present value. This is not required if you are trying to find the rate. (P)
 * @return float The future value (F).
 */
function single_compound_payment(interest, periods, present_sum = 1)
{
	interest /= 100;
	let exponent = (1 + interest) ** periods;
	return -present_sum * exponent;
}
/**
 * Finds the present value given a known future value. [P/F] Assumes there are no payments (A).
 *
 * @param {float} interest The rate per period, in percentage form (I).
 * @param {int} periods The number of periods (N).
 * @param {float|int} future_value The future value (f).
 * @return float The present value (P).
 */
function present_value(interest, periods, future_value = 1)
{
	interest /= 100;
	let exponent = 1 / (1 + interest) ** periods;
	return -future_value * exponent;
}
/**
 * Finds the future value given a uniform set of payments. [F/A] Assumes there is no present value (P).
 *
 * @param {float} interest The rate per period, in percentage form (I).
 * @param {int} periods The number of periods (N).
 * @param {float|int} payments The number of payments (A).
 * @return float The future value (F).
 */
function uniform_compound(interest, periods, payments = 1)
{
	interest /= 100;
	let numerator = (1 + interest) ** periods - 1;
	return -payments * numerator / interest;
}
/**
 * Finds the amount per payment to get the given future value. [A/F] Assumes there is no present value (P).
 *
 * @param {float} interest The rate per period, in percentage form (I).
 * @param {int} periods The number of periods (N).
 * @param {float|int} future_value The future value (F).
 * @return float The amount per payment (A).
 */
function uniform_sinking_fund(interest, periods, future_value = 1)
{
	interest /= 100;
	let denominator = (1 + interest) ** periods - 1;
	return -future_value * interest / denominator;
}
/**
 * Finds the amount of payments to that can be taken out given a predetermined present value [A/P] Assumes there is no future value (F).
 *
 * @param {number} interest The rate per period, in percentage form (I).
 * @param {int} periods The number of periods (N).
 * @param {int|float} present_value The present value (P).
 * @return float The amount per payment (A).
 */
function uniform_capital_recovery(interest, periods, present_value = 1)
{
	interest /= 100;
	let exponent = (1 + interest) ** periods;
	let numerator = interest * exponent;
	let denominator = exponent - 1;
	return -present_value * numerator / denominator;
}
/**
 *  [P/A].
 *
 * @param {float} interest The rate per period, in percentage form (I).
 * @param {int} periods The number of periods (N).
 * @param {int|float} payments The amount per payments (A).
 * @return float The present value (P).
 */
function uniform_present_worth(interest, periods, payments = 1) {
	if (interest === 0) {
		return payments * periods;
	}
	interest /= 100;
	let exponent = (1 + interest) ** periods;
	let numerator = exponent - 1;
	let denominator = interest * exponent;
	return -payments * numerator / denominator;
}

function requestLoanListener(){
	alert(this.responseText);
}

function requestLoan(){
	const form = document.forms["request_loan_form"];
	let name = form.elements.loan_name.value;
	let pv = form.elements.initial_amount.value;
	let apr = parseFloat(form.elements.apr.value);
	let n = form.elements._n.value;
	let cpy = form.elements.compounding_per_year.value;
	let num = 0; // Numeric representation of cpy

	switch(cpy){
		case "Annually":
			num = 1;
			break;
		case "SemiAnnually":
			num = 2;
			break;
		case "Quarterly":
			num = 4;
			break;
		case "Monthly":
			num = 12;
			break;
		default:
			console.error("Error, cpy wasn't matched.");
	}

	let pmt = -uniform_capital_recovery(apr/num, n, pv);

	if(!confirm(`You understand that the payment per period for this loan will be ${pmt.toLocaleString('en-US',{ style: 'currency', currency: 'USD' })}, correct?`)){
		return;
	}

	const req = new XMLHttpRequest();
	req.addEventListener("load", requestLoanListener);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/request_loan");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`amount=${pv}&compounding_per_year=${cpy}&apr=${apr}&n=${n}&loan_name=${name}`);
}

const function_map = {
	"F/P": "single_compound_payment",
	"P/F": "present_value",
	"F/A": "uniform_compound",
	"A/F": "uniform_sinking_fund",
	"A/P": "uniform_capital_recovery",
	"P/A": "uniform_present_worth"
};

//console.log(function_map["A/P"](4, 360, 360000));
//echo $_function_map["A/P"](8,12,50) . PHP_EOL;