<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Asia/Calcutta');


include("header.php");

$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("menumaster.php");
}  else {
    include("logout.php");
}


include("dbConnection.php");

$branch = $_SESSION['branchCode'];
$branchId = $_SESSION['branchCode'];
$currentDate = date('Y-m-d');




if (isset($_GET['start_date'], $_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {

    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    $searchQuery = mysqli_query($con, "SELECT * FROM pledge_bill WHERE date BETWEEN '$start_date' AND '$end_date' AND status!='Rejected' $state ORDER BY id DESC");

    if (!$searchQuery) {
        die("Error in query: " . mysqli_error($con));
    }
} else {
    
    $searchQuery = mysqli_query($con, "SELECT * FROM pledge_bill WHERE status!='Rejected' ORDER BY id DESC");
}

$grossWTotal = 0;
$netWTotal = 0;
$amountTotal = 0;
$interestAmountTotal = 0;
$totalAmtToCollectTotal = 0;
$balanceAmtTotal = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pledge Bill</title>

</head>
<style> 
	.panel-heading input[type=text]{
		box-sizing: border-box;
		border: 2px solid #ccc;
		border-radius: 4px;
		font-size: 16px;
		background-color: white;
		/* background-image: url('images/searchicon.png'); */
		background-position: 220px 12px; 
		background-repeat: no-repeat;
		padding: 12px 50px 12px 15px;
		-webkit-transition: width 0.4s ease-in-out;
		transition: width 0.4s ease-in-out;
	}
	#search_branchId,#search_date{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}
	#wrapper{
		background: #f5f5f5;
	}	
	#wrapper h3{
		text-transform:uppercase;
		font-weight:600;
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
	.fa_Icon {
		color: #ffcf40;
	}
	.fa-Icon {
		color: #990000;
	}
	tbody{
	    font-weight: 600;
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
		border: 5px solid #fff;
		border-radius: 10px;
		padding: 20px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
		background-color: #f5f5f5;
		min-height: 300px;
	}	
	.preload{ 
		width:100px;
		height: 100px;
		position: fixed;
		top: 40%;
		left: 70%;
	}	
	.ajaxload{ 
		width:100px;
		height: 300px;
		position: fixed;
		top: 20%;
		left: 20%;
	}
	.buttons-csv,.btn-info{
	    font-size: 10px;
	}
	fieldset {
		margin-top: 1.5rem;
		margin-bottom: 1.5rem;
		border: none;
		box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
		border-radius:5px;
	}
	legend{
		margin-left:8px;
		width:450px;
		background-color: #123C69;
		padding: 5px 15px;
		line-height:30px;
		font-size: 14px;
		color: white;
		text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
		transform: translateX(-1.1rem);
		box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
		margin-bottom:0px;
		letter-spacing: 2px;
	}
	.card {
		position: relative;
		display: flex;
		flex-direction: column;
		min-width: 0;
		word-wrap: break-word;
		background-color: #fff;
		background-clip: border-box;
		border: 0 solid rgba(0,0,0,.125);
		border-radius: .25rem;
		box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
	}
	.card-body {
		flex: 1 1 auto;
		min-height: 1px;
		padding: 1rem;
	}
	h4, h6{
		font-weight: bold;
	}
	.form-control{
		height:25px;
	}

	@media only screen and (max-width: 600px) {
		
		legend{

			width:295px;
			font-size: 10px;

		}	
	
	}
</style>


<datalist id="branchList"> 
    <?php 
        $branches = mysqli_query($con,"SELECT branchId,branchName FROM branch where status=1");
        while($branchList = mysqli_fetch_array($branches)){
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>

    <div id="wrapper">
        <div class="content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-heading">
							<div class="col-lg-12">
                                <div class="col-lg-3">
                                    <h3 class="text-success">
										<i class="fa fa-cubes" aria-hidden="true" style="font-size:20px;color:#990000;"></i> Pledge Bills &nbsp; 
										<?php
											if ($start_date == $end_date) {
												echo date("(d-m-Y)", strtotime($currentDate));
											} else {
												echo " " . "(".date("d-m-Y", strtotime($start_date)) . " to " . date("d-m-Y", strtotime($end_date)).")";
											}
		
										?>
									</h3>
									<div class="col-md-9 text-right">
										<button class="btn data-sort" data-value="All" style="background-color: white;cursor: pointer; margin-right: 10px; border-radius:7px; box-shadow: 5px 5px 5px #999;" type="button" title="Display All Data"><i class="fa fa-snowflake-o"></i></br>All Data</button>
										<button class="btn data-sort" data-value="Pledged" style="background-color:white;cursor: pointer; margin-right: 10px; border-radius:7px; box-shadow: 5px 5px 5px #999;" type="button" title="Currently Active"><i class="fa fa-balance-scale"></i> <br/>Pledged</button>
										<button class="btn data-sort" data-value="Released"  style="background-color:#e6ffe6; margin-right: 10px;cursor: pointer; border-radius:7px; box-shadow: 5px 5px 5px #999;" type="button" title="Released Data"><i class="fa fa-check"></i> <br/>Released</button>
										<button class="btn data-sort" data-value="Sold" style="background-color:#ffcccc; margin-right: 10px;cursor: pointer; border-radius:7px; box-shadow: 5px 5px 5px #999;" type="button" title="Sale Gold Data"><i class="fa fa-recycle"></i> <br/>Sold</button>
									</div>
									
										
								</div>
								<div class="col-sm-3">
									<div class="input-group" style="margin-right:10px;">
										<input type="hidden" name="newUser" id="newUser" value="addUser">
										<?php
											if ($type == 'Master' && $type !== 'Zonal') {
												// Show the button only when $type is 'Master' and not 'Zonal'
												echo '<span class="input-group-btn">
														<button onclick="clickButton()" class="btn btn-success btn-md" style="height: 50px; margin-left:130px;">+ADD NEW PLEDGE</button>
													</span>';
											}
										?>
									</div>
								</div><hr>
								<div class="row">
									
									<form action="" method="GET">

										<div class="col-lg-1">
											<div class="input-group"style="margin-left:-10px;"><span class="input-group-addon"><span style="color:#990000;" class="fa fa-calendar"></span></span>
												<input type="date" class="form-control" name="start_date" id="start_date"style="height:55px;">
												
												<span class="input-group-addon" style="border:none; background:none;">To</span>
												
												<input type="date" class="form-control" name="end_date" id="end_date" style="height:55px;">
												<span class="input-group-btn">
													<button onclick="searchByDateRange()" class="btn btn-success btn-md" style="height:54px;"><i class="fa fa-search"></i></button>
												</span>
											</div>
										</div>

									</form>	
									<div class="col-lg-2" style="margin-left:300px;margin-top:-2px;">
									<?php
										if ($type == 'Master') {
											// Show the form only when $type is 'Master'
											echo '<form action="export.php" enctype="multipart/form-data" method="post">
													<input type="hidden" name="from" value="' . (isset($_GET['start_date']) ? $_GET['start_date'] : $start_date) . '">
													<input type="hidden" name="to" value="' . (isset($_GET['end_date']) ? $_GET['end_date'] : $end_date) . '">
													<button type="submit" name="exportPledge" class="btn btn-success btn-block" value="Export Excel" required style="width:150px; height:50px;">
														<span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel
													</button>
												</form>';
										}
										?>
									</div>
                            </div>
                        </div>


                        <div class="container2" style="display: block;">
                            <div class="col-lg-12">
                                <div class="panel-body" style="box-shadow:10px 15px 15px #999; ">

                                    <div class="table-responsive">
                                        <table id="example5" class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr style="background-color:#123C69; color:white;">
                                                    <th>#</th>
													<th>Date</th>
													<th>BillNumber</th>
													<th>BranchName</th>
                                                    <th>CustomerName</th>
                                                    <th>MobileNo</th>
                                                    <th>GrossW</th>
                                                    <th>NetW</th>
                                                    <th>Amount</th>
                                                    <th>Interest</th>
                                                    <th>InterestAmount</th>
													<th>MonthCount</th>
													<th>Actual_Interest</th>
													<th>Total_Amt_to be_collect</th>
													<th>Balance_Amt</th>
													<th>EmpID</th>
                                                    <th>EmpName</th>
													<th>RemainingDays</th>
													<th>Status</th>
													<th>Release_Date</th>
													<th>Release_EmpID</th>
													<th>Release_EmpName</th>
                                                    <th>Bill</th>
                                                    <th>DOC1</th>
													<th>DOC2</th>

                                                </tr>
                                            </thead>
											
											<?php
												if ($searchQuery && $count = mysqli_num_rows($searchQuery)) {
													$i = 1;
													while ($res = mysqli_fetch_array($searchQuery)) {
														$netW = $res['grossW'] - $res['stoneW'];
														$branchId = $res['branchId'];
														$branchNameQuery = mysqli_query($con, "SELECT branchName FROM branch WHERE branchId = '$branchId'");
														$branchNameRow = mysqli_fetch_assoc($branchNameQuery);
														$branchName = $branchNameRow["branchName"];

												// 		$invoiceNumber = $res['invoiceId'];
												// 		$rowColor = (strpos($invoiceNumber, 'HO') === 0) ? 'background-color: #ffeeba;' : '';
												        $billId = $res['billId'];
                                                        $rowColor = '';
                                                        if ($res['status'] == 'Released') {
                                                            // If status is 'Released', set row color to light green
                                                            $rowColor = 'background-color: #e6ffe6;';
                                                        } elseif ($res['status'] == 'Salegold') {
                                                            // If status is 'Salegold', set row color to light red
                                                            $rowColor = 'background-color: #ffcccc;';
                                                        }

														$date = $res['date'];
														$currentDate = date('Y-m-d');
														$daysDifference = floor((strtotime($currentDate) - strtotime($date)) / (60 * 60 * 24));

														// paid interest
														$billId = $res['billId'];
														$paidInterestQuery = mysqli_query($con, "SELECT SUM(paidAmount) AS totalPaidAmount FROM pledge_fund WHERE billId='$billId'");
														if ($paidInterestQuery) {
															$interestRow = mysqli_fetch_assoc($paidInterestQuery);
															$paidAmount = $interestRow['totalPaidAmount'];
														} else {
															$paidAmount = 0;
														}

														// Calculate balance
														$amount = $res['amount'];
														$balance = ($amount + $threeMonth) - $paidAmount;

														echo "<tr style='$rowColor'>";
														echo "<td>$i</td>";
														echo "<td>" . $res['date'] . "</td>";
														echo "<td>" . $res['billId'] . "</td>";
														echo "<td>" . $branchName . "</td>";
														echo "<td>" . $res['name'] . "</td>";
														echo "<td>" . $res['contact'] . "</td>";
														echo "<td>" . $res['grossW'] . "</td>";
														echo "<td class='text-center'>" . $netW . "</td>";
														echo "<td>" . $res['amount'] . "</td>";
														echo "<td>" . $res['rate'] . "</td>";
														echo "<td>" . $res['rateAmount'] . "</td>";

														// actual month
														if ($daysDifference < 30) {
															$factor = "1";
															echo "<td>" . $factor  . "</td>";
														} else {
															$factor = floor($daysDifference / 30) + 1;
															echo "<td>" . $factor . "</td>";
														}
														
														// Calculate actual interest

                                                     if ($daysDifference < 30) {
                                                             $actualInterest = $res['rateAmount'];
                                                     } elseif ($daysDifference >= 31 && $daysDifference <= 60) {
                                                             $actualInterest = (2 * $res['rateAmount']);
                                                     } elseif ($daysDifference >= 61 && $daysDifference <= 90) {
                                                             $actualInterest = (3 * $res['rateAmount']);
                                                     } elseif ($daysDifference >= 91 && $daysDifference <= 120) {
                                                             $actualInterest = (3 * $res['rateAmount']) + ($amount * 0.03);
                                                     } elseif ($daysDifference >= 121 && $daysDifference <= 150) {
                                                             $actualInterest = $res['rateAmount'] + ((3 * $res['rateAmount']) + ($amount * 0.03));
                                                     } elseif ($daysDifference >= 151 && $daysDifference <= 180) {
                                                             $actualInterest = (2 * $res['rateAmount']) + ((3 * $res['rateAmount']) + ($amount * 0.03));
                                                     } else {
                                                             $actualInterest = 0;
                                                            }
                                                     
														
														echo "<td>" . $actualInterest . "</td>";
															
														// <th>Interest Amount Collect</th>
														if ($paidAmount == 0) {
															echo "<td class='text-center'>0</td>";
														} else {
															echo "<td class='text-center'>" . $paidAmount . "</td>";
														}
														// Calculate remainBalance based on actualInterest 
														
															$remainBalance = ($amount + $actualInterest) - $paidAmount;
															
															echo "<td>" . $remainBalance . "</td>";
														
														// employee name
														echo "<td>" . $res['empId'] . "</td>";
														echo "<td>" . $res['empName'] . "</td>";
														

														// remaining Days
														if ($res['status'] == 'Released' OR $res['status'] == 'Salegold') {
															// If status is 'Released', set remainDays to 0
															$remainDays = 0;
														} else {
															if ($daysDifference <= 90) {
																$remainDays = max(0, 90 - $daysDifference);
															} else {
																$remainDays = ($daysDifference - 90);
															}
														}
														echo "<td>" . $remainDays . "</td>";

														// status
														//echo "<td>" . $res['status'] . "</td>";
														
															if ($res['status'] == 'Billed') {
															echo "<td class='text-center'>Pledged</td>";
														} else 
															if ($res['status'] == 'Released') {
															echo "<td class='text-center'>Released</td>";
														}else 
															if ($res['status'] == 'Salegold') {
															echo "<td class='text-center'>Sold</td>";
														}else 
															if ($res['status'] == '') {
															echo "<td class='text-center'></td>";
														}
														
														
														
														
														// release Date
															$releaseQuery = mysqli_query($con, "SELECT date FROM pledge_fund WHERE billId='$billId' AND (status='Released' OR status='Salegold') ");
															if ($releaseQuery && mysqli_num_rows($releaseQuery) > 0) {
																$reData = mysqli_fetch_assoc($releaseQuery);
																$releaseDate = $reData['date'];
																echo "<td>" . $releaseDate . "</td>"; 
															} else {
																echo "<td> </td>";
															}
															
														// Released gold Employee data
														
															$releaseEmp = mysqli_query($con, "SELECT empId FROM pledge_fund WHERE billId='$billId' AND (status='Released' OR status='Salegold')");
															if ($releaseEmp && mysqli_num_rows($releaseEmp) > 0) {
																$empData = mysqli_fetch_assoc($releaseEmp);
																
																$employeeID = $empData['empId'];
																echo "<td>" . $employeeID . "</td>";
																
																
															} else {
																echo "<td> </td>";
															}

															$releaseEmp = mysqli_query($con, "SELECT empName FROM pledge_fund WHERE billId='$billId' AND (status='Released' OR status='Salegold')");
															if ($releaseEmp && mysqli_num_rows($releaseEmp) > 0) {
																$empData = mysqli_fetch_assoc($releaseEmp);
																$employeeName = $empData['empName'];
															
																echo "<td>" . $employeeName . "</td>"; 
																
															} else {
																echo "<td> </td>";
															}
                                                            	

														echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='InvoicePledge.php?id=" . base64_encode($res['id']) . "'><i class='fa_Icon fa fa-edit'></i> </a></b></td>";

														if ($res['kyc1'] != "") {
															echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='PledgeDocuments/" . $res['kyc1'] . "'><i class='fa_Icon fa fa-eye'></i> Kyc2</a></td>";
														} else {
															echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> KYC1</a></td>";
														}
														if ($res['kyc2'] != "") {
															echo "<td class='text-center'><a target='_blank' class='btn btn-success btn-md' href='PledgeDocuments/" . $res['kyc2'] . "'><i class='fa_Icon fa fa-eye'></i> Kyc2</a></td>";
														} else {
															echo "<td class='text-center'><a class='btn'><i style='color:#ffcf40' class='fa fa-ban'></i> KYC2</a></td>";
														}
														echo "</tr>";
														$i++;
														
														if($status!='Rejected'){
														$grossWTotal += $res['grossW'];
														$netWTotal += $netW;
														$amountTotal += $res['amount'];
														$interestAmountTotal += $res['rateAmount'];
														$totalAmtToCollectTotal += $paidAmount;
														$balanceAmtTotal += $remainBalance;
														}else{
															echo "";
														}
													}
												} else {
													echo "<tr><td colspan='18'>No results found for the selected date.</td></tr>";
												}
											?>

                                            </tbody>
                                            <tfoot>
												<tr>
													<td colspan="6">Total:</td>
													<td><?php echo $grossWTotal; ?></td>
													<td><?php echo $netWTotal; ?></td>
													<td><?php echo $amountTotal; ?></td>
													<td><?php echo '' ?></td>
													<td><?php echo $interestAmountTotal; ?></td>
													<td><?php echo '' ?></td>
													<td><?php echo '' ?></td>
													<td><?php echo $totalAmtToCollectTotal; ?></td>
													<td><?php echo $balanceAmtTotal; ?></td>
													<td colspan="12"></td> <!-- Colspan to fill the remaining columns -->
												</tr>
											</tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12"></div>
        <div style="clear:both"></div>
        <?php include("footer.php"); ?>
    </div>




<script>
		
     
	function searchByDateRange() {
		var start_date = document.getElementById("start_date").value;
		var end_date = document.getElementById("end_date").value;
		window.location.href = "pledgeMaster.php?start_date=" + start_date + "&end_date=" + end_date;
    }
    
	function clickButton() {
		var selectedButton = document.getElementById("newUser").value;
		window.location.href = "pledgenewUser.php?newUser=" + selectedButton;
    }


</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const buttons = document.querySelectorAll(".data-sort");

    buttons.forEach(button => {
        button.addEventListener("click", function() {
            const value = this.getAttribute("data-value");
            filterRows(value);
        });
    });

    function filterRows(value) {
        const rows = document.querySelectorAll("#example5 tbody tr");

        rows.forEach(row => {
            const statusCell = row.querySelector("td:nth-child(19)"); // Adjust this index based on your 'Status' column

            if (statusCell) {
                const status = statusCell.textContent.trim();

                if (value === "All") {
                    row.style.display = "";
                } else if (value === "Pledged" && status === "Pledged") { // Change to match your actual status value
                    row.style.display = "";
                } else if (value === "Released" && status === "Released") {
                    row.style.display = "";
                } else if (value === "Sold" && status === "Sold") {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        });
    }
});

</script>


</body>

</html>

