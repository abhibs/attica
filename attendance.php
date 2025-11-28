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
	   include("dbConnection.php");
	   

	   ?>
	   
	   <div id="wrapper">
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <h3 class="text-success"><b><i style="color:#900" class="fa fa-money"></i> Funds Raised today</b> <button style="float:right" onclick="window.location.reload();" class="btn btn-success"><b><i style="color:#ffcf40" class="fa fa-spinner"></i> Reload</b></button></h3>
                </div>
                <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
				<form id="frm1" action=""  method="GET" onSubmit="return validate();">
                <table id="example2" class="table table-striped table-bordered table-hover">
                <thead>
                <tr class="text-success">
                    <th><i style="color:#990000" class="fa fa-sort-numeric-asc"></i></th>
					<th>Emp_Id</th>
                    <th>Name</th>
					<th>Branch</th>
					<th>Time</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php
								 
								 $date=date('Y-m-d');
								 $branchName=$_SESSION['branchCode'];
								 $sql="select branchName from branch where branchId='$branchName'";
											$res=mysqli_query($con,$sql);
											$row1=mysqli_fetch_array($res);
											$branch=$row1['branchName'];
							$query=mysqli_query($con,"SELECT DISTINCT empId, name, branch, time, date FROM attendance where date='$date' and branch='$branch';");
					   $count=mysqli_num_rows($query);
					    for($i=1;$i<=$count;$i++)
											{
					  
										if($count>0)
										{
											$row=mysqli_fetch_array($query);
												echo "<tr>";
												echo "<td>" . $i .  "</td>";												
												//echo "<td>" . $row['employeeId'] .  "</td>";
												echo "<td>" . $row['empId'] . "</td>";
												echo "<td>" . $row['name'] . "</td>";
												echo "<td>" . $row['branch'] . "</td>";
												echo "<td>" . $row['time'] . "</td>";
												echo "<td>" . $row['date'] . "</td>";
										        echo "</tr>";
											}
										}
				 
					 ?>
					 </tbody>				
                
                </table>
				
				
</form>
                </div>
            </div>
        </div>

    </div>

   <?php include("footer.php");?> </div>