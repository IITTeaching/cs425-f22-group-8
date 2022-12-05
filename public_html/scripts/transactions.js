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
    if(type.value === "Transfer"){
        transfer_account.hidden = false;
        transfer_account.required = true;
    } else{
        transfer_account.hidden = true;
        transfer_account.required = false;
    }
}

function transactionListener() {
    alert(this.responseText);
    window.location.reload();
}

function transact(){
    let transaction_type = document.getElementById("transaction").value;
    let amount = document.getElementById("amount").value;
    let this_account = document.getElementById("number").value;
    let description = document.getElementById("description").value;
    let params = `transaction_type=${transaction_type}&amount=${amount}&description=${description}`;
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