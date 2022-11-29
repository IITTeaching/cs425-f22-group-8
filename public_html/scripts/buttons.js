function allGood(){
    let submit = document.getElementById("submit");
    submit.className = "ring-button";
    submit.disabled = false;
    submit.hidden = false;

    let wrapper = document.getElementById("submit_wrapper");
    wrapper.className = "wrap";
}

function missingInfo(){
    let submit = document.getElementById("submit");
    submit.className = "";
    submit.disabled = true;
    submit.hidden = true;

    let wrapper = document.getElementById("submit_wrapper");
    wrapper.className = "";
}