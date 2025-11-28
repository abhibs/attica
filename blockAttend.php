<?php
		session_start();
	$type=$_SESSION['usertype'];
	$username=$_SESSION['login_username'];
	if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	
	else if($type=='HR'){
		include("header.php");
		include("menuhr.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
		/*   BRANCH DATA   */
	$branchData = [];
	$branchQuery = mysqli_query($con, "SELECT branchId, branchName, city, state FROM branch WHERE status = 1");
	while($row = mysqli_fetch_assoc($branchQuery)){
		$branchData[$row['branchId']] = $row;
	}
	
	/*   ATTENDANCE DATA   */
	$attendanceQuery = '';
	$branchId = '';
	$from = '';
	$to = '';
	if(isset($_GET['getAttendance'])){
		$branchId = $_GET['branchId'];
		$from = $_GET['from'];
		$to = $_GET['to'];
		$state = '';
		switch($branchId){
			case "All Branches": $state=''; break;
			case "Bangalore"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Bengaluru')"; break;
			case "Karnataka"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Karnataka' AND city!='Bengaluru')"; break;
			case "Chennai"     : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Chennai')"; break;
			case "Tamilnadu"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Tamilnadu' AND city!='Chennai')"; break;
			case "Hyderabad"   : $state=" AND branchId IN (SELECT branchId FROM branch WHERE city='Hyderabad')"; break;
			case "AP-TS"       : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh') AND city!='Hyderabad')"; break;
			case "Pondicherry" : $state=" AND branchId IN (SELECT branchId FROM branch WHERE state='Pondicherry')"; break;
			default            : $state=" AND branchId='$branchId'"; break;
		}
	 $attendanceQuery = mysqli_query($con, "SELECT empId, name, branchId, branch, date, time, photo, lastlogin
        FROM attendance
        WHERE date BETWEEN '$from' AND '$to' " . $state . " and status=1
        ORDER BY empId, date DESC");
} else {
    $attendanceQuery = mysqli_query($con, "SELECT empId, name, branchId, branch, date, time, photo, lastlogin
        FROM attendance
        WHERE status=1
        ORDER BY empId, date DESC");
}
	
	$data = [];
	while($row = mysqli_fetch_assoc($attendanceQuery)){
		$data[$row['empId'].$row['date']][] = $row;
	}	
	


?>
<style>	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:700;
	font-size: 18px;
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
	padding:0.3em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 14px;
	font-family:'Roboto',sans-serif;
	
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	
	}
	 .unblock-button {
        background-color: transparent;
        border: none;
        color:green;
        font-size: 13px;
        cursor: pointer;
		font-weight: 700;
    }
	.fa_Icon {
	color: #990000;
	}
	.modal-title {
	font-size: 20px;
	font-weight: 600;
	color:#708090;
	text-transform:uppercase;
	}
	.modal-header{
	background: #123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	border: 1px solid #edf2f9;
	background-color: #f5f5f5;
	border-radius:3px;
	padding: 20px;
	}
	.td-align-right{
	text-align : right;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>

<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList"> 
	<option value="All Branches"> All Branches</option>
	<option value="Bangalore"> Bangalore</option>
	<option value="Karnataka"> Karnataka</option>
	<option value="Chennai"> Chennai</option>
	<option value="Tamilnadu"> Tamilnadu</option>
	<option value="Hyderabad"> Hyderabad</option>
	<option value="AP-TS"> AP-TS</option>
	<option value="Pondicherry"> Pondicherry</option>
	<?php 
		foreach($branchData as $row){
			echo "<option value='$row[branchId]' label='$row[branchName]'></option>";
		}
	?>
</datalist>
<div id="wrapper">
	<div class="row content">
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="col-sm-3">
					<h3 class="text-success">Blocked Attendance</span></h3>
					<small style='color: #990000'>(
						<?php 
							echo (isset($_GET['getAttendance'])) ? $_GET['from']." to ".$_GET['to'] : $date;
						?>
					)</small>
				</div>
				<form action="" method="GET" >
					<div class="col-sm-3">
						<input list="branchList" class="form-control" name="branchId"  placeholder="Branch Id" required/>
					</div>
					<div class="col-sm-4">
						<div class="input-group">
							<input type="date" class="form-control" name="from"/>
							<span class="input-group-addon">to</span>
							<input type="date" class="form-control" name="to"/>
						</div>
					</div>					
					<div class="col-sm-2" style="margin-top: 1px;">
						<button class="btn btn-success btn-block" name="getAttendance"><span class="fa_Icon fa fa-search"></span>  Search </button>
					</div>
				</form>				
			</div>
			<div style="clear:both"><br></div>
		
		
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-body">
					<div class="table-responsive">
						<table id="exampleBalance" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									 <th>#</th> 
									<th>Employee_Id</th>
									<th>Employee_Name</th>
									<th>Branch</th>
									<th>State</th>
									<th>Last_Login_Date</th>
									<th>Date</th>
									<th>Time</th>
									<th style='text-align: center;'>Status</th>		
								</tr>
							</thead>
							<tbody id="tableBody">
							<?php
                                  $i = 1;
        foreach ($data as $row) {
            $loginDate = strtotime($row[0]['lastlogin']);
            $date = strtotime($row[0]['date']);
            $employeeId = $row[0]['empId'];
            $currentDate = date('Y-m-d'); 

            $diff = ($row[0]['lastlogin'] == '0000-00-00' || empty($row[0]['lastlogin'])) ? 0 : floor(($date - $loginDate - 1) / (60 * 60 * 24));

            // Add a unique ID for the table row
            echo "<tr id='row_$employeeId_$row[0][date]'" . ($row[0]['date'] != $currentDate ? " style='background-color: #ffe6e6;'" : "") . ">";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $row[0]['empId'] . "</td>";
            echo "<td>" . $row[0]['name'] . "</td>";
            echo "<td>" . ($row[0]['branchId']=='AGPL089' ? 'Vijayawada-Bhavanipuram' : $row[0]['branch']) . "</td>";
            echo "<td>" . $branchData[$row[0]['branchId']]['state'] . "</td>";
            echo "<td>" . $row[0]['lastlogin'] . "<br><small> Difference: " . $diff . "</small></td>";
            echo "<td>" . $row[0]['date'] . "</td>";
            echo "<td>" . $row[0]['time'] . "</td>";
            echo "<td style='text-align: center; vertical-align: middle;'>";     
            echo "<form class='unblock-form'>";
            echo "<input type='hidden' name='unblock_empId' value='$employeeId'>";
            echo "<input type='hidden' name='unblock_date' value='{$row[0]['date']}'>";
            echo "<button type='submit' class='unblock-button' data-empid='$employeeId' data-date='{$row[0]['date']}'>Unblock</button>";
            echo "</form>";
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
	<script>

$(document).ready(() => {
    $('#exampleBalance').DataTable({
        paging: false,
        "ajax": '',
        dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
        "lengthMenu": [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, "All"]],
        buttons: [
            { extend: 'copy', className: 'btn-sm' },
            { extend: 'csv', title: 'ExportReport', className: 'btn-sm' },
            { extend: 'pdf', title: 'ExportReport', className: 'btn-sm' },
            { extend: 'print', className: 'btn-sm' }
        ]
    });
});

$(document).ready(function() {
    $('#table').DataTable();
});
</script>


<script>
$(document).ready(function() {
    // Handle unblock button click
    $(document).on('click', '.unblock-button', function(e) {
        e.preventDefault(); // Prevent form submission immediately

        var empId = $(this).data('empid');
        var date = $(this).data('date');
        var row = $(this).closest('tr'); // The row to be removed

        // Send AJAX request to unblock attendance
        $.ajax({
            url: 'unblock.php', // PHP script to handle unblocking
            type: 'POST',
            data: {
                unblock_empId: empId,
                unblock_date: date
            },
            success: function(response) {
                // Check if the unblock was successful
                if (response === 'success') {
                    // Remove the row from the table
                    row.remove();
                    alert('Attendance Unblocked');
                } else {
                    alert('Error unblocking attendance. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred. Please try again.');
            }
        });
    });
});

</script>


<?php include("footer.php"); ?>
