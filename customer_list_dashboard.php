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
	

	
    $qr = mysqli_query($con,"SELECT b.branchId,b.branchName, ec.* FROM everycustomer ec inner join branch b ON b.branchId=ec.branch where ec.date='$date' AND ec.reg_type='QR' order by Id desc");	
	$qr_rowCount= mysqli_num_rows($qr);
	
	$vm = mysqli_query($con,"SELECT b.branchId,b.branchName, ec.* FROM everycustomer ec inner join branch b ON b.branchId=ec.branch where ec.date='$date' AND ec.reg_type='VM' order by Id desc");	
	$vm_rowCount= mysqli_num_rows($vm);

	$bm = mysqli_query($con,"SELECT b.branchId,b.branchName, ec.* FROM everycustomer ec inner join branch b ON b.branchId=ec.branch where ec.date='$date' AND ec.reg_type='BM' order by Id desc");	
	$bm_rowCount= mysqli_num_rows($bm);
	

	
	if(isset($_GET["reg_type"])){
		
		if($_GET["reg_type"]=="" || $_GET["reg_type"]=="QR"){
			$sql=$qr;
			$title="CUSTOMER LIST REGISTERED VIA QR CODE";			

		}elseif($_GET["reg_type"]=="VM"){
			$sql=$vm;
			$title="CUSTOMER LIST REGISTERED VIA VM";
			
		}elseif($_GET["reg_type"]=="BM"){
			$sql=$bm;
			$title="CUSTOMER LIST REGISTERED VIA BM";
			
		}else{
			$sql=$qr;
			$title="CUSTOMER LIST REGISTERED VIA QR CODE";		
		}
		
	}else{
		$sql=$qr;
		$title="CUSTOMER LIST REGISTERED VIA QR CODE";		
	}	
	
	
	$qr_enq = mysqli_query($con,"SELECT X.*,Y.branchName FROM
								(SELECT A.*,B.quotation,B.extra FROM
								(SELECT id,name,mobile,gold,metal,issue,gwt,ramt,branchId,comment,remarks,status,time
								FROM walkin
								WHERE date='$date' AND branchId!='') A 
								INNER JOIN 
								(SELECT branch,contact,quotation,extra FROM everycustomer WHERE date='$date' AND branch!='' AND quotation!='' AND reg_type='QR' ) B
								ON A.branchId=B.branch AND A.mobile=B.contact) X
								INNER JOIN
								(SELECT branchId,branchName FROM branch WHERE branchId!='') Y
								ON X.branchId=Y.branchId");	
	$qr_enq_count= mysqli_num_rows($qr_enq);								
						
	$vm_enq = mysqli_query($con,"SELECT X.*,Y.branchName FROM
								(SELECT A.*,B.quotation,B.extra FROM
								(SELECT id,name,mobile,gold,metal,issue,gwt,ramt,branchId,comment,remarks,status,time
								FROM walkin
								WHERE date='$date' AND branchId!='') A 
								INNER JOIN 
								(SELECT branch,contact,quotation,extra FROM everycustomer WHERE date='$date' AND branch!='' AND quotation!='' AND reg_type='VM' ) B
								ON A.branchId=B.branch AND A.mobile=B.contact) X
								INNER JOIN
								(SELECT branchId,branchName FROM branch WHERE branchId!='') Y
								ON X.branchId=Y.branchId");	
	$vm_enq_count= mysqli_num_rows($vm_enq);	
					
	$bm_enq = mysqli_query($con,"SELECT X.*,Y.branchName FROM
								(SELECT A.*,B.quotation,B.extra FROM
								(SELECT id,name,mobile,gold,metal,issue,gwt,ramt,branchId,comment,remarks,status,time
								FROM walkin
								WHERE date='$date' AND branchId!='') A 
								INNER JOIN 
								(SELECT branch,contact,quotation,extra FROM everycustomer WHERE date='$date' AND branch!='' AND quotation!='' AND reg_type='BM' ) B
								ON A.branchId=B.branch AND A.mobile=B.contact) X
								INNER JOIN
								(SELECT branchId,branchName FROM branch WHERE branchId!='') Y
								ON X.branchId=Y.branchId");		
	$bm_enq_count= mysqli_num_rows($bm_enq);								
?>
<link rel="stylesheet" href="vm/styles/vmStyle.css"/>
<style>
	.box{
		padding:10px;
		transition:.2s all; 
	}
	.box-wrap:hover .box{
		transform: scale(.98);
		box-shadow:none;
	}
	.box-wrap:hover .box:hover{
	    filter:blur(0px);
		transform: scale(1.05);
		box-shadow:0 8px 20px 0px #b8860b;
	}	
	#wrapper .panel-body {
		box-shadow: none;	
	}	
	.hpanel {
		margin-bottom: 0px;
	}
</style>
<div id="wrapper">
	<div class="row content animate-panel">
		<div class="row m-t-md box-wrap">
			<div class="col-lg-4 box">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">REGISTRATION BY QR</h3>
							</div>
							<div class="stats-icon pull-right">
								<i style="color:#990000" class="fa fa-book fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<h5 class="stats-label">NO OF CUSTOMERS</h5>
									<h4><a href="customer_list_dashboard.php?reg_type=QR"><i style="color:#990000" class="fa fa-users"></i> <?php echo $qr_rowCount; ?> </a></h4>
								</div>
								<div class="col-xs-6">				
									<h5 class="stats-label"> NO OF ENQUIRY </h5>
									<h4> <b> <i style="color:#990000" class="fa fa-eye"></i> <a href="customer_eReport.php?reg_type=QR"> <?php echo $qr_enq_count;?> </a> </b></h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>

			<div class="col-lg-4 box">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">REGISTRATION BY VM</h3>
							</div>
							<div class="stats-icon pull-right">
								<i style="color:#990000" class="fa fa-book fa-2x"></i>
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<h5 class="stats-label">NO OF CUSTOMERS</h5>
									<h4><a href="customer_list_dashboard.php?reg_type=VM"><i style="color:#990000" class="fa fa-users"></i> <?php echo $vm_rowCount; ?></a></h4>
								</div>
								<div class="col-xs-6">				
									<h5 class="stats-label"> NO. OF ENQUIRY </h5>
									<h4> <b> <i style="color:#990000" class="fa fa-eye"></i> <a href="customer_eReport.php?reg_type=VM"> <?php echo $vm_enq_count;?> </a></b> </h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>



			<div class="col-lg-4 box">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="row">
							<div class="stats-title pull-left">
								<h3 class="font-extra-bold no-margins text-success">REGISTRATION BY BM</h3>
							</div>
							<div class="stats-icon pull-right">
								<i style="color:#990000" class="fa fa-book fa-2x"></i> 
							</div>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div class="col-xs-6">
									<h5 class="stats-label">NO OF CUSTOMERS:</h5>
									<h4><a href="customer_list_dashboard.php?reg_type=BM"><i style="color:#990000" class="fa fa-users"></i> <?php echo $bm_rowCount;?> </a></h4>
								</div>
								<div class="col-xs-6">				
									<h5 class="stats-label">NO. OF ENQUIRY</h5>
									<h4><b> <i style="color:#990000" class="fa fa-eye"></i> <a href="customer_eReport.php?reg_type=BM"> <?php echo $bm_enq_count;?> </a></b></h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b>
					</div>
				</div>
			</div>
			
		
		</div>
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading" >
					<div class="row">
						<div class="col-lg-10">
							<h3 class="text-success"><i class="fa_Icon fa fa-list"></i> <?php echo $title;?></h3>
						</div>
						<div class="col-lg-2">

						</div>
					</div>
				</div>
				<div class="panel-body animate-panel" style="margin-bottom:20px;">
					<div class="table-responsive">
						<table id="example5" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow" >
									<th>#</th>
									<th>Branch</th>
									<th>Name</th>
									<th>Contact</th>									
									<th>Accompanied By</th>									
									<th>Type</th>
									<th>Status</th>
									<th class='text-center'>Time</th>
									<th>Details</th>
									<th class='text-center'>Quotation</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									while($value = mysqli_fetch_assoc($sql)){
										echo "<tr><td>".$i."</td>";
										echo "<td>".$value['branchName']."</td>";
										echo "<td>".$value['customer']."</td>";
										echo "<td>".$value['contact']."</td>";										
										echo "<td>".$value['idnumber']."</td>";										
										echo "<td>".$value['type']."</td>";										
										echo "<td>".$value['status']."</td>";										
										echo "<td class='text-center'>".$value['time']."</td>";										
										$extra = json_decode($value['extra'],true);
										echo "<td>Gross W : ".$extra['GrossW']."<br>Net W : ".$extra['NetW']."<br>Release Amt : ".$extra['Rel-Amount']."</td>";
										if($value['quotation'] != ''){
											$decoded = json_decode($value['quotation'],true);
											echo "<td class='text-center'><a target='_BLANK' href='QuotationImage/".$decoded['image']."'><button class='btn btn-circle' type='button'><i class='fa fa-file-image-o' style='font-size:18px; font-weight:600; color:#123C69' ></i></button></a></td>";
										}
										else{
											echo "<td class='text-center'>SOLD</td>";
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