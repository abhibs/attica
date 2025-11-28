    <?php
        session_start();
        $type=$_SESSION['usertype'];
        if($type=='Master'){
            include("header.php");
            include("menumaster.php");
        }
        else if ($type == 'SundayUser') {
        include("header.php");
        include("menuSundayUser.php");
    } 
        else if($type=='ChequesCounter'){
            include("header.php");
            include("menucheques.php");
        }
        else if($type=='AccHead'){
            include("header.php");
            include("menuaccHeadPage.php");
        }
        else{
            include("logout.php");
        }
        include("dbConnection.php");
    ?>
    <style>
        #wrapper h3{
        text-transform:uppercase;
        font-weight:600;
        font-size: 20px;
        color:#123C69;
        }
        .hpanel .panel-body {
        box-shadow: 10px 15px 15px #999;
        border: 1px solid #edf2f9;
        background-color: #f5f5f5;
        border-radius:3px;
        padding: 20px;
        }
        .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
        background-color:#fffafa;
        }
        .text-success{
        color:#123C69;
        text-transform:uppercase;
        font-weight:bold;
        font-size: 12px;
        }
        .btn-primary{
        background-color:#123C69;
        }
        .theadRow {
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
        font-size: 12px;
        font-family:'Roboto',sans-serif;
        text-transform:uppercase;
        color:#fffafa;
        background-color:#123C69;
        box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
        text-align:center;
        position:relative;
        }
        .fa_Icon {
        color:#990000;
        }
        .table-responsive .row{
        margin: 0px;
        }
    </style>
    <div id="wrapper">
        <div class="row content">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <div class="panel-tools">
                            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        </div>
                        <h3><b><i class="fa_Icon fa fa-institution"></i> Cheque Details &nbsp;</b></h3>
                    </div>
                    <div class="panel-body">
        <div class="table-responsive">
            <table id="example2" class="table table-striped table-bordered table-hover">
                <thead class="theadRow">
                    <tr>
                        <th>#</th>
                        <th>Branch_ID</th>
                        <th>Branch Name</th>
                        <th>Branch Address</th>
                        <th>Contact</th>                    
                        <th>Email</th>
                        <th>Latest Cheque Count</th>
                    </tr>
                </thead>
<tbody>
<?php
    $i = 1;

    // ✅ Updated SQL to exclude Chennai branches except AGPL030
    $sql = mysqli_query($con, "
        SELECT branchId, branchName, addr, officeContact, email, gst, openclosestatus
        FROM branch
        WHERE Status = 1 
          AND city != 'Bengaluru' 
          AND (city != 'Chennai' OR branchId = 'AGPL030')
    ");

    while ($row = mysqli_fetch_assoc($sql)) {
        $branchId = $row['branchId'];

        // Get latest cheque count from closing table
        $chequeRes = mysqli_query($con, "
            SELECT cheques 
            FROM closing 
            WHERE branchId = '$branchId' 
            ORDER BY date DESC 
            LIMIT 1
        ");
        $chequeRow = mysqli_fetch_assoc($chequeRes);
        $chequeCount = $chequeRow ? $chequeRow['cheques'] : 'N/A';

        // Get employeeId from users table where username = branchId
        $userRes = mysqli_query($con, "
            SELECT employeeId 
            FROM users 
            WHERE username = '$branchId'
            LIMIT 1
        ");
        $userRow = mysqli_fetch_assoc($userRes);
        $employeeId = $userRow['employeeId'];

        // Get contact from employee table
        $empContact = 'N/A';
        if ($employeeId) {
            $empRes = mysqli_query($con, "
                SELECT contact 
                FROM employee 
                WHERE empId = '$employeeId'
                LIMIT 1
            ");
            $empRow = mysqli_fetch_assoc($empRes);
            if ($empRow) {
                $empContact = $empRow['contact'];
            }
        }

        // Display row
        echo "<tr>";
        echo "<td>" . $i . "</td>";
        echo "<td>" . $branchId . "</td>";  
        echo "<td>" . $row['branchName'] . "</td>";
        echo "<td>" . $row['addr'] . "</td>";
        echo "<td>" . $empContact . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $chequeCount . "</td>"; 
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
    <?php include("footer.php"); ?>

