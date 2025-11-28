<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'VM-HO') {
		include("headervc.php");
		include("menuvc.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	$empId = $_SESSION['employeeId'];
	$vmBranchList = mysqli_fetch_assoc(mysqli_query($con,"SELECT agentId,branch FROM vmagent WHERE agentId='$empId'"));
	$branches = explode(",", $vmBranchList['branch']);
	
	$enq = mysqli_query($con, "SELECT w.*, b.branchName
	FROM walkin w 
	LEFT JOIN branch b on w.branchId=b.branchId
	WHERE w.date='$date' AND w.issue!='Rejected' AND w.branchId IN ('$branches[0]','$branches[1]','$branches[2]','$branches[3]','$branches[4]') AND w.branchId!=''");
?>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading" >
					<div class="row">
						<div class="col-lg-10">
							<h3 class="text-success"><i class="fa_Icon fa fa-edit"></i> Enquiry Report</h3>
						</div>
						<div class="col-lg-2">
							<a href="searchWalkin.php">
								<button class="btn btn-success btn-block"> <span style="color:#ffcf40" class="fa fa-search"></span> Search Customer </button>
							</a>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Name</th>
 								    <!--<th>Contact</th> -->
									<th>Type</th>
									<th>Having</th>
									<th>GrossW</th>
									<th>ReleaseA</th>
									<th>Branch Remarks</th>
									<th>Disposition</th>
									<th>HO Comment</th>
									<th>Time</th>
									<th>Quot</th>
									<th>Gold Image</th>
									<th>To_Bill</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									while($value = mysqli_fetch_assoc($enq)){
										echo "<tr><td>".$i."</td>";
										echo "<td>".$value['branchName']."</td>";
										echo "<td>".$value['name']."</td>";
										// echo "<td>".$value['mobile']."</td>";
										echo "<td>".$value['gold']."</td>";
										echo "<td>".$value['havingG']."</td>";
										echo "<td>".$value['gwt']."</td>";
										echo "<td>".$value['ramt']."</td>";
										echo "<td>".$value['remarks']."</td>";
										echo "<td>".$value['issue']."</td>";
										echo "<td>".$value['comment']."</td>";
										echo "<td>".$value['time']."</td>";
										echo "<td class='text-center'><a target='_BLANK' href='../QuotationImage/".$value['quotation']."'><button class='btn btn-circle' type='button'><i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69' ></i></button></a></td>";
										if (!empty($value['enquiry_image'])) {
											echo "<td class='text-center'>
													<a target='_BLANK' href='../EnquiryImage/".rawurlencode($value['enquiry_image'])."'>
													<button class='btn btn-circle' type='button'>
														<i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69'></i>
													</button>
													</a>
												</td>";
										} else {
											echo "<td class='text-center'>No Image</td>";
										}
										echo "<td><button style='background-color: #123C69; color: #ffffff' onClick='sendToBilling(this)' data-mobile='".$value['mobile']."' data-branch='".$value['branchId']."'><i>to_bill</i></button></td>";
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
	<?php include("footerNew.php"); ?>
	<script>		
		function sendToBilling(button){
			
			const flag = confirm("Is this customer billing ?");
			if(flag){
				const mobile = button.dataset.mobile;
				const branchId = button.dataset.branch;
				var req = $.ajax({
					url: "zvmn.php",
					type: "POST",
					data: {
						mobile: mobile,
						branchId: branchId,
						customerBill: true
					}
				});
				req.done(function(e) {
					if(e == 'SUCCESS'){
						window.location.href = 'zbmhoHome1.php';
					}
					else if(e == 'ERROR'){
						alert("Error Occurred !!! Please Try Again");
					}
					else{
						console.log(e);
					}
				});
			}		
			
		}
	</script>		
