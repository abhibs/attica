<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
		
	if ($type == 'SocialMedia') {
		include("header.php");
		include("menuSocialMedia.php");
	}
include("dbConnection.php");
$date = date('Y-m-d');

if (isset($_POST["submit"])) {
   

    $filename = $_FILES["file"]["tmp_name"];

 
    $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

   
    if ($_FILES["file"]["size"] > 0 && strtolower($file_extension) === 'csv') {
        $file = fopen($filename, "r");
		
	

        while (($emapData = fgetcsv($file, 100000, ",")) !== FALSE) {
            $name = mysqli_real_escape_string($con, $emapData[0]);
            $mobile = mysqli_real_escape_string($con, $emapData[1]);
            $type = mysqli_real_escape_string($con, $emapData[2]);
            $state = mysqli_real_escape_string($con, $emapData[3]);
            $date = mysqli_real_escape_string($con, $emapData[4]);
            $time = date('h:i:s');
            $status = "Pending"; 
            $import_date = date("Ymd");
            $import_time = date('h:i:s');
            $remarks="";
            $followup="0000-00-00";
             if (!DateTime::createFromFormat('Y-m-d', $date)) {
                echo "<script>alert('Invalid Date Format: Please use the Y-m-d date format.');</script>";
                echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";
				exit;
            }
             // Validate mobile number length
            if (strlen($mobile) !== 10 || !ctype_digit($mobile)) {
    echo "<script>alert('Invalid Mobile Number: Mobile number should be exactly 10 digits and contain only numbers.');</script>";
    echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";
    exit;
}

// Validate name for special characters (allowing only letters and spaces)
if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
    echo "<script>alert('Invalid Name: Only letters and spaces are allowed in the name.');</script>";
    echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";
    exit;
}
            
           $sql = "INSERT into enquiry (`name`, `mobile`, `type`, `state`, `date`, `time`, `status`,`import_date`,`import_time`,`remarks`,`followup`) 
                    VALUES ('$name', '$mobile', '$type', '$state', '$date', '$time', '$status','$import_date','$import_time','$remarks','$followup')";
            
            $result = mysqli_query($con, $sql);

            if (!$result) {
                echo "<script> alert('Invalid File: Please Upload CSV File.');</script>";
                echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";
                exit(); 
            }
        }

        fclose($file);
        
        echo "<script>alert('File has been successfully Imported.');</script>";
        echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";
        exit(); 
        
        mysqli_close($con);
    } else {
        
        echo "<script>alert('Invalid File: Please Upload a CSV File.'); </script>";
        echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";        
        exit(); 
    }
}

