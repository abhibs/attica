<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'ITMaster') {
	include("header.php");
	include("menuItMaster.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
$date = date('Y-m-d');

$branchCode = $_SESSION['branchCode'];
$sql = "SELECT branchId,branchName FROM branch";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$branchId = $row['branchId'];
$branchName = $row['branchName'];
?>
<style>
	.tab {
		font-family: 'Titillium Web', sans-serif;
	}

	.tab .nav-tabs {
		padding: 0;
		margin: 0;
		border: none;
	}

	.tab .nav-tabs li a {
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
	.tab .nav-tabs li.active a:hover {
		color: #f2f2f2;
		background: #123C69;
		border: none;
		border-bottom: 3px solid #ffa500;
		font-weight: 600;
		border-radius: 3px;
	}

	.tab .nav-tabs li a:before {
		content: "";
		background: #f8f8ff;
		height: 100%;
		width: 100%;
		position: absolute;
		bottom: 0;
		left: 0;
		z-index: -1;
		transition: clip-path 0.3s ease 0s, height 0.3s ease 0.2s;
		clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}

	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before {
		height: 0;
		clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}

	.tab .tab-content {
		color: #0C1115;
		box-shadow: 10px 15px 15px #999;
		font-size: 12px;
		/* outline: 2px solid rgba(19,99,126,0.2); */
		position: relative;
		border: 1px solid #edf2f9;
		border-radius: 10px;
		padding: 5px;
		
	}

	.tab-content h4 {
		color: #123C69;
		font-weight: 500;
	}

	.tab-pane {
		background: #f8f8ff;
		padding: 10px;
	}

	@media only screen and (max-width: 479px) {
		.tab .nav-tabs {
			padding: 0;
			margin: 0 0 15px;
		}

		.tab .nav-tabs li {
			width: 100%;
			text-align: center;
		}

		.tab .nav-tabs li a {
			margin: 0 0 5px;
		}
	}

	#wrapper {
			background-color: #E3E3E3;
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

	.fa_Icon {
		color: #ffa500;
	}

	fieldset {
		margin-top: 1.5rem;
		margin-bottom: 1.5rem;
		border: none;
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 5px;
		box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
	}

	legend {
		margin-left: 8px;
		width: 150px;
		background-color: #123C69;
		padding: 0px 10px;
		line-height: 30px;
		font-size: 18px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0, 0, 0, 0.8);
		margin-bottom: 0px;
		letter-spacing: 2px;
		text-transform: uppercase;
		font-weight: bold;
	}

	button {
		transform: none;
		box-shadow: none;
	}

	button:hover {
		background-color: gray;
		cursor: pointer;
	}

	legend:after {
		content: "";
		height: 0;
		width: 0;
		background-color: transparent;
		border-top: 0.0rem solid transparent;
		border-right: 0.35rem solid black;
		border-bottom: 0.45rem solid transparent;
		border-left: 0.0rem solid transparent;
		position: absolute;
		left: -0.075rem;
		bottom: -0.45rem;
	}

	.block-button {
		background-color: #123C69;
		color: red;
		border: none;
		padding: 3px 8px;
		cursor: pointer;
		
	}

	 select {
            width: 100%;
            padding: 5px 10px;
            margin: 1px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
		
		.itname{
			
			 width: 100%;
            padding: 5px 10px;
            margin: 1px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
		}

</style>

<datalist id="branchList">
<option value="All Branches"> All Branches</option>
	<?php
	$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch where status=1");
	while ($branchList = mysqli_fetch_array($branches)) {
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="col-xs-8">
					<legend> <i style="padding-top:6px" class="fa_Icon fa fa-ticket"></i> IT Issues </legend>
				</div>
				<div class="col-xs-4" style="margin-top:1px;margin-bottom:50px;">
					<form action="" method="GET">
						<div class="input-group">
							<input list="branchList" class="form-control" name="branchId" placeholder="SELECT BRANCH"
								required>
							<span class="input-group-btn">
								<button class="btn btn-primary btn-block" id="branchIDsearch" type="submit"><i
										class="fa fa-search"></i></button><br>
							</span>
						</div>
					</form>
				</div>


				<div class="tab" role="tabpanel">
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#pending" aria-controls="pending" role="tab"
								data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> Branch_Issue </a></li>
                        <li role="presentation"><a href="#vm" aria-controls="vm" role="tab"
								data-toggle="tab"><i class="fa_Icon fa fa-refresh"></i> VM_Issue</a></li>
						<li role="presentation"><a href="#approved" aria-controls="approved" role="tab"
								data-toggle="tab"><i class="fa_Icon fa fa-check"></i> Branch_Resolved</a></li>
						<li role="presentation"><a href="#vmapproved" aria-controls="vmapproved" role="tab"
								data-toggle="tab"><i class="fa_Icon fa fa-check"></i> VM_Resolved</a></li>

					</ul>
					<div class="tab-content tabs" style="min-height:300px;">
						<div role="tabpanel" class="tab-pane fade in active" id="pending">
							<h4>PENDING</h4>
							<table id="example5" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<th>BranchId</th>
										<th>BranchName</th>
										<th>Issue_Type</th>
										<th>BM_Contact</th>
										<th>AnydeskID</th>
										<th>Remarks</th>
										<th>Date</th>
										<th>Time</th>
										<th>Priority_level</th>
										<th>IT_remarks</th>
										<th>done</th>
										<th>reject</th>
									</tr>
								</thead>
								<tbody>
 <?php
$i = 1;

if (isset($_GET['branchId'])) {
    $branchId = $_GET['branchId'];
    if ($branchId == 'All Branches') {
        $whereCondition = "status = 'Pending'";
    } else {
        $whereCondition = "branchId = '$branchId' AND status = 'Pending'";
    }
} else {
    //$whereCondition = "date = '$date' AND status = 'Pending'";
    $whereCondition = " status = 'Pending'";
}

$query = mysqli_query($con, "SELECT id, branchId, branchName, issueType, priority, date, time, contact, anydesk, remarks, status, rslvDate FROM it_issue WHERE $whereCondition AND status = 'Pending' AND branchName!='HO' ORDER BY date DESC");

while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>";
    echo "<td>" . $i . "</td>";
    echo "<td>" . $row['branchId'] . "</td>";
    echo "<td>" . $row['branchName'] . "</td>";
    echo "<td>" . $row['issueType'] . "</td>";
    echo "<td>" . $row['contact'] . "</td>";
    echo "<td>" . $row['anydesk'] . "</td>";
    echo "<td>" . $row['remarks'] . "</td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td>" . $row['time'] . "</td>";
    echo "<td class='text-center' >";
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="issueId" value="' . $row['id'] . '">';
    echo '<select name="priority" required>';
	echo '<option selected="true" disabled="disabled" value="">Select Priority</option>';
    echo '<option value="Normal" ' . ($row['priority'] == 'Normal' ? 'selected' : '') . '>Normal</option>';
    echo '<option value="Medium" ' . ($row['priority'] == 'Medium' ? 'selected' : '') . '>Medium</option>';
    echo '<option value="High" ' . ($row['priority'] == 'High' ? 'selected' : '') . '>High</option>';
    echo '<option value="Urgent" ' . ($row['priority'] == 'Urgent' ? 'selected' : '') . '>Urgent</option>';
    echo '</select>';
    echo "</td>";
	
	
	echo "<td><center><input type='text' class='itname' name='itname' value='" . $row['itname'] . "' required></center></td>";
	
    echo '<td class="text-center">
            <button class="block-button" type="submit" name="confirmBtn"><i class="fa fa-check fa-lg" style="color:red"></i></button>
          </td>';
    echo '<td class="text-center">
            <button class="block-button" type="submit" name="rejectBtn"><i class="fa fa-close fa-lg" style="color:red"></i></button>
          </td>';
    echo '</form>';
    echo "</tr>";
    $i++;
}

if (isset($_POST['confirmBtn'])) {
    $issueId = $_POST['issueId'];
    $priority = $_POST['priority'];
    $itname = $_POST['itname'];            
    $currentDate = date('Y-m-d');

    $updateSql = "UPDATE it_issue SET status='Done', rslvDate='$currentDate', priority='$priority' ,itname='$itname' WHERE id='$issueId'";

    if (mysqli_query($con, $updateSql)) {
        echo '<script>alert("Your confirmation has been updated.");</script>';
        echo "<script>setTimeout(\"location.href = 'ITissueHO.php';\", 150);</script>";
        exit;
    } else {
        echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
    }
}

if (isset($_POST['rejectBtn'])) {
    $issueId = $_POST['issueId'];
  
    $itname = $_POST['itname'];            
    $currentDate = date('Y-m-d');

    $updateSql = "UPDATE it_issue SET status='Rejected', rslvDate='$currentDate',itname='$itname' WHERE id='$issueId'";

    if (mysqli_query($con, $updateSql)) {
        echo '<script>alert("Your confirmation has been updated.");</script>';
        echo "<script>setTimeout(\"location.href = 'ITissueHO.php';\", 150);</script>";
        exit;
    } else {
        echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
    }
}
?>

								</tbody>
							</table>
						</div>
						
					<div role="tabpanel" class="tab-pane fade" id="vm">
							<h4>VM Issues</h4>
							<table id="example6" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<th>EmployeeId</th>
										<th>Username</th>										
										<th>Issue_Type</th>
										<th>AnydeskID</th>
										<th>Remarks</th>
										<th>Date</th>
										<th>Time</th>
										<th>Priority_level</th>
										<th>IT_remarks</th>
										<th>done</th>
										<th>reject</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									$query = mysqli_query($con, "SELECT id, empId, username, issueType, priority, date, time, anydesk, remarks, status, rslvDate FROM it_issue WHERE status = 'Pending' AND branchName='HO' ORDER BY date DESC");
									while ($row = mysqli_fetch_assoc($query)) {
										echo "<tr";
										if ($row['status'] == 'Done') {
											echo ' style="background-color: #ffe6e6;"';
										}
										echo ">";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['empId'] . "</td>";
										echo "<td>" . $row['username'] . "</td>";
										echo "<td>" . $row['issueType'] . "</td>";
										echo "<td>" . $row['anydesk'] . "</td>";
    echo "<td>" . $row['remarks'] . "</td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td>" . $row['time'] . "</td>";
    echo "<td class='text-center' >";
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="issueId" value="' . $row['id'] . '">';
    echo '<select name="priority" required>';
	echo '<option selected="true" disabled="disabled" value="">Select Priority</option>';
    echo '<option value="Normal" ' . ($row['priority'] == 'Normal' ? 'selected' : '') . '>Normal</option>';
    echo '<option value="Medium" ' . ($row['priority'] == 'Medium' ? 'selected' : '') . '>Medium</option>';
    echo '<option value="High" ' . ($row['priority'] == 'High' ? 'selected' : '') . '>High</option>';
    echo '<option value="Urgent" ' . ($row['priority'] == 'Urgent' ? 'selected' : '') . '>Urgent</option>';
    echo '</select>';
    echo "</td>";
	
	
	echo "<td><center><input type='text' class='itname' name='itname' value='" . $row['itname'] . "' required></center></td>";
	
    echo '<td class="text-center">
            <button class="block-button" type="submit" name="confirmBtn"><i class="fa fa-check fa-lg" style="color:red"></i></button>
          </td>';
    echo '<td class="text-center">
            <button class="block-button" type="submit" name="rejectBtn"><i class="fa fa-close fa-lg" style="color:red"></i></button>
          </td>';
    echo '</form>';
    echo "</tr>";
    $i++;
}

if (isset($_POST['confirmBtn'])) {
    $issueId = $_POST['issueId'];
    $priority = $_POST['priority'];
    $itname = $_POST['itname'];            
    $currentDate = date('Y-m-d');

    $updateSql = "UPDATE it_issue SET status='Done', rslvDate='$currentDate', priority='$priority' ,itname='$itname' WHERE id='$issueId'";

    if (mysqli_query($con, $updateSql)) {
        echo '<script>alert("Your confirmation has been updated.");</script>';
        echo "<script>setTimeout(\"location.href = 'ITissueHO.php';\", 150);</script>";
        exit;
    } else {
        echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
    }
}

if (isset($_POST['rejectBtn'])) {
    $issueId = $_POST['issueId'];
  
    $itname = $_POST['itname'];            
    $currentDate = date('Y-m-d');

    $updateSql = "UPDATE it_issue SET status='Rejected', rslvDate='$currentDate',itname='$itname' WHERE id='$issueId'";

    if (mysqli_query($con, $updateSql)) {
        echo '<script>alert("Your confirmation has been updated.");</script>';
        echo "<script>setTimeout(\"location.href = 'ITissueHO.php';\", 150);</script>";
        exit;
    } else {
        echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
    }
}
									?>
								</tbody>
							</table>
						</div>	
						
						
						
						
						
						
						
						
						<div role="tabpanel" class="tab-pane fade" id="approved">
							<h4>Branch_Resolved</h4>
							<table id="example7" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<th>BranchId</th>
										<th>Branch Name</th>
										<th>Issue Type</th>
										<th>Priority level</th>
										<th>BM Contact</th>
										<th>Remarks</th>
										<th>Date</th>
										<th>Resolve Date</th>
										<th>Resolved By</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;

				if (isset($_GET['branchId'])) {
    $branchId = $_GET['branchId'];
    if ($branchId == 'All Branches') {
        $whereCondition = "status != 'Pending'";
    } else {
        $whereCondition = "branchId = '$branchId' AND status != 'Pending'";
    }
} else {
    $whereCondition = "rslvDate = '$date' AND status != 'Pending'";
}
									$query = mysqli_query($con, "SELECT branchId,branchName, issueType, priority, contact,date, remarks,itname, rslvDate, status FROM it_issue WHERE $whereCondition AND status != 'Pending' AND branchName!='HO'");

									while ($row = mysqli_fetch_assoc($query)) {
										echo "<tr";
										if ($row['status'] == 'Done') {
											echo ' style="background-color: #ffe6e6;"';
										}
										echo ">";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchId'] . "</td>";
										echo "<td>" . $row['branchName'] . "</td>";
										echo "<td>" . $row['issueType'] . "</td>";
										echo "<td>" . $row['priority'] . "</td>";
										echo "<td>" . $row['contact'] . "</td>";
										echo "<td>" . $row['remarks'] . "</td>";
										
										echo "<td>" . $row['date'] . "</td>";
										echo "<td>" . $row['rslvDate'] . "</td>";
										echo "<td>" . $row['itname'] . "</td>";
										echo "<td>";
										if ($row['status'] == 'Done') {
											echo "Branch Confirmation Pending";
										} elseif ($row['status'] == 'Cleared') {
											echo "Resolved";
										} else {
											echo $row['status'];
										}
										echo "</td>";
										echo "</tr>";
										$i++;
									}
									?>
								</tbody>
							</table>
						</div>
						
						
						<div role="tabpanel" class="tab-pane fade" id="vmapproved">
							<h4>VM_Resolved</h4>
							<table id="example8" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<th>EmployeeId</th>
										<th>User Name</th>
										<th>Issue Type</th>
										<th>Priority level</th>										
										<th>Remarks</th>
										<th>Date</th>
										<th>Resolve Date</th>
										<th>Resolved By</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 1;
									
			
				if (isset($_GET['branchId'])) {
    $branchId = $_GET['branchId'];
    if ($branchId == 'All Branches') {
        $whereCondition = "status != 'Pending'";
    } else {
        $whereCondition = "branchId = '$branchId' AND status != 'Pending'";
    }
} else {
    $whereCondition = "rslvDate = '$date' AND status != 'Pending'";
}
	

									$query = mysqli_query($con, "SELECT empId,username, issueType, priority, remarks,date,rslvDate,itname, status FROM it_issue WHERE $whereCondition AND status != 'Pending' AND branchName='HO'");

									while ($row = mysqli_fetch_assoc($query)) {
										echo "<tr";
										if ($row['status'] == 'Done') {
											echo ' style="background-color: #ffe6e6;"';
										}
										echo ">";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['empId'] . "</td>";
										echo "<td>" . $row['username'] . "</td>";
										echo "<td>" . $row['issueType'] . "</td>";
										echo "<td>" . $row['priority'] . "</td>";
										
										echo "<td>" . $row['remarks'] . "</td>";
										
										echo "<td>" . $row['date'] . "</td>";
										echo "<td>" . $row['rslvDate'] . "</td>";
										echo "<td>" . $row['itname'] . "</td>";
										echo "<td>";
										if ($row['status'] == 'Done') {
											echo "VM Confirmation Pending";
										} elseif ($row['status'] == 'Cleared') {
											echo "Resolved";
										} else {
											echo $row['status'];
										}
										echo "</td>";
										echo "</tr>";
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
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>