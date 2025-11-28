<?php
session_start();
include("header.php");
if ($type == 'Master') {
    include("menumaster.php");
} else if ($type == 'VM-AD') {
    include("menuvmadd.php");
}else if ($type == 'Zonal') {
    include("menuZonal.php");
}
include("dbConnection.php");
$date = date('Y-m-d');

if ($_GET['empId']) {
    $empid = $_GET['empId'];
    $query1 = mysqli_query($con, "SELECT * FROM users where id = '$empid' order by id DESC;");
    $row1 = mysqli_fetch_array($query1);
}
?>
<style>
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
		color:#b8860b;
	}
	.row{
	    margin-left:0px;
	    margin-right:0px;
	}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
				<div class="col-sm-5">
					<h3 class="text-success"><i class="fa_Icon fa fa-dashboard"></i> Modify VM</h3>
				</div>
                <div class="col-sm-3">
                    <div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
                        <input list="cusId" class="form-control" name="bran" id="bran" required placeholder="Get Branch ID" />
                    </div>
                </div>
                <datalist id="cusId">
                    <?php
                    $sql = "select * from branch";
                    $res = mysqli_query($con, $sql);
                    while ($row = mysqli_fetch_array($res)) { ?>
                        <option value="<?php echo $row['branchId']; ?>">
                            <?php echo $row['branchName']; ?></option>
                    <?php } ?>
                </datalist>
                <div style="clear:both"></div>
                <br>
                <div class="hpanel" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #f5f5f5;">
                    <div class="panel-body" style="background-color: #f5f5f5;">
                        <a class="small-header-action" href="#">
                            <div class="clip-header" style="background:#ffcf40">
                                <i style="color:#990000" class="fa fa-arrow-up"></i>
                            </div>
                        </a>
                        <?php
                        if (isset($_GET['empId'])) {
                        ?>

                            <div class="form-group">
                                <form method="POST" class="form-horizontal" action="edit.php?vmId=<?php echo $row1['id'] ?>">
                                <?php
                            } else {
                                ?>
                                    <h3 class="text-success"><b><i style="color:#990000" class="fa fa-rupee"></i> Add Employee</b></h3>
                                    <hr>
                                    <div class="form-group">
                                        <form method="POST" class="form-horizontal" action="add.php">
                                        <?php
                                    }
                                        ?>
                                        <div class="col-lg-3">
                                            <p class="text-success"><b>VM Name</b></p>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-address-card"></span></span>
                                                <input type="text" name="name" class="form-control" required value="<?php if (isset($_GET['empId'])) {
                                                                                                                        $agent = $row1['employeeId'];

                                                                                                                        $qu23 = mysqli_query($con, "SELECT * FROM `employee` where empId = $agent");
                                                                                                                        $row23 = mysqli_fetch_array($qu23);
                                                                                                                        echo $row23['name'];
                                                                                                                    }  ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="text-success"><b>User ID</b></p>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-address-card"></span></span>
                                                <input type="text" name="empId" id="empId" class="form-control" required value="<?php if (isset($_GET['empId'])) {
                                                                                                                                    echo $row1['username'];
                                                                                                                                }  ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="text-success"><b>Password</b></p>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
                                                <input type="text" name="password" id="password" class="form-control" required value="<?php if (isset($_GET['empId'])) {
                                                                                                                                            echo $row1['password'];
                                                                                                                                        }  ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <p class="text-success"><b>Language</b></p>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
                                                <!-- <input type="text" class="form-control" name="number" id="number" value="<?php if (isset($_GET['empId'])) {
                                                                                                                                    echo $row1['language'];
                                                                                                                                }  ?>"> -->
                                                <select name="language" class="form-control" id="number">
                                                    <?php
                                                    if (isset($_GET['empId'])) {
                                                        $lan = $row1['language'];
                                                        $qu23 = mysqli_query($con, "SELECT * FROM `language` where id = $lan;");
                                                        $row23 = mysqli_fetch_array($qu23);
                                                        
                                                    }
                                                    ?>

                                                    <option selected="true" readonly value="<?php echo $row23['id'];  ?>"><?php echo $row23['language'];  ?></option>
                                                    <option value="1">Kannada</option>
                                                    <option value="2">Tamil</option>
                                                    <option value="3">Telugu</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <br>
                                            <div class="input-group">
                                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-sign-in"></span></span>
                                                <?php
                                                if (isset($_GET['empId'])) {
                                                ?>
                                                    <input type="submit" name="updatevm" id="updatevm" class="btn btn-success btn-block" value="Update">
                                                <?php
                                                } else {
                                                ?>
                                                    <input type="submit" name="submitvm" id="submitemp" class="btn btn-success btn-block">
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                    </div>
                </div>
                <?php
                if ($type == 'HR') {
                ?>
                    <div class="hpanel">
                        <div class="panel-heading">
                            <h3 class="text-success"> &nbsp; <i class="fa_Icon fa fa-edit"></i> View Daily Rate Report </h3>
                        </div>
                        <div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #f5f5f5;">
                            <div class="table-responsive">
                                <table id="example2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr class="theadRow">
                                            <th><span class="fa_Icon fa fa-sort-numeric-asc"></span></th>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Designation</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM employee order by id DESC;");
                                        $count = mysqli_num_rows($query);
                                        for ($i = 1; $i <= $count; $i++) {

                                            if ($count > 0) {
                                                $row = mysqli_fetch_array($query);
                                                echo "<tr><td>" . $i .  "</td>";
                                                echo "<td>" . $row['empId'] . "</td>";
                                                echo "<td>" . $row['name'] . "</td>";
                                                echo "<td>" . $row['contact'] . "</td>";
                                                echo "<td>" . $row['designation'] . "</td>";
                                                echo "<td><a class='btn btn-success' href='addEmployee.php?empId=" . $row['id'] . "'><i class='fa fa-edit'></i></a></td>";
                                                $name = $row['name'];
                                                echo "<td><a class='btn btn-danger' href='delete.php?emplId=" . $row['id'] . "' onClick=\"javascript: return confirm('Press Ok to delete Employee: " . $name . "!');\"><i class='fa fa-times'></i></a></td></tr>";
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>

        </div>
        <!-- Right sidebar -->

        <?php include("footer.php"); ?>

        <!-- Vendor scripts -->
    </div>