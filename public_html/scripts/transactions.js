function parseMoney($raw){
	return parseFloat($raw).toLocaleString('en-US',{ style: 'currency', currency: 'USD' });
}

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
	json["Balance"] = parseMoney((json["Balance"]));
	json["Monthly Fee"] = parseMoney(json["Monthly Fee"]);
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
	let json = JSON.parse(this.responseText);
	let table = document.getElementById("schedule");

	while(table.lastElementChild !== table.firstElementChild){
		table.removeChild(table.lastElementChild);
	}

	for(let i = 0; i < json.length; i++){
		let row = json[i];
		let tr = document.createElement("tr");

		let balance = parseMoney(row["account_balance"]);
		let amount = parseMoney(row["transaction_amount"]);
		let time = new Date(Date.parse(row["day"])).toLocaleString();

		tr.innerHTML = `<td>${time}</td><td>${amount}</td><td>${balance}</td><td>${row["transaction_description"]}</td>`;
		table.appendChild(tr);
	}
}

function getPendingTransactions(){
	let account_number = document.getElementById("number").innerText;

	const req = new XMLHttpRequest();
	req.addEventListener("load", loadSchedule);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/get_pending_transactions");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_number=${account_number}`);
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

function accountListener(){
	let json = JSON.parse(this.responseText);

	let tr = document.createElement("tr");

	let number = json["Account Number"];
	let name = json["Name"];
	let balance = parseMoney(json["Balance"]);
	let type = json["Type"];
	let interest = json["Interest"];
	let monthly_fee = parseMoney(json["Monthly Fee"]);
	let overdrawn = json["Overdrawn"] ? "Yes" : "No";

	tr.id = `account${number}`;
	tr.onclick = () => accountRowOnClick(tr);
	tr.innerHTML = `<td>${name}</td><td style="text-align:right">${balance}</td><td>${type}</td><td>${interest}%</td><td style="text-align:right">${monthly_fee}</td><td>${overdrawn}</td>`;
	document.getElementById("accounts").appendChild(tr);
}


function displayAccount(account_number){
	let params = `account_number=${account_number}`;
	const req = new XMLHttpRequest();
	req.addEventListener("load", accountListener);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/get_account_info");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(params);
}

function getAccounts(){
	const req = new XMLHttpRequest();

	function _thisListener(){
		let json = JSON.parse(this.responseText);
		for(let i = 0; i < json.length; i++){
			displayAccount(json[i]);
		}
	}

	req.addEventListener("load", _thisListener);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/get_accounts");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send("");
}