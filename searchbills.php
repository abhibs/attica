<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if ($type == 'Master') {
		include("header.php");
		include("menumaster.php");
	}
	else if($type == 'AccHead'){
		include("header.php");
		include("menuaccHeadPage.php");
	}
	else if ($type == 'Zonal') {
		include("header.php");
		include("menuZonal.php");
	} 
	else if ($type == 'SubZonal') {
                include("header.php");
                include("menuSubZonal.php");
	}
	else if($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else {
		include("logout.php");
	}
	include("dbConnection.php");
?>
<style>
    #wrapper h3 {
	text-transform: uppercase;
	font-weight: 600;
	font-size: 20px;
	color: #123C69;
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
    .btn-success {
	display: inline-block;
	padding: 0.7em 1.4em;
	margin: 0 0.3em 0.3em 0;
	border-radius: 0.15em;
	box-sizing: border-box;
	text-decoration: none;
	font-size: 10px;
	font-family: 'Roboto', sans-serif;
	text-transform: uppercase;
	color: #fffafa;
	background-color: #123C69;
	box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
	text-align: center;
	position: relative;
    }
    .fa_Icon {
	color: #990000;
    }
    .fa_icon {
	color: #990000;
    }
	.hpanel .panel-body {
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
	background-color: #f5f5f5;
	}
	.table-responsive .row{
	margin: 0px;
	}
</style>
<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
				<div class="panel-heading" >
					<h3><i class="fa_Icon fa fa-edit"></i> View Transactions <span class="fa_Icon"><?php if(isset($_POST['search'])){ echo "- ".$_POST['search'];} ?></span></h3>
				</div>
				
                <div class="panel-body">
                    <div class="table-responsive">
                        <?php if ($type == 'Zonal') {?>
                            <table id="" class="table table-bordered">
                                <thead>
                                    <tr class="theadRow">
                                        <th>Customer Name</th>
                                        <th>Branch</th>
                                        <th>Date</th>
									</tr>
								</thead>
                                <tbody>
                                    <?php
										$query = mysqli_query($con,"SELECT t.name,b.branchName,t.date 
										FROM trans t, branch b
										WHERE t.phone='$_POST[search]' AND t.branchId=b.branchId AND t.status='Approved'");
										$numOfRows = mysqli_num_rows($query);
										if($numOfRows > 0){
											while ($row = mysqli_fetch_assoc($query)) {
												echo "<tr>";
												echo "<td>" . $row['name'] . "</td>";
												echo "<td>" . $row['branchName'] . "</td>";
												echo "<td>" . $row['date'] . "</td>";
												echo "</tr>";
											}
										}
										else{
											echo "<tr>";
											echo "<td class='text-center font-bold' colspan=3>NO BILLS</td>";
											echo "</tr>";
										}
									?>
								</tbody>
							</table>
							<?php } else { ?>
                            <table id="example5" class="table table-bordered">
                                <thead>
                                    <tr class="theadRow">
                                        <th>#</th>
                                        <th>Branch</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Gross Weight</th>
                                        <th>Net Weight</th>
                                        <th>Gross Amount</th>
                                        <th>Net Amount</th>
                                        <th>Date</th>
                                       <!-- <th>Details</th>
                                        <th>Bill</th>-->
									</tr>
								</thead>
                                <tbody>
                                    <?php
										$i = 1;
										$contact = $_POST['search'];
										$query = mysqli_query($con, "SELECT t.id, t.name, t.grossW, t.netW, t.grossA, t.netA, t.date, t.time, b.branchName 
										FROM trans t,branch b
										WHERE t.phone = '$contact' AND t.branchId = b.branchId AND t.status = 'Approved'
										ORDER BY t.id DESC");
										while ($row = mysqli_fetch_assoc($query)) {
											echo "<tr>";
											echo "<td>" . $i .  "</td>";
											echo "<td>" . $row['branchName'] . "</td>";
											echo "<td>" . $row['name'] . "</td>";
											echo "<td>" . $contact . "</td>";
											echo "<td>" . $row['grossW'] . "</td>";
											echo "<td>" . $row['netW'] . "</td>";
											echo "<td>" . $row['grossA'] . "</td>";
											echo "<td>" . $row['netA'] . "</td>";
											echo "<td>" . $row['date'] . "<br>" . $row['time'] . "</td>";
											//echo "<td><a class='btn btn-success' target='_blank' href='xviewCustomerDetails.php?id=" . $contact . "&ids=" . $row['id'] . "'><i style='color:#ffcf40' class='fa fa-check'></i> View</a></b></td>";
											//echo "<td><a class='btn btn-success' target='_blank' href='Invoice.php?id=".base64_encode($row['id'])."'><i style='color:#ffcf40' class='fa fa-eye'></i>  View</a></td>";
											echo "</tr>";
											$i++;
										}
									?>
								</tbody>
							</table>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php include("footer.php"); ?>
