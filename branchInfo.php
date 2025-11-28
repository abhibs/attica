<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	
	if(isset($_GET['branchId'])){
		$branchId = $_GET['branchId'];
		$indate = $_GET['date'];
		$branchName = mysqli_fetch_assoc(mysqli_query($con,"SELECT branchName FROM branch WHERE branchId='$branchId'"));
		$everyCustomer = mysqli_query($con,"SELECT customer,contact,status FROM everycustomer WHERE branch='$branchId' AND date='$indate'");
		$trans = mysqli_query($con,"SELECT id,name,phone,grossW,amountPaid,type,status,cashA,impsA FROM trans WHERE branchId='$branchId' AND date='$indate'");
		$release = mysqli_query($con,"SELECT releaseID,name,phone,amount,type,status,relCash,relIMPS FROM releasedata WHERE BranchId='$branchId' AND date='$indate'");
		$walkin = mysqli_query($con,"SELECT name,mobile,remarks,status FROM walkin WHERE branchId='$branchId' AND date='$indate'");
		$expense = mysqli_query($con,"SELECT particular,type,amount,status FROM expense WHERE branchCode='$branchId' AND date='$indate'");
		$fund = mysqli_query($con,"SELECT request,type,status,time FROM fund WHERE branch='$branchId' AND date='$indate'");
		//$transfer = mysqli_query($con,"SELECT transferAmount,branchTo,branchId,status,time FROM trare WHERE (branchId='$branchId' OR branchTo='$branchName[branchName]') AND date='$indate'");
		$transfer = mysqli_query($con,"SELECT tr.transferAmount,tr.branchTo,b.branchName as branchFrom,tr.status,tr.time FROM trare tr join branch b on b.branchId=tr.branchId WHERE (tr.branchId='$branchId' OR tr.branchTo='$branchName[branchName]') AND tr.date='$indate'");
        $pledge = mysqli_query($con, "SELECT id,invoiceId,name, contact, grossW,amount, status FROM pledge_bill WHERE branchId='$branchId' AND date='$indate'");

	}
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 15px;
	color:#123C69;
	}
	#wrapper .panel-body{
	box-shadow: 10px 15px 15px #999;
	background-color: #f5f5f5;
	border-radius: 3px;
	padding: 15px;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight: 600;
	}
	thead{
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
	.li-nava{
	font-weight:600;
	font-size: 1px;
	color:#123C69;
	}
	.nav-list{
	font-weight:600;
	font-size:14px;
	color:#123C69;
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
		
		<div class="row" class="element">
			<form action="" method="GET">
				<div class="col-lg-3 col-md-offset-5">
					<input type="date" name="date" required class="form-control" value="<?php echo $date; ?>">
				</div>
				<div class="col-lg-4">
					<div class="input-group">
						<input list="branchList" name="branchId" placeholder="SELECT BRANCH" required class="form-control">
						<span class="input-group-btn"> 
							<button type="submit" class="btn btn-success"><i class="fa fa-search" style="color:white"></i></button>
						</span>
					</div>
				</div>
			</form>
		</div>
		
		<hr style="margin-bottom:0px">
		
		<div class="row">
			<div class="col-lg-12">
				<div class="container element">
					<div class="navbar-header" style="padding-top:13px">
						<h3 class="text-success" >
							<span style="color:#990000;">
								<?php
								if(isset($_GET['branchId'])){
									echo $branchName['branchName'] ." - <span onclick='copy(this)'>". $_GET['branchId']."</span>";
								}
								else{
									echo "BRANCH DETAILS";
								}
								?>
							</span>
						</h3>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav navbar-right info-nav">
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "editEveryCustomer.php?branchId=".$_GET['branchId']; }else{ echo "editEveryCustomer.php";
							} ?>" target='_BLANK'><span class="nav-list">NEW</span></a></li>
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "viewBillDetails.php?branchId=".$_GET['branchId']; }else{ echo "viewBillDetails.php";
							} ?>" target='_BLANK'><span class="nav-list">BILLS</span></a></li>
							<li><a class="page-scroll" page-scroll href="searchReleasedata.php" target='_BLANK'><span class="nav-list">RELEASE</span></a></li>
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "editExpenseDetails.php?branchId=".$_GET['branchId']; }else{ echo "editExpenseDetails.php";
							} ?>" target='_BLANK'><span class="nav-list">EXPENSE</span></a></li>
							<li><a class="page-scroll" page-scroll href="searchFund.php" target='_BLANK'><span class="nav-list">FUND</span></a></li>
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "editTrare.php?branchId=".$_GET['branchId']; }else{ echo "editTrare.php";
							} ?>" target='_BLANK'><span class="nav-list">TRARE</span></a></li>
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "editClosing.php?branchId=".$_GET['branchId']; }else{ echo "editClosing.php";
							} ?>" target='_BLANK'><span class="nav-list">CLOSING</span></a></li>
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "dailyClosingR.php?bus=".$_GET['branchId']; }else{ echo "dailyClosingR.php";
							} ?>" target='_BLANK'><span class="nav-list">BALANCE</span></a></li>
							<li><a class="page-scroll" page-scroll href="<?php if(isset($_GET['branchId'])){ echo "searchGoldSendData.php?branchId=".$_GET['branchId']; }else{ echo "searchGoldSendData.php";
							} ?>" target='_BLANK'><span class="nav-list">GOLD SEND</span></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">EVERY CUSTOMER</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Name</th>
									<th>Phone</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								    
								    if(isset($_GET['branchId'])){
								    	while($row = mysqli_fetch_assoc($everyCustomer)){
										    echo "<tr>";
										    echo "<td>".$row['customer']."</td>";
										    echo "<td><a href='editCustomers.php?mobile=".$row['contact']."' target='_BLANK'>".$row['contact']."</a></td>";
										    echo "<td>".$row['status']."</td>";
										    echo "</tr>";
									    }
								    }
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-8">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">TRANSACTION</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Name</th>
									<th>Phone</th>
									<th>GrossW</th>
									<th>Amount Paid</th>
									<th>Type</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if(isset($_GET['branchId'])){
									while($row = mysqli_fetch_assoc($trans)){
										echo "<tr>";
										echo "<td>".$row['name']."</td>";
										echo "<td><a href='searchTrans.php?search=".$row['id']."' target='_BLANK'>".$row['phone']."</a></td>";
										echo "<td>".$row['grossW']."</td>";
										echo "<td>".$row['amountPaid']."<br>Cash : ".$row['cashA']."<br>Imps : ".$row['impsA']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">FUND</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Request</th>
									<th>Type</th>
									<th>Status</th>
									<th>Time</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if(isset($_GET['branchId'])){
									while($row = mysqli_fetch_assoc($fund)){
										echo "<tr>";
										echo "<td>".$row['request']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "<td>".$row['time']."</td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>		
			<div class="col-lg-8">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">RELEASE</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Name</th>
									<th>Phone</th>
									<th>Amount</th>
									<th>Type</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if(isset($_GET['branchId'])){
									while($row = mysqli_fetch_assoc($release)){
										echo "<tr>";
										echo "<td>".$row['name']."</td>";
										echo "<td><a href='searchReleasedata.php?releaseID=".$row['releaseID']."&date=",$date."&phone=".$row['phone']."' target='_BLANK'>".$row['phone']."</a></td>";
										echo "<td>".$row['amount']."<br>Cash : ".$row['relCash']."<br>Imps : ".$row['relIMPS']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">WALKIN</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Name</th>
									<th>Phone</th>
									<th>Remarks</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if(isset($_GET['branchId'])){
									while($row = mysqli_fetch_assoc($walkin)){
										echo "<tr>";
										echo "<td>".$row['name']."</td>";
										echo "<td>".$row['mobile']."</td>";
										echo "<td>".$row['remarks']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>			
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">EXPENSE</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Particular</th>
									<th>Type</th>
									<th>Amount</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if(isset($_GET['branchId'])){
									while($row = mysqli_fetch_assoc($expense)){
										echo "<tr>";
										echo "<td>".$row['particular']."</td>";
										echo "<td>".$row['type']."</td>";
										echo "<td>".$row['amount']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="hpanel">
					<div class="panel-heading">
						<span class="text-success">TRANSFER</span>
					</div>
					<div class="panel-body">
						<table cellpadding="1" cellspacing="1" class="table table-bordered">
							<thead>
								<tr>
									<th>Amount</th>
									<th>From</th>
									<th>To</th>
									<th>Time</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if(isset($_GET['branchId'])){
									while($row = mysqli_fetch_assoc($transfer)){
										echo "<tr>";
										echo "<td>".$row['transferAmount']."</td>";
										//echo "<td>".$row['branchId']."</td>";
										echo "<td>".$row['branchFrom']."</td>";
										echo "<td>".$row['branchTo']."</td>";
										echo "<td>".$row['time']."</td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-8">
                <div class="hpanel">
                    <div class="panel-heading">
                        <span class="text-success">PLEDGE</span>
                    </div>
                    <div class="panel-body">
                        <table cellpadding="1" cellspacing="1" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>GrossW</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                
                                if(isset($_GET['branchId'])){
                                    while ($row = mysqli_fetch_assoc($pledge)) {
										echo "<tr>";
										echo "<td>".$row['name']."</td>";
										echo "<td><a href='editPledge.php?invoiceId=".$row['invoiceId']."' target='_blank'>".$row['contact']."</a></td>"; 
										echo "<td>".$row['grossW']."</td>";
										echo "<td>".$row['amount']."</td>";
										echo "<td>".$row['status']."</td>";
										echo "</tr>";
									}
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
		</div>
		
	</div>
	<script>
		function copy(that){
			var inp =document.createElement('input');
			document.body.appendChild(inp)
			inp.value =that.textContent
			inp.select();
			document.execCommand('copy',false);
			inp.remove();
		}
	</script>
<?php include("footer.php");?>