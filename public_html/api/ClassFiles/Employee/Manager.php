<?php

require_once "Employee.php";
require_once "LoanShark.php";
require_once "Teller.php";
require_once (dirname(__DIR__, 2) . "/ConfigFiles/ManagerConfig.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/Address.php");
require_once (dirname(__DIR__, 2) . "/ClassFiles/Authentication.php");

class Manager extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new ManagerConfig());
	}

	public function addEmployee(string $name, EmployeeTypes $role, Address $address, int $ssn, Address $branch, float $salary,
									string $password): Employee{
		$name = $this->prepareData($name);
		$ssn = hash_hmac("sha256", $ssn, "A super duper secret key");
		$username = explode(" ", strtolower(str_replace(".", "", $name)));
		$password = password_hash($this->prepareData($password), CRYPT_SHA512);
		$username = $username[0][0] . $username[count($username)-1]; // TODO: Add middle names or numbers in case there is another person with a similar name

		$sql = sprintf("INSERT INTO Employee(name, role, address, ssn, branch, salary) VALUES('%s','%s',%d,'%s',%d,%f) RETURNING id",
			$name, $role->value, $address->getAddressId(), $ssn, $branch->getAddressId(), $salary);
		$result = $this->query($sql);
		$this->checkQueryResult($result);
		$id = pg_fetch_result($result, 0);
		switch ($role){
			case EmployeeTypes::LoanShark:
				$employee = new LoanShark($id);
				break;
			case EmployeeTypes::Teller:
				$employee = new Teller($id);
				break;
			case EmployeeTypes::Manager:
				$employee = new Manager($id);
				break;
		}

		$_2fa = (new Authentication())->createSecretKey();
		$employee->setAuthCode($_2fa);

		$this->query(sprintf("INSERT INTO EmployeeLogins VALUES(%d, '%s', '%s','%s')",
			$employee->getEmployeeID(), $username, $password, $employee->getAuthCode()));

		return $employee;
	}

	protected function employeeType(): EmployeeTypes { return EmployeeTypes::Manager; }

	public static function fromUsername(string $username): false|Manager{
		$id = parent::fromUsername($username);
		if(!$id) { return false; }
		return new Manager($id);
	}
} ?>

<!DOCTYPE html>
<html>
<style>
body {font-family: Arial, Helvetica, sans-serif;}
* {box-sizing: border-box;}

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
  background-color: #474e5d;
  padding-top: 50px;
}

/* Modal Content/Box */
.modal-content {
  background-color: #fefefe;
  margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

/* Style the horizontal ruler */
hr {
  border: 1px solid #f1f1f1;
  margin-bottom: 25px;
}
 
/* The Close Button (x) */
.close {
  position: absolute;
  right: 35px;
  top: 15px;
  font-size: 40px;
  font-weight: bold;
  color: #f1f1f1;
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

      <label for="address_num"><b>Address Num</b></label>
      <input type="text" placeholder="3301" name="address_num" required>

	  <input type="text" id="direction" name="direction" pattern="[N|E|S|W]?" list="directions" placeholder="Direction">
		<datalist id="directions">
			<option>N</option>
			<option>E</option>
			<option>S</option>
			<option>W</option>
		</datalist>

		<input type="text" name="streetname" id="streetname" placeholder="Street Name" required>
        	<input type="text" name="city" id="city" placeholder="City" required>

			<input name = "state" id="state" list="states" placeholder="State" required>
			<datalist id="states">
				<?php while($row = pg_fetch_row($state_result)) {?>
					<?php echo $row[0] . PHP_EOL; ?>
				<?php }?>
			</datalist>

			<input type="number" name="zipcode" id="zipcode" placeholder="Zipcode" required min="10000" max="99999" autocomplete="postal-code" inputmode="decimal">

		<label for="apt"><b>Apt/Unit></b></label>
		<input type="text" name="apt" id="apt" value="">

		<label for="branch"><b>Branch</b></label>
		<input name="branch" id="branch" oninput="checkInfo()" oninput="checkInfo()" list="branches" placeholder="Branch" required>
		<datalist id="branches">
			<?php foreach($dct as $key => $value) { ?>
				<option value="<?php echo $key?>"><?php echo $value ?></option>
			<?php } ?>
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