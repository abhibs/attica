<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Branch'){
		include("header.php");
		include("menu.php");
	}
	else{
		include("logout.php");
	}
    include("dbConnection.php");
	$date = date('Y-m-d');
	$branchId = $_SESSION['branchCode'];
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
		font-size: 12px;
		font-family:'Roboto',sans-serif;
		text-transform:uppercase;
		color:#fffafa;
		background-color:#123C69;
		box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
		text-align:center;
		position:relative;
	}
	.panel-box{
		margin-bottom: 20px;
		border: 4px solid #fff;
		border-radius:10px;
		padding: 20px;
		overflow: hidden;
		background: #990000;
		filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec');
		-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='#f5f5f5', EndColorStr='#f6f2ec')";
		-moz-box-shadow: 0 0 2px rgba(0, 0, 0, 0.35), 0 85px 180px 0 #fff, 0 12px 8px -5px rgba(0, 0, 0, 0.85);
		-webkit-box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 810px -68px #fff, 0 12px 8px -5px rgb(0 0 0 / 65%);
		box-shadow: 0 0 2px rgb(0 0 0 / 35%), 0 85px 180px 0 #fff, 0 12px 8px -5px rgb(0 0 0 / 85%);	
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="panel-heading">
			<h3><span style="color:#900" class="fa fa-users"></span> Daily Attendance <!--<a href="leave.php" style="float:right;" class="btn btn-success" disabled="true"><i style="color:#ffcf40" class="fa fa-info-circle"></i> LEAVE REQUEST</a>--></h3>
		</div>
		<div class="col-lg-3">
			<div class="hpanel">
				<div class="panel-body" style="box-shadow:10px 15px 15px #555;border-radius:10px;">
					<form method="POST" action="add.php">
						<input type="hidden" name="image" class="image-tag">
						<div class="row">
							<div class="col-md-12">
								<div id="results" style="position:absolute;opacity:0.5"></div><div id="my_camera" style="margin-left:auto;margin-right:auto"></div>
							</div>
							<div class="col-md-12" style="margin-top:15px;">
								<label class="text-success">Employee ID</label>
								<div class="input-group">
									<span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book"></span></span>
									<input type="text" class="form-control" name="empp" id="empp" placeholder="1000***" maxlength="7" title="enter 7 digit employee code: Ex: 1000600" pattern="[A-Z0-9]{7}" required autocomplete="off">
								</div>
								<span class="help-block m-b-none">Sample Employee ID[7 digit] : 1234567</span>
								<br>
							</div>
							<div class="col-md-12">
								<button onClick="take_snapshot()" name="submitAttend" id="submitAttend" class="btn btn-primary btn-block"><i class="fa fa-camera"></i> Mark Attendance</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-lg-9">
		    
			<div class="hpanel">
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<form id="frm1" action=""  method="GET" onSubmit="return validate();">
						<table id="example5" class="table table-striped table-bordered table-hover">
							<thead>
								<tr class="theadRow">
									<th>SNo</th>
									<th>Name</th>
									<th>Branch</th>
									<th>Time</th>
									<th>Employee</th>
								</tr>
							</thead>
							<tbody>
							<?php
$i = 1;
$sql = mysqli_query($con, "SELECT DISTINCT empId, name, branch, time, photo, date, status FROM attendance WHERE date='$date' AND branchId='$branchId'");
if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        echo "<tr>";
        echo "<td>" . $i . "</td>";
        
        if ($row['status'] == 1) {
            // Display a message for blocked users
			echo "<td>" . $row['name'] . " <br>" . $row['empId'] . "</td>";
            echo "<td colspan='3' class='text-center' style='color: red;'>Your EmployeeId is <b>blocked </b>. Please contact HR department.<br><small>( You are marked as absent )</small></td>";
        } else {
            // Display employee information for users with status 0
            echo "<td>" . $row['name'] . " <br>" . $row['empId'] . "</td>";
            echo "<td>" . $row['branch'] . "</td>";
            echo "<td>" . $row['date'] . "<br>" . $row['time'] . "</td>";
            echo "<td width='80px' style='margin:0px;padding:0px;text-align:center'><a target='blank' href='AttendanceImage/" . $row['photo'] . "'><img width='100%' src='AttendanceImage/" . $row['photo'] . "'></a></td>";
        }
        echo "</tr>";
        $i++;
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>NO DATA AVAILABLE IN TABLE.</td></tr>";
}
?>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
		<div style="clear:both"></div>
	</div>
	<script src="scripts/webcam.min.js"></script>
	<?php include("footer.php"); ?>
	<script language="JavaScript">
		Webcam.set
		({
			width: 210,
			height: 160,
			image_format: 'jpeg',
			jpeg_quality: 100
		});
		Webcam.attach( '#my_camera' );
		function take_snapshot()
		{
			Webcam.snap(
			function(data_uri)
			{
				$(".image-tag").val(data_uri);
				document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
			});
		}
	</script>
