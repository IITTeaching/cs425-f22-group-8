<?php
require_once "api/ClassFiles/CookieManager.php";
require_once "api/ClassFiles/User.php";
require_once "api/ClassFiles/Views.php";
require_once "api/constants.php";

$cookie = new CookieManager();
$username = $cookie->getCookieUsername();

if(!$cookie->isValidCookie()){
	http_response_code(401);
	header("Location: " . HTTPS_HOST . "/");
	return;
}

if(!$username){
	respond("You are registered as logged in, but there is no user attached to this session.");
	http_response_code(500);
	$cookie->deleteCookie();
	return;
}

try{
	$user = User::fromUsername($username);
} catch(PGException | InvalidArgumentException $pgError){
	http_response_code(500);
	respond($pgError->getMessage());  # TODO: Add a note telling users how to access the transactions.
	return;
}

try {
	$account_types = (new Views())->getAccountTypes();
} catch (PGException $e) {
	respond($e->getMessage());
	$account_types = array();
}

$loans = $user->getLoans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Banking</title>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="/css/sidebar.css" type="text/css" rel="stylesheet"/>
	<link href="/css/employee_pages.css" type="text/css" rel="stylesheet"/>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<script type="text/javascript" src="/scripts/transactions.js"></script>
	<script type="text/javascript">
		function openSidebar () {
			let side = document.getElementById("page-side");
			side.classList.add("show");
		}

		function closeSidebar () {
			let side = document.getElementById("page-side");
			side.classList.remove("show");
		}
	</script>
</head>
<body class="sidebar" onload="getAccounts()">
<nav id="page-side">
	<div id="leftcontent">
		<div id="account-number" class="side-account-info">
			<div class="label">Account Number:</div><div id="number">The account's number</div>
		</div>
		<div id="account-name" class="side-account-info">
			<div class="label">Name: </div><div id="name">The account's name</div> <!-- Should be clickable to change -->
		</div>
		<div id="account-balance" class="side-account-info">
			<div class="label">Balance: </div><div id="balance">The account's balance</div> <!--The rest of these should just be displayed.-->
		</div>
		<div id="account-interest" class="side-account-info">
			<div class="label">Interest: </div><div id="interest">The account's interest</div>
		</div>
		<div id="account-monthly-fees" class="side-account-info">
			<div class="label">Monthly Fees: </div><div id="monthly_fees">The account's monthly fees</div>
		</div>
		<div id="account-overdrawn" class="side-account-info">
			<div class="label">Can be Overdrawn: </div><div id="overdrawn">If the account can be overdrawn.</div>
		</div>
	</div>
	<div id="rightcontent">
		<input type="text" id="transaction" name="transaction" pattern="Withdrawal|Deposit|Transfer" list="transactions" placeholder="Transaction Type" onchange="checkTransactionType()"><br>
		<datalist id="transactions">
			<option>Withdrawal</option>
			<option>Deposit</option>
			<option>Transfer</option>
		</datalist>
		$<input name="amount" id="amount" step="0.01" min="0" max="1000" placeholder="Amount" required><br>
		<input name="transfer_to_account_number" id="transfer_to_account_number" placeholder="Recipient Account Number" hidden><br id="transfer_break" hidden>
		<input name="description" id="description" type="text" placeholder="Transaction Description"><br>
		<input name="do_transaction" id="do_transaction" type="submit" value="Do the Transaction" onclick="transact()">
	</div>
	<div id="scheduling">
		<br><hr><br>
		<button id="pending_transactions" onclick="getPendingTransactions()">See Pending Transactions</button><br>
		<label for="statement_month">Input Month: </label><input type="month" id="statement_month" name="statement_month" placeholder="mm-yyyy" value="" min="2022-11-01" max="<?php echo date("Y-m-d")?>"><br>
		<button id="see_statement" onclick="getMonthlyStatement()">See Monthly Statement</button>
		<table id="schedule" class="profile_info">
			<tr>
				<th>Time</th>
				<th>Amount</th>
				<th>Account Balance</th>
				<th>Description</th>
			</tr>
		</table>
	</div>
</nav>
<div id="page-main">
	<h2>My Accounts</h2>
	<table id="accounts" class="profile_info">
		<tr>
			<th>Account Name</th>
			<th>Balance</th>
			<th>Type</th>
			<th>Interest</th>
			<th>Monthly Fee</th>
			<th>Can Be Overdrawn</th>
		</tr>
	</table>
	<div id="id01" class="modal">
		<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
		<form class="modal-content" method="post" action="/api/create_account">
			<div class="container">
				<h1>Add Account</h1>
				<p style="color: rgb(133, 133, 133);">Please fill in the following form with the new Account's information.</p>

				<hr>
				<label class="form_label" for="account_name">Account Name</label>
				<input type="text" placeholder="New Account Name" name="account_name" id="account_name" minlength="0" maxlength="30" required>

				<label class="form_label" for="account_type">Account Type</label>
				<input type="text" name="account_type" id="account_type" list="account_types" placeholder="Account Type" required>
				<datalist id="account_types">
					<?php foreach($account_types as $account_type) { ?>
						<option><?php echo $account_type ?></option>
					<?php } ?>
				</datalist>

				<label class="form_label" for="initial_balance">Initial Balance</label>
				<input type="number" name="initial_balance" id="initial_balance" placeholder="Initial Balance" min="0" step="0.01">

				<div class="clearfix">
					<button type="button" onclick="document.getElementById('id01').style.display='none'" class="employee_forms cancelbtn">Cancel</button>
					<button type="submit" class="employee_forms signupbtn">Create Account</button>
				</div>
			</div>
		</form>
	</div>

	<h2>My Loans</h2>
	<table id="loans" class="profile_info">
		<tr>
			<th>Loan Name</th>
			<th>Initial Amount</th>
			<th>Remaining Amount</th>
			<th>APR</th>
		</tr>
		<?php if(is_array($loans)){ foreach($loans as $loan) { ?>
			<tr>
				<td><?php echo $loan->getName(); ?></td>
				<td>$<?php echo sprintf("%.2f", $loan->getInitialAmount()); ?></td>
				<td>$<?php echo sprintf("%.2f", $loan->getAmountRemaining()); ?></td>
				<td><?php echo $loan->getAPR(); ?>%</td>
			</tr>
		<?php }} ?>
	</table>
	<nav class="floating-menu">
		<h3>Hello <?php try {
					echo $user->getFirstName();
				} catch (PGException $e) {
					echo "Internal Server Error";
				} ?></h3>
		<button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Create New Account</button>
		<a href="/api/logout">Logout</a>

	</nav>
</div>
</body>
</html>
