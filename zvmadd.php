<?php
session_start();
$type = $_SESSION['usertype'];
    include("header.php");
if ($type == 'Master') {
    include("menumaster.php");
} else if ($type == 'VM-AD') {
    include("menuvmadd.php");
}else if($type == 'VM-WAIT') {
 	include("header.php");
    include("menuvmWait.php");
} 
else if ($type == 'Zonal') {
    include("menuZonal.php");
}
else if($type == 'SubZonal'){
	include("header.php");
        include("menuSubZonal.php");
}
else if($type == 'ZonalMaster') {
        include("header.php");
        include("menuzonalMaster.php");
}
else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
}
else{
       include("logout.php");
}

include("dbConnection.php");

if (isset($_POST['AddVM'])) {

    $username = $_POST['username'];
    $userid = $_POST['userid'];
    $password = $_POST['password'];
    $employeeId = $_POST['employeeId'];
    $branch = '';
    $ip = '';
    $date = date('Y-m-d');
    $language = $_POST['language'];

    $sql = "INSERT INTO `users` (`id`, `type`, `username`, `password`, `employeeId`, `branch`, `agent`, `date`, `ip`, `language`) VALUES (NULL, 'VM-HO', '$userid', '$password', '$employeeId', '', '', '$date', '', '$language')";
    mysqli_query($con, $sql);


    $sql1 = "INSERT INTO `employee` (`id`, `empId`, `name`, `contact`, `mailId`, `address`, `location`, `designation`, `photo`) VALUES (NULL, '$employeeId', '$username', '', '', '', '', 'VM', '')";
    mysqli_query($con, $sql1);

    $sql2 = "INSERT INTO `vmagent` (`id`, `agentId`, `branch`) VALUES (NULL, '$employeeId', '')";
    mysqli_query($con, $sql2);

    echo "<script type='text/javascript'>alert('Inserted in User Table is $employeeId ')</script>";
}
?>

<style>
    #results img {
        width: 100px;
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
        <div class="row-content">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <!--<div class="form-group row" style="padding-left:8px;padding-right:15px;">-->
                        <!--    <div class="col-xs-3" style="margin-top:20px;">-->
                        <!--        <div data-toggle="modal" data-target="#myModal" class='btn btn-success btn-user'>-->
                        <!--            <h4><b><i style="color:#990000" class="fa fa-user"></i> Add Fraud</b></h4>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                    </div>
                    <div class="hpanel">
                        <?php
                        $rm = mysqli_query($con, "SELECT username FROM `users` WHERE type='VM-HO' ORDER BY `users`.`id`  DESC LIMIT 1;");
						$emr = mysqli_fetch_array($rm);
						$emrp = $emr['username'];
                        ?>
                        <h3 class="text-success" style="float:right;">Last VM ID : <b class="text-dark"><?php echo $emrp ?></b></h3>
                        <h3 class="text-success" style="margin-bottom: 20px;"> <i class="fa_Icon fa fa-user"></i> Adding New Virtual Manager</h3>
						<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #f5f5f5;">
							<form method="POST" class="form-horizontal" action="zvmadd.php">
								<div class="form-group row" style="padding-left:50px;">
									<div class="col-sm-3">
										<label class="text-success">VM Name</label>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
											<input type="text" name="username" placeholder="Name" required class="form-control" autocomplete="off">
										</div>
									</div>
									<div class="col-sm-2">
										<label class="text-success">Employee ID</label>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
											<input type="text" name="employeeId" style="padding:0px 5px" placeholder="Employee ID" class="form-control" autocomplete="off">
										</div>
									</div>
									<div class="col-sm-2">
										<label class="text-success">Language</label>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
											<select name="language" class="form-control" id="number">
												<option selected="true" disabled="disabled">Select Language</option>
												<option value="1">Kannada</option>
												<option value="2">Tamil</option>
												<option value="3">Telugu</option>
											</select>
										</div>
									</div>
									<div class="col-sm-2">
										<label class="text-success">User ID</label>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
											<input type="text" name="userid" style="padding:0px 5px" required placeholder="Last is <?php echo $emrp  ?> " required class="form-control" autocomplete="off">
										</div>
									</div>
									<div class="col-sm-2">
										<label class="text-success">Password</label>
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
											<input type="text" name="password" style="padding:0px 5px" required placeholder=" Password " required class="form-control" autocomplete="off">
										</div>
									</div>
									<div class="col-sm-12"><br>
										<button class="btn btn-success" name="AddVM" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> Add New VM</button>
									</div>
								</div>
							</form>
						</div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <?php include("footer.php"); ?>
        <!-- <script src="scripts/webcam.min.js"></script> -->
    </div>
