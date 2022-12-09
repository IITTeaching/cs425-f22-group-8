function _setError(element, error_message){
	element.classList.add("err");
	element.innerHTML = error_message;
}

function _removeError(element){
	element.classList.remove("err");
	element.innerHTML = "";
}