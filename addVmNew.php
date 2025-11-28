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
   
    $employeeId = $_POST['employeeId'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $language = $_POST['language'];
    $grade = $_POST['grade'];
    $date = date("Y-m-d");

    $branch = "AGPL000";
    $type = 'VM-HO';

    $sqlQuery = "SELECT 'users' as tableName FROM users WHERE employeeId='$employeeId' 
                 UNION 
                 SELECT 'employee' as tableName FROM employee WHERE empId='$employeeId'  
                 UNION 
                 SELECT 'vmagent' as tableName FROM vmagent WHERE agentId='$employeeId'";
    $resultQuery = mysqli_query($con, $sqlQuery);

    if (mysqli_num_rows($resultQuery) > 0) {
        echo "<script>alert('Employee ID already exists');</script>";
        echo "<script>setTimeout(\"location.href = 'addVmNew.php';\",150);</script>";
    } else {

        $insertUserSql = "INSERT INTO `users` (type, username, password, employeeId, branch, agent, date, ip, language) 
                          VALUES ('$type', '$username', '$password', '$employeeId', '', '', '$date', '', '$language')";


        $insertEmployeeSql = "INSERT INTO `employee` (empId, name, contact, mailId, address, location, designation, photo) 
                              VALUES ('$employeeId', '$name', '', '', '', '', 'VM', '')";


        $insertVmAgentSql = "INSERT INTO `vmagent` (agentId, branch, language, grade) 
                             VALUES ('$employeeId', '$branch', '', '$grade')";
           

        $userResult = mysqli_query($con, $insertUserSql);
        $employeeResult = mysqli_query($con, $insertEmployeeSql);
        $vmAgentResult = mysqli_query($con, $insertVmAgentSql);

        if ($userResult && $employeeResult && $vmAgentResult) {
            echo "<script>alert('New VM Added');</script>";
            echo "<script>setTimeout(\"location.href = 'addVmNew.php';\",150);</script>";
        } else {
            echo "<script>alert('ERROR OCCURRED!!!');</script>";
            echo "<script>setTimeout(\"location.href = 'addVmNew.php';\",150);</script>";
            exit;
        }
    }
}

// Fetch data for each table
$sqlUsers = mysqli_query($con, "SELECT * FROM users WHERE type='VM-HO' ORDER BY id DESC");
$sqlEmployee = mysqli_query($con, "SELECT * FROM employee WHERE designation='VM' ORDER BY id DESC");
$sqlVmAgent = mysqli_query($con, "SELECT * FROM vmagent ORDER BY id DESC");

?>

