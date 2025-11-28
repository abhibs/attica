<?php
session_start();
   $type=$_SESSION['usertype'];
   include("header.php");
    if($type=='Master')
	   {
		    include("menumaster.php");
	   }
	   else if($type=='Admin')
	   {
		    include("menuadmin.php");
	   }
	   else if($type=='Branch')
	   {
		    include("menu.php");
	   }
	   else if($type=='Call Centre')
	   {
		    include("menuCall.php");
	   }
	  else if($type=='Accounts')
	{
	include("menuacc.php");
	}
	else if($type=='Zonal')
    {
        include("menuZonal.php");
    }
	   include("dbConnection.php");
	   
	   $date=date('Y-m-d');
	   $search="";
$search1="";
$search2="";
$price="";
if(isset($_GET['aaa']))
{
	$search=$_REQUEST['dat2'];
	$search1=$_REQUEST['dat3'];
	$search2=$_REQUEST['bran'];
	$branch=$search2;
$sql3="select * from branch where branchId='$branch'";
	   $res3=mysqli_query($con,$sql3);
	   $row3=mysqli_fetch_array($res3);
	   $branch1=$row3['branchId'];
	   $branchName=$row3['branchName'];
	   $_SESSION['b']=$branchName;
$_SESSION['d1']=$search1;
$_SESSION['d2']=$search;
	   $branch=$_SESSION['branchCode'];
	$sql13="select priceId from branch where branchId='$search2'";
$res13=mysqli_query($con,$sql13);
$row13=mysqli_fetch_array($res13);
$priceId=$row13['priceId'];
if($priceId==1 )
	{
		$price='Bangalore';
	}
	else if($priceId==2)
	{
		$price='Karnataka';
	}
	else if($priceId==3)
	{
    
		$price='Andhra Pradesh';
	
	}
	else if($priceId==4)
	{
    
		$price='Telangana';
	
	}
	else if($priceId==5)
	{
    
		$price='Pondicherry';
	
	}
	else if($priceId==6)
	{
    
		$price='Tamilnadu';
	
	}
	$_SESSION['se']=$search2;
	$_SESSION['one']=$search1;
	$_SESSION['two']=$search;

}

	   ?>
	   
	   <div id="wrapper">
