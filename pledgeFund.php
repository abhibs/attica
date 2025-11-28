<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Calcutta');

$type = $_SESSION['usertype'];

if ($type == 'Branch') {	
    include("header.php");
    include("menu.php");
} elseif ($type == 'Zonal') {
    include("header.php");
    include("menuZonal.php");
} else {
    include("logout.php");
}

include("dbConnection.php");
$currentDate = date('Y-m-d');


$branchId = $_SESSION['branchCode'];
$query = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchId FROM branch WHERE branchId='$branchId'"));
$employeeId = $_SESSION['employeeId'];
$employeeName = $_SESSION['employeeName'];
$invoiceNumber = $_SESSION['invoiceNumber'];
$sql = mysqli_fetch_assoc(mysqli_query($con, "SELECT empId, name FROM employee WHERE empId='$employeeId'"));

if (isset($_POST['submit'])) {
    $billId = $_POST['billId'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $paidamount = $_POST['paidamount'];
	$status=$_POST['status'];
	$branchId=$query['branchId'];
    $empId = $sql['empId'];
    $empName = $sql['name'];
    $date = date('Y-m-d');
    $time = date('H:i:s');

    $addQuery = mysqli_query($con, "INSERT INTO pledge_fund (billId, name, contact, paidamount,status,branchId, empId, empName, date, time) 
        VALUES ('$billId', '$name', '$contact', '$paidamount', '$status','$branchId','$empId', '$empName', '$date', '$time')");

    if ($addQuery) {
        echo "<script>alert(' Added')</script>";
        header("Location: pledge_fund.php");
    } else {
        echo "<script>alert('Error!! Cannot Be Added')</script>";
        header("Location: pledge_fund.php");
    }
}



if (isset($_POST['search_submit'])) {
    $search_id = $_POST['search_id'];

    $searchQuery = "SELECT * FROM pledge_fund WHERE invoiceId = '$search_id' OR billId = '$search_id' OR contact = '$search_id'  ORDER BY id DESC";
    $resultSearch = mysqli_query($con, $searchQuery);
}


?>


<style>
	#results img{
		width:100px;
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
	.quotation h3{
		color:#123C69;
		font-size: 18px!important;
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
	.btn-info{
		background-color:#123C69;
		border-color:#123C69;
		font-size:12px;
	}	
	.btn-info:hover, .btn-info:focus, .btn-info:active, .btn-info.active{
		background-color:#123C69;
		border-color:#123C69;
	}
	.fa_Icon{
		color:#ffa500;
	}
	thead {
		text-transform:uppercase;
		background-color:#123C69;

	}
	thead tr{
		color: #f2f2f2;
		font-size:12px;
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

	.modaldesign {
		float: right;
		cursor: pointer;
		padding: 5px;
		background:none;
		color: #f0f8ff;
		border-radius: 5px;
		margin: 15px;
		font-size: 20px;
	}
	#available{
		text-transform:uppercase;
	}
</style>

	
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"> <i style="color:#990000" class="fa fa-rupee"></i> PLEDGE FUND </h3>
				</div>
				<div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:10px;">
					<form method="POST" class="form-horizontal" action="">
						
					<div class="col-sm-3">
						<label class="text-success">Search Invoice Number or Bill Number</label>
					</div>
					<label class="col-sm-8"><br></label>
					<div class="col-sm-3">
						
						<div class="input-group">
							<span class="input-group-addon"><span style="color:#990000" class="fa fa-vcard"></span></span>
							<input type="text" class="form-control" id="search_id" name="search_id" placeholder="Search invoice or bill Number" required>
						</span>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="input-group">
						<span class="input-group-btn">
							<button type="button" class="btn btn-success" style="height:33px; width:50px; margin-right: 200px;"id="search_submit"><i class="fa fa-search"></i></button>
						</span>
						</div>
					</div>

				<label class="col-sm-12"><br><br></label>
						<div class="col-sm-3">
							<label class="text-success">Bill Number</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-credit-card-alt"></span></span>
								<input type="text" name="billId" id="billId" placeholder="BILL Number" readonly class="form-control" required value="">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">Customer Name</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
								<input type="text" name="name" id="name" readonly placeholder="Customer Name" class="form-control" required value="">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">Contact</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-phone"></span></span>
								<input type="text" name="contact" id="contact"  readonly class="form-control" placeholder="Mobile Number" required value="">
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">Given Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
								<input type="text" name="amount" id="amount"  readonly class="form-control"  required value="">
							</div>
						</div>
						<label class="col-sm-12"><br></label>
						<div class="col-sm-3">
							<label class="text-success">Interest Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-money"></span></span>
								<input type="text" name="rateAmount" id="rateAmount"  readonly class="form-control" value="">
							</div>
						</div>



						<div class="col-sm-3">
							<label class="text-success">Amount</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-rupee"></span></span>
								<input type="text" name="paidamount" id="paidamount"  required class="form-control" placeholder="Amount" >
							</div>
						</div>
						<div class="col-sm-3">
							<label class="text-success">status</label>
							<div class="input-group">
								<span class="input-group-addon"><span style="color:#990000" class="fa fa-file"></span></span>
								<select id="status" style="padding:0px 3px" name="status"class="form-control" >
									<option selected="true" disabled="disabled" value="">Status</option>
									<option Value="Interest Paid">Interest Paid</option>
									<option Value="Released">Released</option>
									<option Value="salegold">Sale Gold</option>

                                </select>
							</div>
						</div>
							<label class="col-sm-12"><br><br></label>
                            <br><br>
                            <div class="col-sm-14" style="text-align:center">
                                <button class="btn btn-success" name="submit" id="submit" style="margin-top:21px;"> Submit</button>
                            </div>
					</form>
				</div>
			</div>
			
			<div class="hpanel">
				<div class="panel-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:10px;">
					<table id="example5" class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Bill ID</th>
								<th>Customer name</th>
								<th>Contact</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Branch Id</th>
								<th>Employee ID</th>
								<th>Employee Name</th>
								<th>Date</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody>
						<?php

							if ($type == 'Branch' ) {
								$sqlQuery = "SELECT * FROM pledge_fund WHERE branchId = '$branchId' AND date = '$currentDate' ORDER BY id DESC";
							} elseif ($type == 'Zonal') {
								// Adjust this part of the code as per your requirements
								$sqlQuery = "SELECT * FROM pledge_fund WHERE date = '$currentDate'";
							} else {
								$sqlQuery = "SELECT * FROM pledge_fund WHERE date = '$currentDate'";
							}

							$resultAllRecords = mysqli_query($con, $sqlQuery); 

							$i = 1;
							while ($res = mysqli_fetch_array($resultAllRecords)) {
								echo "<tr>";
								echo "<td>$i</td>";
								echo "<td>" . $res['billId'] . "</td>";
								echo "<td>" . $res['name'] . "</td>";
								echo "<td>" . $res['contact'] . "</td>";
								echo "<td>" . $res['paidamount'] . "</td>";
								echo "<td>" . $res['status'] . "</td>";
								echo "<td>" . $res['branchId'] . "</td>";
								echo "<td>" . $res['empId'] . "</td>";
								echo "<td>" . $res['empName'] . "</td>";
								echo "<td>" . $res['date'] . "</td>";
								echo "<td>" . $res['time'] . "</td>";
								echo "</tr>";
								$i++;
							}
							?>

						</tbody>
					</table>
				</div>
			</div>


		</div>
		<div style="clear:both"></div>
	</div>
	<?php include("footer.php"); ?>

<script>
    $(document).ready(function () {
        $('#search_submit').click(function () {
            var searchId = $('#search_id').val();
            $.ajax({
                type: 'POST',
                url: 'get_customer_details.php',
                data: { searchId: searchId },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        alert(response.error);
                    } else {
                        // Update the form fields with the retrieved data
                        $('#billId').val(response.billId);
                        $('#name').val(response.name);
                        $('#contact').val(response.contact);
                        $('#amount').val(response.amount);
						$('#rateAmount').val(response.rateAmount);
                    }
                }
            });
        });
    });

</script>