<style>
	.tab{
	font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs{
	padding: 0;
	margin: 0;
	border: none;
	}   
	.tab .nav-tabs li a{
	color: #123C69;
	background: #f8f8ff;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	letter-spacing: 1px;
	text-transform: uppercase;
	padding: 7px 10px 6px;
	margin: 5px 5px 0px 0px;
	border: none;
	border-bottom: 3px solid #123C69;
	border-radius: 0;
	position: relative;
	z-index: 1;
	transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover{
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius:3px;
	}
	.tab .nav-tabs li a:before{
	content: "";
	background: #f8f8ff;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s,height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before{
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab .tab-content{
	color: #0C1115;
	background: #f8f8ff;
	font-size: 12px;
	/* outline: 2px solid rgba(19,99,126,0.2); */
	position: relative;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
	.tab-content h4{
	color: #123C69;
	font-weight:500;
	}	
	.tab-pane{
	background: #f8f8ff;
	padding:10px;
	}	
	@media only screen and (max-width: 479px){
	.tab .nav-tabs{
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li{
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a{ margin: 0 0 5px; }
	}
	#wrapper{
	background: #f5f5f5;
	}	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	font-size: 12px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
	}	
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
	}
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 11px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.fa_Icon {
	color:#ffcf40;
	}
	.fa_icon{
	color:#990000;
	}
	.row{
	margin-left:0px;
	margin-right:0px;
	}
	.btn-danger{
	background-color: #990000;		
	}
</style>

<div class="modal fade" id="add_new_user" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
				<h3>NEW USER</h3>
			</div>
			<div class="modal-body">
				<form method="POST" class="form-horizontal" action="" enctype="multipart/form-data">
					<div class="form-group row">
						<div class="col-xs-12">
							<div class="col-sm-6">
								<label class="text-success">EMPLOYEE ID</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-id-badge"></span></span>
									<input type="text" name="employeeId" id="employeeId" placeholder="Enter Employee ID" maxlength="10" oninput="document.getElementById('password').value = this.value" class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-6">
								<label class="text-success">NAME</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="name" id="name" required placeholder="Enter Employee Name" required class="form-control" autocomplete="off">
								</div>
							</div>
                            <div class="col-sm-6">
								<label class="text-success">USER NAME</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
									<input type="text" name="username" id="username" required placeholder="Enter UserName"  required class="form-control" autocomplete="off">
								</div>
							</div>						
							<div class="col-sm-6">
								<label class="text-success">PASSWORD</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-lock"></span></span>
									<input type="text" name="password" id="password" required placeholder="Enter Password" maxlength="10" required class="form-control" autocomplete="off" readonly>
								</div>
							</div>
                            <div class="col-sm-6">
							    <p class="text-success"><b>GRADE</b></p>
                                <div class="input-group">
                                    <span class="input-group-addon"><span style="color:#990000;" class="fa fa-buysellads"></span></span>
                                    <select class="form-control" style="padding:0px 5px" name="grade" id="grade"
                                        required>
                                        <option selected="true" value="">Grade</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                    </select>
                                </div>
						    </div>
							<div class="col-sm-6">
							    <p class="text-success"><b>LANGUAGE</b></p>
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
						    </div>	<br>					
							<div class="col-sm-4" style="margin-left:30%;"><br>
								<button class="btn btn-success btn-block" name="submit" id="submit" type="submit"><span style="color:#ffcf40" class="fa fa-plus-square"></span> ADD VM </button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
                    <div class="col-xs-2" style="margin-top:6.5px; margin-left:80%;">
                        <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#add_new_user"><i class="fa fa-user-plus"></i> ADD NEW USER</button>
                    </div>	 
				</div>
				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-user-circle-o"></i> VMAgent Details</a></li>
						<li class=""><a data-toggle="tab" href="#tab-2"><i class="fa fa-vcard"></i> Employee details</a></li>
						<li class=""><a data-toggle="tab" href="#tab-3"><i class="fa fa-users"></i> Users Details</a></li>
					</ul>
					<div class="tab-content">
						<!-- VmAgent Table -->
							<div id="tab-1" class="tab-pane active">
								<div class="panel-body">
									<table id="example5" class="table table-striped table-bordered table-hover">
										<thead>
											<tr class="theadRow">
												<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Employee ID</th>
												<th>Branch</th>
												<th>Language</th>
												<th>Grade</th>
												<th class="text-center">Update</th>
												<!--<th class="text-center">Delete</th>-->
											</tr>
										</thead>
										<tbody id="vmDetails">
											<?php
											$i=1;
											while ($row = mysqli_fetch_assoc($sqlVmAgent)) {
											?>
												<tr id="row_vm_<?php echo $row['id']; ?>">
												<input type='hidden' name='id' value=".$row['id'].">
													<td><?php echo $i; ?></td>
													<td><?php echo $row['agentId']; ?></td>
													<td><?php echo $row['branch']; ?></td>
													<td><?php echo $row['language']; ?></td>
													<td><?php echo $row['grade']; ?></td>
													<td class='text-center'><a class='btn btn-success' title='EDIT' href='editVmData.php?id=<?php echo $row['id']; ?>'><i class='fa fa-edit'></i> </a></td>
													<!--<td class='text-center'>-->
													<!--	<button class='btn btn-danger' onclick='deleteRecord("vm", <?php echo $row['id']; ?>)'><i class='fa fa-trash'></i></button>-->
													<!--</td>										-->
												</tr>
											<?php
											$i++;
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						<!-- Employee Table -->
							<div id="tab-2" class="tab-pane">
								<div class="panel-body">
									<table id="example6" class="table table-striped table-bordered table-hover">
										<thead>
											<tr class="theadRow">
												<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Employee ID</th>
												<th>Name</th>
												<th class="text-center">Update</th>
												<!--<th class="text-center">Delete</th>-->
											</tr>
										</thead>
										<tbody id="employeeDetails">
											<?php
											$i=1;
											while ($row = mysqli_fetch_assoc($sqlEmployee)) {
											?>
												<tr id="row_employee_<?php echo $row['id']; ?>">
													<input type='hidden' name='id' value=".$row['id'].">
													<td><?php echo $i; ?></td>
													<td><?php echo $row['empId']; ?></td>
													<td><?php echo $row['name']; ?></td>
													<td class='text-center'><a class='btn btn-success' title='EDIT' href='editVmData.php?id=<?php echo $row['id']; ?>'><i class='fa fa-edit'></i> </a></td>
													<!--<td class='text-center'>-->
													<!--	<button class='btn btn-danger' onclick='deleteRecord("employee", <?php echo $row['id']; ?>)'><i class='fa fa-trash'></i></button>-->
													<!--</td>-->
												</tr>
											<?php
											$i++;
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						<!-- Users Table -->
							<div id="tab-3" class="tab-pane">
								<div class="panel-body">
									<table id="example7" class="table table-striped table-bordered table-hover">
										<thead>
											<tr class="theadRow">
												<th><i class="fa_Icon fa fa-sort-numeric-asc"></i></th>
												<th>Employee ID</th>
												<th>Username</th>
												<th>Language</th>
												<th class="text-center">Edit</th>
												<!--<th class="text-center">Delete</th>-->
											</tr>
										</thead>
										<tbody id="userDetails">
											<?php
											$i=1;
											while ($row = mysqli_fetch_assoc($sqlUsers)) {
											?>
												<tr id="row_user_<?php echo $row['id']; ?>">
													<input type='hidden' name='id' value=".$row['id'].">
													<td><?php echo $i; ?></td>
													<td><?php echo $row['employeeId']; ?></td>
													<td><?php echo $row['username']; ?></td>
													<td><?php echo $row['language']; ?></td>
													<td class='text-center'><a class='btn btn-success' title='EDIT' href='editVmData.php?id=<?php echo $row['id']; ?>'><i class='fa fa-edit'></i> </a></td>
													<!--<td class='text-center'>-->
													<!--	<button class='btn btn-danger' onclick='deleteRecord("user", <?php echo $row['id']; ?>)'><i class='fa fa-trash'></i></button>-->
													<!--</td>-->
												</tr>
											<?php
											$i++;
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php");?>
<script>
function deleteRecord(table, id) {
    if (confirm('Are you sure you want to delete this record?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'addVmNew.php?id=' + id, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert('Record deleted successfully.');
                location.reload(); 
            }
        };
        xhr.send();
    }
}
</script>

<?php
// if (isset($_GET['id'])) {
//     $id = $_GET['id'];

//     // Delete from the users table
//     $deleteUserSql = "DELETE FROM users WHERE id=$id";
//     if (mysqli_query($con, $deleteUserSql)) {
//         echo '<script>alert("Data has been deleted from users table.");</script>';
//     } else {
//         echo "<script>alert('Error!! Data cannot be deleted from users table')</script>";
//     }

//     // Delete from the employee table
//     $deleteEmployeeSql = "DELETE FROM employee WHERE id=$id";
//     if (mysqli_query($con, $deleteEmployeeSql)) {
//         echo '<script>alert("Data has been deleted from employee table.");</script>';
//     } else {
//         echo "<script>alert('Error!! Data cannot be deleted from employee table')</script>";
//     }

//     // Delete from vmagent table
//     $deleteVmAgentSql = "DELETE FROM vmagent WHERE id=$id";
//     if (mysqli_query($con, $deleteVmAgentSql)) {
//         echo '<script>alert("Data has been deleted from vmagent table.");</script>';
//     } else {
//         echo "<script>alert('Error!! Data cannot be deleted from vmagent table')</script>";
//     }

//     echo "<script>setTimeout(\"location.href = 'addVmNew.php';\", 150);</script>";
// }
?>