<div class="content">
<form action="" method="GET" >
			<div>
				 <div class="col-sm-3">
				 <label class="text-success">Branch Id</label>
			<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
				 <input list="cusId"  class="form-control" name="bran" id="bran" placeholder="Branch Id" />  
				 </div>
				 </div>
				 <datalist id="cusId">
						
								<?php 
								 $sql="select * from branch";
	   $res=mysqli_query($con,$sql);
	   $row=mysqli_fetch_array($res);
								while($row = mysqli_fetch_array($res)) { 
								
											$branch=$row['branchId'];
											$sql="select branchName from branch where branchId='$branch'";
								?>
								
								   <option value="<?php echo $row['branchId']; ?>">
								   <?php echo $row['branchName']; ?></option>
                                    <?php } ?>
							
                                </datalist>
				 <div class="col-sm-3">
				<label class="text-success">From Date:</label> 
			<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
				<input type="date"  class="form-control" id="dat3" name="dat3" />
				</div></div>
				<div class="col-sm-3"> 
				 <label class="text-success">To Date:</label>
			<div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
				 <input type="date" class="form-control"  id="dat2" name="dat2" />
				 </div></div>
				 <div class="col-sm-1"> 
				 <label class="text-success">_________________</label>
				<button class="btn btn-success" name="aaa" id="aaa"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
				</form>
				</div>
				<div class="col-sm-2"> 
				 <label class="text-success">______________________</label>
				<form action="export.php" enctype="multipart/form-data" method="post">
	<button type="submit" name="exports" class="btn btn-primary" value="Export Excel" required><span style="color:#ffcf40" class="fa fa-edit"></span> Export Excel</button></form> 
				</div>
				</div>
				 
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <h3 class="text-success" align="center"><b><i style="color:#990000" class="fa fa-edit"></i> Transaction Report</b></h3>
                </div>
                <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
				<form method="GET" action="silverSendPdfMaster.php">
					 <table id="example2" class="table table-striped table-bordered table-hover">
                <thead>
                <tr class="text-success">
				<th><i style="color:#990000" class="fa fa-sort-numeric-asc"></i></th>
				<!--th>BranchId</th-->
					<th>Branch</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>GrossW</th>
                    <th>NetW</th>
					<th>GrossA</th>
                    <th>NetA</th>
                    <th>Trans_Date</th>
					<th>Move_to_HO</th>
                </tr>
                </thead>
                <tbody>
				<?php
				 $query=mysqli_query($con,"SELECT * FROM trans where date BETWEEN '$search1' AND '$search' AND metal='Silver' AND branchId='$search2';");
							//$row=mysqli_fetch_array($query);
					 
					 
					   $count=mysqli_num_rows($query);
					    for($i=1;$i<=$count;$i++)
											{
												 
										
											$row=mysqli_fetch_array($query);
											$sta=$row['sta'];
											$branch=$row['branchId'];
											$sql="select branchName from branch where branchId='$branch'";
											$res=mysqli_query($con,$sql);
											$row1=mysqli_fetch_array($res);
		
				
				echo "<tr><td>" . $i .  "</td>";
//												echo "<td>" . $row['branchId'] . "</td>";
												echo "<td>" . $row1['branchName'] . "</td>";
												echo "<td>" . $row['name'] . "</td>";
												echo "<td>" . $row['phone'] . "</td>";
												echo "<td>" . round($row['grossW'],2) . "</td>";
												echo "<td>" . round($row['netW'],2) . "</td>";
												echo "<td>" . round($row['grossA'],0) . "</td>";
												echo "<td>" . round($row['netA'],0) . "</td>";
												echo "<td>" . $row['date'] ."</td>";
												if($sta == 'Checked')
					  {
						  $st="Moved to HO";
						  
					  }
					  else if($sta != "Checked")
					  {
						  $st="Pending";
					  }
					  
												echo "<td><b style='color:#08347d'>".$st."</b></td></tr>";
					}
$sql="select SUM(netW) as nw,SUM(grossW) as gw , SUM(netA) as na,SUM(grossA) as ga, grossA, netW, date, branchId, COUNT(id) as id from trans where metal='Silver' AND branchId='$search2' and date BETWEEN '$search1' AND '$search'";
	   $res=mysqli_query($con,$sql);
	   $row=mysqli_fetch_array($res);
	   $sql20="select cash from gold where type='Silver' and city='$price' order by id DESC";
					   $res20=mysqli_query($con,$sql20);
					   $row20=mysqli_fetch_array($res20);
if(isset($_GET['aaa']))
{
    $pur=($row['ga']/$row['nw'])/$row20['cash']*100;
}
 ?>
					 
					 
					
					 </tbody>				
                <tfoot>
                <tr class="text-success">
				<th>SLNo</th>
				<th>Branch</th>
				<th>Gross Weight</th>
				<!--th>BranchId</th-->
					<th>Net Weight</th>
                    <th>Gross Amount</th>
                    <th>Net Amount</th>
                    <th>Average Purity</th>
					<th>Packets</th>
				<th>Date</th>
					<th>Silver Send Report</th>
                </tr>
				<tr>
				<td><?php echo 1 ;?></td>
				 <td><?php echo $row1['branchName'];?></td>
				 <td><?php echo round($row['gw'],2);?></td>
				 
				 <td><?php echo round($row['nw'],2);?></td>
				 
				 <td><?php echo round($row['ga'],0);?></th>
				 
				 <td><?php echo round($row['na'],2);?></td>
				<td class="text-success"><?php echo round($pur,2);?> %</td>
				<td class="text-success"><?php echo $row['id'];?></td>
				 <td><?php echo $row['date'];?></td>
				<td><input type="submit" class="btn btn-success" formtarget="_blank" id="delsilver" name="delsilver" value="View Report" ></td>
				</tr>
                </tfoot>
                </table>
				
				
</form>

                </div>
            </div>
        </div>

    </div>

   <?php include("footer.php");?> </div>