if (isset($_POST['submitCust'])) {
	
	$name = $_POST['name'];
	$mobile = $_POST['mobile'];
	$type = $_POST['type'];
	$state = $_POST['state'];	
	$date = date("Ymd");
	$time = date('h:i:s');
	$import_date = date("Ymd");
	$import_time = date('h:i:s');
	$status = "Pending";
	$remarks="";
    $followup="0000-00-00";
	 $insertSql = "INSERT into enquiry (`name`, `mobile`, `type`, `state`, `date`, `time`, `status`,`import_date`,`import_time`,`remarks`,`followup`) 
                    VALUES ('$name', '$mobile', '$type', '$state', '$date', '$time', '$status','$import_date','$import_time','$remarks','$followup')";
	   
	 
	if (mysqli_query($con, $insertSql)) {
	        echo "<script>alert('New Cutomer Added')</script>";
			echo "<script>setTimeout(\"location.href = 'import.php';\",150);</script>";
			exit;
		} else {
			echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'import.php'\",150);</script>";
			exit;
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
		font-size: 18px;
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
		padding: 0.35em 1.4em;
		margin: 0 0.3em 0.3em 0;
		border-radius: 0.15em;
		box-sizing: border-box;
		text-decoration: none;
		font-size: 14px;
		font-family: 'Roboto', sans-serif;
		text-transform: uppercase;
		color: #fffafa;
		background-color: #123C69;
		box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
		text-align: center;
		position: relative;
		 font-weight: bold;
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

	.block-button {
		background-color: #123C69;
		color: green;
		border: none;
		padding: 5px 10px;
		cursor: pointer;
	}
	
  input[type="file"] {
        display: none;
    }

    .custom-upload-btn-label {
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

    .custom-upload-btn-label span {
        font-size: 14px;
        font-weight: bold;
    }
		.dlt-button {
    background-color: transparent;
	color:red;
    border: none;
    padding: 3px 8px;
    cursor: pointer;
}	
</style>

 <div id="wrapper">
        <div class="row content">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <div class="row" style="padding-right: 20px; padding-left: 20px;">
<div style="position: absolute; top: 20px; right: 6px;">
    <form action="" method="post" enctype="multipart/form-data" class="form-inline">
        <div class="form-group">
            <div style="display: flex; align-items: center;">
                <span id="selectedFileName" style="margin-right: 10px; font-weight: bold; font-size: 14px;"></span>
                <label for="file" class="custom-upload-btn-label">
                    <i style="color:#ffcf40; font-size: 14px;" class="fa fa-upload"></i>
                    <span>&nbsp;Import CSV File</span>
                    <input type="file" name="file" id="file" class="form-control-file" onchange="updateFileName()">
                </label>
            </div>
        </div>
        <button class="btn btn-success ml-2" type="submit" name="submit">
            <i style="color:#ffcf40; font-size: 14px;" class="fa fa-download"></i>&nbsp; Submit
        </button>
    </form>
</div>

<script>
    function updateFileName() {
        const input = document.getElementById('file');
        const fileName = input.files[0].name;
        document.getElementById('selectedFileName').innerText = fileName;
    }
</script>
                        </div>
                    </div>
					<br>
					<br>
					
					<div class="panel-body"
					style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
				<h3 class="text-success"><i class="fa_icon fa fa-user-plus"></i> Add Enquiry </h3>
					<form method="POST" action=""><br>
						<div class="col-lg-3">
							<p class="text-success"><b>Customer Name</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-user-circle"></span></span>
								<input type="text" name="name" placeholder="Customer Name" id="name" class="form-control" required>
							</div>
						</div>
						<div class="col-lg-3">
							<p class="text-success"><b>Mobile Number</b></p>
							<div class="input-group">
							<span class="input-group-addon"><span class="fa_icon fa fa-phone"></span></span>
								<input type="text" name="mobile" pattern="[0-9]{10}" required placeholder="Contact Number" maxlength="10" class="form-control" autocomplete="off">
							</div>
						</div>
						
						<div class="col-lg-2">
							<p class="text-success"><b>Type</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-check"></span></span>
								<select class="form-control" style="padding:0px 5px" name="type" id="type"
									required>
									<option selected="true" value="">SELECT TYPE</option>
									<option value="Sell Gold">Sell Gold</option>
									<option value="Buy Gold">Buy Gold</option>
									<option value="Release Pledged Gold">Release Pledged Gold</option>
								</select>
							</div>
						</div>
						<div class="col-lg-2">
							<p class="text-success"><b>State</b></p>
							<div class="input-group">
								<span class="input-group-addon"><span class="fa_icon fa fa-home"></span></span>
								<select class="form-control" style="padding:0px 5px" name="state" id="state"
									required>
									<option selected="true" value="">SELECT STATE</option>
									<option value="Karnataka">Karnataka</option>
									<option value="Tamilnadu">Tamilnadu</option>
									<option value="Pondicherry">Pondicherry</option>
									<option value="Andhra Pradesh">Andhra Pradesh</option>
									<option value="Telangana">Telangana</option>									
								</select>
							</div>
						</div>
						<div class="col-lg-2">
							<button type="submitCust" name="submitCust" id="submitCust" class="btn btn-success btn-block"
								style="margin-top:27px"><span class="fa_Icon fa fa-sign-in"> </span>&nbsp; ADD
								Enquiry</button>
						</div>
					</form>
				</div><br><br>
				<div class="panel-heading">
						<!--<legend> <i style="padding-top:15px" class="fa_Icon fa fa-user-circle"></i> Websit Enquiry Details </legend>-->
						<h3 class="text-success"><i style="color:#990000;"  class="fa_Icon fa fa-user-circle"></i> Website Leads </h3>
					</div>
                    <div class="panel-body" style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					
                        <table id="example2" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr class="theadRow">
                                    <th><span class="fa fa-sort-numeric-asc"></span></th>
                                    <th>Customer Name</th>
                                    <th>Contact number</th>
                                    <th>Type</th>
                                    <th>State</th>
                                    <th>Date</th>
                                    <th>Time</th>									
									<th class="text-center"><i class="fa fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php
$i = 1;
$sql = mysqli_query($con, "SELECT id, name, mobile, type, state, date, time 
                           FROM enquiry 
                           WHERE status='Pending' AND import_date='$date' 
                           ORDER BY import_date ASC");

while ($row = mysqli_fetch_assoc($sql)) {
    echo "<tr>";
    echo "<td>" . $i . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['mobile'] . "</td>";
    echo "<td>" . $row['type'] . "</td>";
    echo "<td>" . $row['state'] . "</td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td>" . $row['time'] . "</td>";
    echo '<td class="text-center">';
    echo '<form method="POST" action="">';
    echo '<input type="hidden" name="issueId" value="' . $row['id'] . '">';
    echo '<button class="dlt-button" type="submit" name="rejectBtn"><i class="fa fa-trash fa-lg" style="color:red;font-size:18px;"></i></button>';
    echo '</form>';
    echo '</td>';
    echo "</tr>";

    $i++;
}
if (isset($_POST['rejectBtn'])) {
    $issueId = $_POST['issueId'];

    $deleteSql = "DELETE FROM enquiry  WHERE id='$issueId'";

    if (mysqli_query($con, $deleteSql)) {
        echo '<script>alert("Customer Data Deleted");</script>';
        echo "<script>setTimeout(\"location.href = 'import.php';\", 150);</script>";
    } else {
        echo '<script>alert("Error updating status: ' . mysqli_error($con) . '");</script>';
    }
}
?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
    <?php include("footer.php"); ?>