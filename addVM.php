<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'ITMaster') {
	include("header.php");
	include("menuItMaster.php");
} else {
	include("logout.php");
}
include("dbConnection.php");


if (isset($_POST['submit'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$employeeId = $_POST['employeeId'];
	$language = $_POST['language'];
	$date = date("Y-m-d");
	$name = $_POST['name'];
	$designation = "VM";
	$branch = "AGPL000";
	$type = "VM-HO";

	$checkSql = "SELECT 'users' as tableName FROM users WHERE employeeId='$employeeId'
                 UNION
                 SELECT 'employee' as tableName FROM employee WHERE empId='$employeeId'
                 UNION
                 SELECT 'vmagent' as tableName FROM vmagent WHERE agentId='$employeeId'";

	$result = mysqli_query($con, $checkSql);

	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$tableName = $row['tableName'];
		echo "<script>alert('Employee ID already exists')</script>";
		echo "<script>setTimeout(\"location.href = 'addVM.php';\",150);</script>";
	} else {

		$insertSql = $insertSql = "INSERT INTO `users` (type, username, password, employeeId, branch, agent, date, ip, language) 
		VALUES ('$type', '$username', '$password', '$employeeId', '', '$designation', '$date', '', '$language')";

		$insertSql1 = "INSERT INTO `employee` (empId, name, designation) 
					   VALUES ('$employeeId', '$name', '$designation')";


		$insertSql1 = "INSERT INTO `employee` (empId, name, contact, mailId, address, location, designation, photo) 
               VALUES ('$employeeId', '$name', '', '', '', '', '$designation', '')";


		$insertSql2 = "INSERT INTO `vmagent` (agentId, branch, language, grade) 
					   VALUES ('$employeeId', '$branch', '', '')";


		if (mysqli_query($con, $insertSql) && mysqli_query($con, $insertSql1) && mysqli_query($con, $insertSql2)) {
			echo "<script>alert('New VM Added')</script>";
			echo "<script>setTimeout(\"location.href = 'addVM.php';\",150);</script>";
		} else {
			echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'addVM.php'\",150);</script>";
			exit;
		}
	}
}
?>



<style>
	#wrapper {
		background-color: #E3E3E3;
	}

	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 20px;
		color: #123C69;
	}

	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
		background-color: #fffafa;
	}

	.text-success {
		color: #123C69;
		text-transform: uppercase;
		font-weight: bold;
		font-size: 12px;
	}

	.btn-primary {
		background-color: #123C69;
	}

	.theadRow {
		text-transform: uppercase;
		background-color: #123C69 !important;
		color: #f2f2f2;
		font-size: 11px;
	}

	.dataTables_empty {
		text-align: center;
		font-weight: 600;
		font-size: 12px;
		text-transform: uppercase;
	}

	.btn-success {
		display: inline-block;
		padding: 0.5em 1.4em;
		margin: 0 0.3em 0.3em 0;
		border-radius: 0.15em;
		box-sizing: border-box;
		text-decoration: none;
		font-size: 12px;
		font-family: 'Roboto', sans-serif;
		text-transform: uppercase;
		color: #fffafa;
		background-color: #123C69;
		box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
		text-align: center;
		position: relative;
	}

	.fa_Icon {
		color: #ffd700;
	}

	.fa_icon {
		color: #990000;
	}

	.row {
		margin-left: 0px;
		margin-right: 0px;
	}

	#wrapper .panel-body {
		box-shadow: 10px 15px 15px #999;
		border: 1px solid #edf2f9;
		background-color: #f5f5f5;
		border-radius: 3px;
		padding: 20px;
	}

	button {
		transform: none;
		box-shadow: none;
	}

	button:hover {
		background-color: gray;
		cursor: pointer;
	}

	legend {
		margin-left: 8px;
		width: 180px;
		background-color: #123C69;
		padding: 1px 15px;
		line-height: 30px;
		font-size: 18px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0, 0, 0, 0.8);
		margin-bottom: 0px;
		letter-spacing: 2px;
		text-transform: uppercase;
	}

	.block-button {
		background-color: #123C69;
		color: green;
		border: none;
		padding: 5px 10px;
		cursor: pointer;
	}
