import {displayAccount} from "./transactions";

function listener(){
	if(this.status !== 200){
		return;
	}
	alert(this.responseText);
	displayAccount(this.getResponseHeader("Account-Number"))
}

export function createAccount(){
	const form = document.forms.create_account_form;
	const name = encodeURIComponent(form.elements.account_name.value);
	const type = form.elements.account_type.value;
	const initial = form.elements.initial_balance.value;

	if(initial < 0){
		alert("The initial amount of an account can not be negative.");
		return;
	}


	const req = new XMLHttpRequest();
	req.addEventListener("load", listener);
	req.open("POST", "https://cs425.lenwashingtoniii.com/api/create_account");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_name=${name}&account_type=${type}&initial_balance=${initial}`);
}