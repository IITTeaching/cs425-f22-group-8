function accountRowOnClick(row){
	let account_number = row["id"];
	account_number = /account(\d+)/.exec(account_number)[1];
	if(account_number !== document.getElementById("number").innerText){
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
	let dct = {
		"Balance": "balance",
		"Interest": "interest",
		"Monthly Fee": "monthly_fees",
		"Name": "name",
		"Overdrawn": "overdrawn",
		"Account Number": "number"
		//"Type": "",
	};
	json["Overdrawn"] = json["Overdrawn"] ? "Yes" : "No";
	json["Balance"] = `\$${json["Balance"]}`;
	json["Monthly Fee"] = `\$${json["Monthly Fee"]}`;
	json["Interest"] = `${json["Interest"]}%`;

	let keys = Object.keys(dct);
	for(let i = 0; i < keys.length; i++){
		let key = keys[i];
		document.getElementById(dct[key]).innerText = json[key];
	}
}

function checkTransactionType(){
	let type = document.getElementById("transaction");
	let transfer_account = document.getElementById("transfer_to_account_number");
	let line_break = document.getElementById("transfer_break");
	if(type.value === "Transfer"){
		transfer_account.hidden = line_break.hidden = false;
		transfer_account.required = true;
	} else{
		transfer_account.hidden = line_break.hidden = true;
		transfer_account.required = false;
	}
}

function transactionListener() {
	alert(this.responseText);  // TODO: Don't update the page unless its one of the success headers.
	window.location.reload(); // TODO: Come up with a better way of updating the page.
}

function transact(){
	let transaction_type = document.getElementById("transaction").value;
	let amount = document.getElementById("amount").value;
	let this_account = document.getElementById("number").innerText;
	let description = document.getElementById("description").value;
	let params = `transaction_type=${transaction_type}&amount=${encodeURIComponent(amount)}&description=${encodeURIComponent(description)}&`;
	if(transaction_type === "Transfer"){
		let final_account = document.getElementById("transfer_to_account_number").value;
		params += `initial_account=${this_account}&final_account=${final_account}`;
	} else{
		params += `account_number=${this_account}`;
	}
	const req = new XMLHttpRequest();
	req.addEventListener("load", transactionListener);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/transact");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(params)
}

function loadSchedule(){
	//let json = JSON.parse(`[{"day":"2022-12-04 15:33:43.363774","transaction_amount":"50","account_balance":"400","transaction_description":"Testing statement"},{"day":"2022-12-04 15:36:41.953533","transaction_amount":"100","account_balance":"500","transaction_description":"Allowance From Grandma"},{"day":"2022-12-04 15:37:00.009377","transaction_amount":"-30","account_balance":"470","transaction_description":"Youtube Subscription"},{"day":"2022-12-04 15:39:18.224074","transaction_amount":"-10","account_balance":"460","transaction_description":"Spotify Subscription"},{"day":"2022-12-04 15:41:18.738087","transaction_amount":"-10","account_balance":"450","transaction_description":"Spotify Subscription"},{"day":"2022-12-05 09:44:50.30326","transaction_amount":"50","account_balance":"500","transaction_description":"Deposit authorized by User 14"},{"day":"2022-12-05 09:45:00.653844","transaction_amount":"50","account_balance":"550","transaction_description":"Deposit authorized by User 14"},{"day":"2022-12-05 09:45:10.855866","transaction_amount":"50","account_balance":"600","transaction_description":"Deposit authorized by User 14"},{"day":"2022-12-05 09:46:05.146962","transaction_amount":"-100","account_balance":"500","transaction_description":"Withdrawal authorized by User 14"},{"day":"2022-12-05 09:49:20.199456","transaction_amount":"-100","account_balance":"400","transaction_description":"Transfer from 1 to 2"},{"day":"2022-12-05 09:51:11.205189","transaction_amount":"-100","account_balance":"300","transaction_description":"Transfer from 1 to 2"},{"day":"2022-12-05 09:51:44.41963","transaction_amount":"-20","account_balance":"280","transaction_description":"Withdrawal authorized by User 14"}]`);
	let json = JSON.parse(this.responseText);
	let table = document.getElementById("schedule");

	while(table.lastElementChild !== table.firstElementChild){
		table.removeChild(table.lastElementChild);
	}

	for(let i = 0; i < json.length; i++){
		let row = json[i];
		let tr = document.createElement("tr");
		tr.innerHTML = `<td>${row["day"]}</td><td>$${row["transaction_amount"]}</td><td>$${row["account_balance"]}</td><td>${row["transaction_description"]}</td>`;
		table.appendChild(tr);
	}
}

function getMonthlyStatement(){
	let month = document.getElementById("statement_month").value;
	let account_number = document.getElementById("number").innerText;

	const req = new XMLHttpRequest();
	req.addEventListener("load", loadSchedule);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/get_monthly_statement");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_number=${account_number}&statement_month=${month}`);
}