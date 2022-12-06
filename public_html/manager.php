<!DOCTYPE html>
<html>
<style>
body {
  font-family: Arial, Helvetica, sans-serif;
  box-sizing: border-box; 
  background-color: rgb(128, 128, 128);
}

/* Full-width input fields */
input[type=text], input[type=password] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  background: #f1f1f1;
}

/* Add a background color when the inputs get focus */
input[type=text]:focus, input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}

/* Set a style for all buttons */
button {
  background-color: #04AA6D;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
  opacity: 0.9;
}

button:hover {
  opacity:1;
}

/* Extra styles for the cancel button */
.cancelbtn {
  padding: 14px 20px;
  background-color: #f44336;
}

/* Float cancel and signup buttons and add an equal width */
.cancelbtn, .signupbtn {
  float: left;
  width: 50%;
}

/* Add padding to container elements */
.container {
  padding: 16px;
}

/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: #434E4A;
  padding-top: 50px;
}

/* Modal Content/Box */
.modal-content {
  background-color: #F0F0F0;
  margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

/* Style the horizontal ruler */
hr {
  border: 1px solid #EAEAEA;
  margin-bottom: 25px;
}
 
/* The Close Button (x) */
.close {
  position: absolute;
  right: 35px;
  top: 15px;
  font-size: 40px;
  font-weight: bold;
  color: #EAEAEA;
}

.close:hover,
.close:focus {
  color: #f44336;
  cursor: pointer;
}

/* Clear floats */
.clearfix::after {
  content: "";
  clear: both;
  display: table;
}

/* Change styles for cancel button and signup button on extra small screens */
@media screen and (max-width: 300px) {
  .cancelbtn, .signupbtn {
     width: 100%;
  }
}
</style>
<body>

<h2>Welcome Manager!</h2>
<h3>This is your homepage.</h3>

<button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Add New Employee</button>

<div id="id01" class="modal">
  <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
  <form class="modal-content" action="/action_page.php">
    <div class="container">
      <h1>Add Employee</h1>
      <p>Please fill in the following form with the Employee's information.</p>

      <hr>
      <label for="fullname"><b>Name</b></label>
      <input type="text" placeholder="Enter Full Name" name="fullname" required>

      <label for="role"><b>Role</b></label>
      <input type="text" placeholder="Enter Role (Teller, Loan Shark, Manager)" name="role" required>

      <label for="address_num"><b>Address</b></label>
      <input type="text" placeholder="3301" name="address_num" required>

      <label for = "direction"><b>Direction</b></label>
	    <input type="text" name="direction" pattern="[N|E|S|W]?" list="directions" placeholder="Direction">
		  <datalist id="directions">
			  <option>N</option>
		  	<option>E</option>
		  	<option>S</option>
		  	<option>W</option>
		  </datalist>

      <label for = "streetname"><b>Street</b></label>
		  <input type="text" name="streetname" id="streetname" placeholder="Street Name" required>

      <label for = "city"><b>City</b></label>
      <input type="text" name="city" placeholder="City" required>

      <label for = "state"><b>State</b></label>
      <input type="text" name="state" placeholder="City" required>

      <label for = "zipcode"><b>Zipcode</b></label>
			<input type="text" name="zipcode" placeholder="Zipcode" required>

		  <label for="apt"><b>Apt/Unit</b></label>
		  <input type="text" name="apt" placeholder ="Apt/Unit #" value="">

      <label for = "branch"><b>Branch</b></label>
	    <input type="text" name="branch" list="branches" placeholder="Branch" required>
		  <datalist id="branches">
			  <option>WCS Western</option>
		  	<option>WCS Green Line</option>
		  	<option>WCS Cottage Grove</option>
		  	<option>WCS Woodlawn</option>
		  </datalist>

      <div class="clearfix">
        <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
        <button type="submit" class="signupbtn">Add Employee</button>
      </div>
    </div>
  </form>
</div>

<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
</body>
</html> 