</style>










<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body"
					style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					<h3 class="text-success"><i class="fa_icon fa fa-user-plus"></i> Add VM </h3><br><br>
					<form method="POST" action="">
						<div class="col-lg-2">
							<p class="text-success"><b>Employee Id</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-user-circle"></span></span>
								<input type="text" name="employeeId" id="employeeId" class="form-control" required
									oninput="document.getElementById('password').value = this.value">
							</div>
						</div>
						<div class="col-lg-2">
							<p class="text-success"><b>Name</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-user"></span></span>
								<input type="text" name="name" id="name" class="form-control" required>
							</div>
						</div>
						<div class="col-lg-2">
							<p class="text-success"><b>Username</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-unlock-alt"></span></span>
								<input type="text" class="form-control" name="username" id="username" required>
							</div>
						</div>
						<div class="col-lg-2">
							<p class="text-success"><b>Password</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-key"></span></span>
								<input type="text" name="password" id="password" class="form-control" readonly required>
							</div>
						</div>
						<div class="col-lg-2">
							<p class="text-success"><b>Language</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-language"></span></span>
								<select class="form-control" style="padding:0px 5px" name="language" id="language"
									required>
									<option selected="true" value="">Language</option>
									<option value="1">Kannada</option>
									<option value="2">Tamil</option>
									<option value="3">Telugu</option>
								</select>
							</div>
						</div>
						<div class="col-lg-2">
							<button type="submit" name="submit" id="submit" class="btn btn-success btn-block"
								style="margin-top:28px"><span class="fa_Icon fa fa-sign-in"> </span>&nbsp; ADD
								VM</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<?php if ($type == 'ITMaster') { ?>
			<div class="col-lg-12">
				<div class="hpanel">
					<div class="panel-heading font-bold">
						<legend> <i style="padding-top:15px" class="fa_Icon fa fa-user-circle"></i> VM Details </legend>
					</div>
					<div class="panel-body"
						style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
						<table id="example2" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th><span class="fa fa-sort-numeric-asc"></span></th>
									<th>Employee ID</th>
									<th>Name</th>
									<th>Username</th>
									<th>Password</th>
									<th>Branch </th>
									<th>Language</th>
									<th class="text-center">Delete / Edit</th>
								</tr>
							</thead>
							<tbody>


								<?php
												$i = 1;

if (isset($_POST['deleteBtn'])) {
    $empId = $_POST['empId'];

    $deleteUsers = "DELETE FROM users WHERE employeeId = '$empId'";
    $resultUsers = mysqli_query($con, $deleteUsers);

    $deleteVmAgent = "DELETE FROM vmagent WHERE agentId = '$empId'";
    $resultVmAgent = mysqli_query($con, $deleteVmAgent);

    $deleteEmployee = "DELETE FROM employee WHERE empId = '$empId'";
    $resultEmployee = mysqli_query($con, $deleteEmployee);

    if ($resultUsers && $resultVmAgent && $resultEmployee) {
        echo "<script>alert('Employee ID: " . $empId . " Deleted!')</script>";
        echo "<script>window.location.href = 'addVM.php';</script>";
    } else {
        echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";
        echo "<script>window.location.href = 'addVM.php';</script>";
    }
}

$sql = mysqli_query($con, "SELECT e.*, u.username, u.password, u.language, v.branch
                           FROM employee e 
                           INNER JOIN users u 
                           ON e.empId = u.employeeId
                           INNER JOIN vmagent v
                           ON e.empId = v.agentId
                           WHERE e.designation='VM' 
                           ORDER BY e.empId ASC;");

while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>";
    echo "<td>" . $i . "</td>";
    echo "<td>" . $row['empId'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . $row['password'] . "</td>";
    echo "<td>" . $row['branch'] . "</td>";
    echo "<td>";
    if ($row['language'] == 1) {
        echo "Kannada";
    } elseif ($row['language'] == 2) {
        echo "Tamil";
    } elseif ($row['language'] == 3) {
        echo "Telugu";
    } else {
        echo "";
    }
    echo "</td>";

    echo '<td class="text-center">';
    echo '<form method="POST" action="" enctype="multipart/form-data">';
    echo '<input type="hidden" name="empId" value="' . $row['empId'] . '">';
    if (strpos($row['empId'], 'VM-TST-') === false) {
        echo '<button class="block-button" type="submit" name="deleteBtn"><i class="fa fa-times fa-lg" style="color:red"></i></button>';
    }
									if (strpos($row['password'], 'PWDVM') !== false) {
										//echo '<button data-toggle="modal" data-target="#myModal" class="block-button" type="submit" name="editBtn"><i class="fa fa-edit fa-lg" style="color:red"></i></button>';
										
										echo '<button data-toggle="modal" data-target="#editModal' . $i . '" class="block-button" type="button" name="editBtn"><i class="fa fa-edit fa-lg" style="color:red"></i></button>';
        
									}
									echo '</form>';
									echo "</td>";
									echo "</tr>";
										
									
									echo '<div class="modal fade" id="editModal' . $i . '" tabindex="-1" role="dialog" aria-labelledby="editModalLabel' . $i . '" aria-hidden="true">';
									echo '<div class="modal-dialog" role="document">';
									echo '<div class="modal-content">';
									echo '<div class="modal-header" style="background-color: #123C69; color: #fff;">';
									echo '<h2 class="text-success" id="editModalLabel' . $i . '"  style="color: white;">Edit Language</h2>';

							
									echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
									echo '<span aria-hidden="true">&times;</span>';
									echo '</button>';
									echo '</div>';
									echo '<div class="modal-body">';
echo '<form method="POST" action="">'; 

// Structure the fields in a single row
echo '<div class="form-group row">';
echo '<label for="username" class="col-sm-3 col-form-label">UserName:</label>';
echo '<div class="col-sm-9">';
echo '<input type="text" class="form-control" id="username" name="username" value="' . $row['name'] . '" readonly>';
echo '</div>';
echo '</div>';

echo '<div class="form-group row">';
echo '<label for="empId" class="col-sm-3 col-form-label">EmployeeID:</label>';
echo '<div class="col-sm-9">';
echo '<input type="text" class="form-control" id="empId" name="empId" value="' . $row['empId'] . '" readonly>';
echo '</div>';
echo '</div>';

echo '<div class="form-group row">';
echo '<label for="language" class="col-sm-3 col-form-label">Language:</label>';
echo '<div class="col-sm-9">';
echo '<select class="form-control" id="language" name="language">';

echo '<option selected="true" value="">Select Language</option>';
echo '<option value="1">Kannada</option>';
echo '<option value="2">Tamil</option>';
echo '<option value="3">Telugu</option>';
echo '</select>';
echo '</div>';
echo '</div>';

echo '<div class="form-group row">';
echo '<div class="col-sm-12 text-right">';
echo '<button class="btn btn-primary" type="submit" name="submitvm" style="padding: 12px 24px;">Submit</button>';
echo '</div>';
echo '</div>';

echo '</form>';
echo '</div>';

$i++;

								}
if (isset($_POST['submitvm'])) {
    $language = $_POST['language'];
    $empId = $_POST['empId'];

    // Perform the update query based on the selected language and empId
    $updateUsersQuery = "UPDATE users SET language='$language' WHERE employeeId='$empId'";
    $res2 = mysqli_query($con, $updateUsersQuery);

    if ($res2) {
        echo "<script>alert('Language Updated')</script>";
        echo "<script>setTimeout(\"location.href = 'addVM.php';\", 150);</script>";
		exit;
    } else {
        echo "<script>setTimeout(\"location.href = 'addVM.php'\", 150);</script>";
    }
}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		 <?php } ?>
		<div style="clear:both"></div>
	</div>
	<?php include("footer.php"); ?>