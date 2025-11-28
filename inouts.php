<?php
session_start();
$type=$_SESSION['usertype'];
include("header.php");
include("menuassets.php");
$codes=$_GET['code'];
include("dbConnection.php");
$date=date('Y-m-d');
$time=date("l / d-M-Y");
?>
<div id="wrapper" style="top:39px">
	<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
				<a href="viewstock.php" style="float:left;" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-arrow-left"></i> Back</b></a>
<?php
			echo "<h3 align='right' class='text-success'><b><i class='fa fa-sort-numeric-asc'></i> View <u>$codes Stock</u> Dated <span style='color:#900'>$time</span></b></h3>";
?>
                <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
                  <table id="example2" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>

                        <th><i class="fa fa-sort-numeric-asc"></i></th>
                        <!-- th>Product</th>
                        < th>Product Name</th -->
                        <th>Sent/Received</th>
                       <th>Quantity</th> 
						<th>From/To</th>
						<th>Serial Number</th>
					   <th>Date</th>
					   					   
										</tr>
									</thead>
									<tbody>
									<?php
										$query1=mysqli_query($con,"SELECT * FROM inouts where code = '$codes' order by date desc");
										$count1=mysqli_num_rows($query1);
										for($i=1;$i<=$count1;$i++)
										{
											if($count1>0)
											{
											    
												$row1=mysqli_fetch_array($query1);
											    $branch = $row1['supplier'];
											    $sql=mysqli_query($con,"SELECT branchName FROM branch where branchId = '$branch'");
											    $row2=mysqli_fetch_array($sql);
												echo "<tr><td>" . $i .  "</td>";
												//echo "<td>" . $row1['code'] . "</td>";
												//echo "<td>" . $row1['name'] . "</td>";
												if($row1['status'] == 0)
												{
													$status = 'Received';
												}
												else
												{
													$status = 'Sent';
												}
												echo "<td><b>" . $status . "</b></td>";
												echo "<td>" . $row1['quantity'] . "</td>";
												if($row2['branchName'] != '')
												{
												    $supplier = "to ".$row2['branchName'];
												}
												else
												{
												    $supplier = "from ".$row1['supplier'];
												}
												echo "<td>" . $supplier . "</td>";
												echo "<td>" . $row1['deliveryNote'] . "</td>";
												echo "<td>" . $row1['date'] . "</td></tr>";
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
    <?php include("footer.php");?>