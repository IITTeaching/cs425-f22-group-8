<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/constants.php";

try{
	$db = new DataBase();
	$user = $db->getCurrentUserId();
} catch(PGException $PGException){
	http_response_code(500);
	header("Response: " . $PGException->getMessage());
	return;
}

if(!$db->isLoggedIn()){
	header("Location: " . HTTPS_HOST . "/");
	return;
}

$accounts = $user->getAccounts();
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
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<script type="text/javascript">
		function openSidebar () {
			let side = document.getElementById("page-side"),
			btn = document.getElementById("side-button");

			side.classList.add("show");
			btn.innerHTML = "X";
		}

		function closeSidebar () {
			let side = document.getElementById("page-side"),
			btn = document.getElementById("side-button");

			side.classList.remove("show");
			btn.innerHTML = "&#9776;";
		}

		function accountRowOnClick(event){
			let account_number = event["path"][1]["id"];
			account_number = /account(\d+)/.exec(account_number);
			if(account_number === document.getElementById("number").value){
				showAccount(account_number);
			} else{
				closeSidebar();
			}
		}

		function showAccount(account_number){
			let params = `account_number=${account_number}`;

			const req = new XMLHttpRequest();
			req.addEventListener("load", reqListener);
			req.open("POST", "https://cs425.lenwashingtoniii.com/api/get_account_info");
			req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			req.send(params);
			openSidebar();
		}

		function reqListener() {
			let json = JSON.parse(this.responseText);
			console.log(json);
			let dct = {
				"Balance": "balance",
				"Interest": "interest",
				"Monthly Fee": "monthly_fees",
				"Name": "name",
				"Overdrawn": "overdrawn"
				//"Type": "",
			};
			let keys = Object.keys(dct);
			for(let i = 0; i < keys.length; i++){
				let key = keys[i];
				document.getElementById(dct[key]).value = json[key];
			}
		}

		function checkTransactionType(){
			let type = document.getElementById("transaction");
			let transfer_account = document.getElementById("transfer_to_account_number");
			if(type.value === "Transfer"){
				transfer_account.hidden = false;
				transfer_account.required = true;
			} else{
				transfer_account.hidden = true;
				transfer_account.required = false;
			}
		}
	</script>
</head>
<body class="sidebar">
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
			<div class="label">Overdrawn: </div><div id="overdrawn">If the account can be overdrawn.</div>
		</div>
	</div>
	<div id="rightcontent">
		<input type="text" id="transaction" name="transaction" pattern="Withdrawal|Deposit|Transfer" list="transactions" placeholder="Transaction Type" onchange="checkTransactionType()"><br>
		<datalist id="transactions">
			<option>Withdrawal</option>
			<option>Deposit</option>
			<option>Transfer</option>
		</datalist>
		$<input name="amount" id="amount" step="0.01" min="0" max="500" placeholder="Amount" required><br>
		<input name="transfer_to_account_number" id="transfer_to_account_number" placeholder="Recipient Account Number" hidden>
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
		<?php if(is_array($accounts)) {foreach($accounts as $account) { ?>
			<tr onclick="accountRowOnClick()">
				<td><?php echo $account->getName(); ?></td>
				<td>$<?php echo sprintf("%.2f", $account->getBalance()); ?></td>
				<td><?php echo $account->getType(); ?></td>
				<td><?php echo $account->getInterest(); ?>%</td>
				<td>$<?php echo sprintf("%.2f", $account->getMonthlyFee()); ?></td>
				<td><?php if($account->canGoNegative()) { echo "True"; } else{ echo "False";} ?></td>
			</tr>
		<?php }}; ?>
	</table>
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
				<td>$<?php echo sprintf("%.2f", $loan->getInitialAmount()); ?></td> <!-- TODO: Make these right aligned on the period -->
				<td>$<?php echo sprintf("%.2f", $loan->getAmountRemaining()); ?></td>
				<td><?php echo $loan->getAPR(); ?>%</td>
			</tr>
		<?php }}; ?>
	</table>
	<nav class="floating-menu">
		<?php if(!$db->isLoggedIn()): ?>
			<h3>We sold you?</h3>
			<a href="/login">Log In</a>
			<a href="/signup">Sign Up</a>
		<?php else: ?>
			<h3>Hello <?php try {
					echo $user->getFirstName();
				} catch (PGException $e) {
					echo "Internal Server Error";
				} ?></h3>
			<a href="/profile">Check My Profile</a>
			<a href="/api/logout">Logout</a>
		<?php endif; ?>

	</nav>
</div>
</body>
</html>
