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
                    <a style="float:right" class='btn btn-success' href='topgoldstock.php'>Go Back </a>
                    <h2><b><?php echo $row219['branchName'];?></b> Stock Gold Details</h2>
                    <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
                        <table id="example3" class="table table-striped table-bordered">
                
                            <thead>
                                <tr class="text-success">
                                    <th><i class="fa fa-sort-numeric-asc"></i></th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Gross W</th>
                                    <th>Net W</th>
                                    <th>Gross A</th>
                                    <th>Net A</th>
                                    <th>Date</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $query = mysqli_query($con, "select * from trans WHERE staDate='' AND status='Approved' AND metal='Gold' AND branchId = '$branchid' order by id ASC;");

                                $count = mysqli_num_rows($query);
                                for ($i = 1; $i <= $count; $i++) {
                                    if ($count > 0) {
                                        $row2 = mysqli_fetch_array($query);
                
                                    $result219 = mysqli_query($con,"SELECT branchName from branch where branchId = '".$row2['branchId']."'");
                                    $row219= mysqli_fetch_array($result219);
                                    $result319 = mysqli_query($con,"SELECT employeeId from users where username = '".$row2['branchId']."'");
                                    $row319= mysqli_fetch_array($result319);
                                    $result419 = mysqli_query($con,"SELECT name,contact from employee where empId = '".$row319['employeeId']."'");
                                    $row419= mysqli_fetch_array($result419);

                                        echo "<tr><td>" . $i .  "</td>";
                                        echo "<td><b>" . $row2['name'] . "</b></td>";
                                        echo "<td><b>" . $row2['phone'] . "</b></td>";
                                        echo "<td><b>" . $row2['grossW'] . "</b></td>";
                                        echo "<td><b>" . $row2['netW'] . "</b></td>";
                                        echo "<td><b>" . round($row2['grossA'],2) . "</b></td>";
                                        echo "<td><b>" . round($row2['netA'],2) . "</b></td>";
                                        echo "<td><b>" . $row2['date'] . "</b></td>";
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