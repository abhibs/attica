<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];	
	if ($type == 'Software') {
		include("header.php");
		include("menuSocialMedia.php");
	}elseif ($type == 'SocialMedia') {
		include("header.php");
		include("menuSocialMedia.php");
	}elseif ($type == 'Master') {
		include("header.php");
		include("menuSocialMedia.php");
	} 
	else {
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	if(isset($_GET["reg_type"])){
		
		if($_GET["reg_type"]==""){
			$reg_type="ALL";			
			$condition="";

		}elseif($_GET["reg_type"]=="ALL"){
			$reg_type="ALL";			
			$condition="";

		}else{
			$reg_type=$_GET["reg_type"];
			$condition=" AND reg_type='$reg_type'";

		}
		
	}else{
		$reg_type="ALL";
		$condition="";
	}
	
    $enq = mysqli_query($con,"SELECT X.*,Y.branchName FROM
							(SELECT A.*,B.quotation,B.extra FROM
							(SELECT id,name,mobile,gold,metal,issue,gwt,ramt,branchId,comment,remarks,status,time
							FROM walkin
							WHERE date='$date' AND branchId!='') A 
							INNER JOIN 
							(SELECT branch,contact,quotation,extra FROM everycustomer WHERE date='$date' AND branch!='' AND quotation!='' $condition ) B
							ON A.branchId=B.branch AND A.mobile=B.contact) X
							INNER JOIN
							(SELECT branchId,branchName FROM branch WHERE branchId!='') Y
							ON X.branchId=Y.branchId");	
?>
<link rel="stylesheet" href="vm/styles/vmStyle.css"/>
<style>
	#selectType{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}

</style>
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
							<select id="selectType" name="reg_type" onchange="fetch_er_link()">
								<option value="none" disabled>TYPE</option>
								<option value="ALL" <?php if($reg_type=="ALL"){ echo "selected"; } ?> >TOTAL</option>
								<option value="QR" <?php if($reg_type=="QR"){ echo "selected"; } ?> >Registered via QR</option>
								<option value="VM" <?php if($reg_type=="VM"){ echo "selected"; } ?> >Registered via VM</option>
								<option value="BM"<?php if($reg_type=="BM"){ echo "selected"; } ?> >Registered via BM</option>
							</select>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="eReport-datatable" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th>#</th>
									<th>Branch</th>
									<th>Name</th>
									<th>Contact</th>									
									<th>Type</th>
									<th>GrossW</th>
									<th>ReleaseA</th>
									<th>Branch Remarks</th>
									<th>Disposition</th>
									<th>HO Comment</th>
									<th width="10%">VM Comment</th>
									<th>Time</th>
									<th>Quotation</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									while($value = mysqli_fetch_assoc($enq)){
										echo "<tr><td>".$i."</td>";
										echo "<td>".$value['branchName']."</td>";
										echo "<td>".$value['name']."</td>";
										echo "<td>".$value['mobile']."</td>";										
										echo "<td>".$value['gold']."</td>";
										echo "<td>".$value['gwt']."</td>";
										echo "<td>".$value['ramt']."</td>";
										echo "<td>".$value['remarks']."</td>";
										echo "<td>".$value['issue']."</td>";
										echo "<td>".$value['comment']."</td>";
										
										$extra = json_decode($value['extra'],true);
										echo "<td>Gross W : ".$extra['GrossW']."<br>Net W : ".$extra['NetW']."<br>Release Amt : ".$extra['Rel-Amount']."</td>";
										
										echo "<td>".$value['time']."</td>";
										if($value['quotation'] != ''){
											$decoded = json_decode($value['quotation'],true);
											echo "<td class='text-center'><a target='_BLANK' href='QuotationImage/".$decoded['image']."'><button class='btn btn-circle' type='button'><i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69' ></i></button></a></td>";
										}
										else{
											echo "<td>SOLD</td>";
										}
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
<?php //include("footerNew.php"); ?>
<?php include("footer.php"); ?>
<script>

	function fetch_er_link() {
		var type=document.getElementById("selectType").value;
		
		window.location.replace('customer_eReport.php?reg_type='+type);	
	}

		
	$('#eReport-datatable').DataTable({
		responsive: true
	});
		

</script>	