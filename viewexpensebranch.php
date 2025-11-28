<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
    include("header.php");
if ($type == 'Master') {
    include("menumaster.php");
}
include("dbConnection.php");
$date = date('Y-m-d');
$branchid = $_GET['branch'];
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                    <?php
                    $result219 = mysqli_query($con,"SELECT branchName from branch where branchId = '$branchid'");
                    $row219= mysqli_fetch_array($result219);
                    ?>
                <div style="clear:both"></div>
                <div class="hpanel">
                    <a style="float:right" class='btn btn-success' href='topexpense.php'>Go Back </a>
                    <h2><b><?php echo $row219['branchName'];?></b> Expense Details</h2>
                    <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
                        <table id="example3" class="table table-striped table-bordered">
                
                            <thead>
                                <tr class="text-success">
                                    <th><i class="fa fa-sort-numeric-asc"></i></th>
                                    <th>Type</th>
                                    <th>Particular</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                    <th>View Bill</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $query = mysqli_query($con, "select * from expense where date = '$date' and branchCode='$branchid' order by id DESC;");

                                $count = mysqli_num_rows($query);
                                for ($i = 1; $i <= $count; $i++) {
                                    if ($count > 0) {
                                        $row2 = mysqli_fetch_array($query);
                                        $row2['employeeId'];
                                        
                                        
                                    $result219 = mysqli_query($con,"SELECT branchName from branch where branchId = '".$row2['branchCode']."'");
                                    $row219= mysqli_fetch_array($result219);
                                    $result319 = mysqli_query($con,"SELECT employeeId from users where username = '".$row2['branchCode']."'");
                                    $row319= mysqli_fetch_array($result319);
                                    $result419 = mysqli_query($con,"SELECT name,contact from employee where empId = '".$row319['employeeId']."'");
                                    $row419= mysqli_fetch_array($result419);

                                        echo "<tr><td>" . $i .  "</td>";
                                        echo "<td><b>" . $row2['type'] . "</b></td>";
                                        echo "<td><b>" . $row2['particular'] . "</b></td>";
                                        echo "<td><b>" . $row2['amount'] . "</b></td>";
                                        echo "<td><b>" . $row2['time'] . "</b></td>";
                                        echo "<td><a class='btn btn-success'  target='_blank' href='ExpenseDocuments/". $row2['file'] . "'> View </a></td>";
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
    <div style="clear:both"></div>
    <?php include("footer.php"); ?>
</div>