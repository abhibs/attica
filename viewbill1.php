<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else if ($type == 'Expense Team') {
    include("header.php");
    include("menuexpense.php");
} else {
    include("logout.php");
}
include("dbConnection.php");



if (isset($_GET['aaa'])) {
    $search2 = $_REQUEST['bran'];
    $date = $_REQUEST['date'];
} else {
    $date = date('Y-m-d');
    $search2 = "";
}
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <form action="" method="GET">
                    <div class="col-sm-3">
                        <label class="text-success">Branch Id</label>
                        <div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-address-book-o"></span></span>
                            <input list="cusId" class="form-control" name="bran" id="bran" placeholder="Branch Id" />
                        </div>
                    </div>
                    <datalist id="cusId">
                        <option value="All Branches">All Branches</option>
                        <?php
                        $sql = "select * from branch";
                        $res = mysqli_query($con, $sql);
                        while ($row = mysqli_fetch_array($res)) { ?>
                            <option value="<?php echo $row['branchId']; ?>">
                                <?php echo $row['branchName']; ?></option>
                        <?php } ?>
                    </datalist>
                    <div class="col-sm-3">
                        <label class="text-success">Date:</label>
                        <div class="input-group"><span class="input-group-addon"><span style="color:#990000" class="fa fa-calendar"></span></span>
                            <input type="date" class="form-control" name="date" />
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <label class="text-success">_________________</label><br>
                        <button class="btn btn-success" name="aaa" id="aaa"><span style="color:#ffcf40" class="fa fa-search"></span> Search</button>
                    </div>
                </form>
                <div style="clear:both"></div>
                <div class="hpanel">
                    <div class="panel-heading">
                        <h3 class="text-success"><b><i class="fa fa-edit"></i> View Transactions</b></h3>
                    </div>
                    <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
                        <form id="frm1" action="" method="GET" onSubmit="return validate();">
                            <table id="" class="table table-striped table-bordered">
                                <thead>
                                    <tr class="text-success">
                                        <th>Branch Name</th>
                                        <th>No of Customer's billed</th>
                                        <th>Gross Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($con, "SELECT SUM(grossA) AS grossA,branchId,COUNT(*) AS billId,date  FROM `trans` WHERE date='$date' AND branchId='$search2' AND status='Approved'");
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        $branch = $row['branchId'];
                                        $sql = "select branchName from branch where branchId='$branch'";
                                        $res = mysqli_query($con, $sql);
                                        $row2 = mysqli_fetch_array($res);
                                        if (!empty($row2['branchName'])) {
                                            echo "<tr>";
                                            echo "<td>" . $row2['branchName'] . "</td>";
                                            echo "<td>" . $row['billId'] . "</td>";
                                            echo "<td>" . $row['grossA'] . "</td>";
                                            echo "<td>" . $row['date'] . "</td>";
                                            echo "</tr>";
                                        } else {
                                            echo "<tr>";
                                            echo "<td>No Branches Selected </td>";
                                            echo "<td>0</td>";
                                            echo "<td>0</td>";
                                            echo "<td>" . $date . "</td>";

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
    </div>
    <div style="clear:both"></div>
    <?php include("footer.php"); ?>
</div>