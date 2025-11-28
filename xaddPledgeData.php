 
<?php
session_start();
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");

$branchId = $_SESSION['branchCode'];
$employeeId = $_SESSION['employeeId'];
//$invoiceNumber = $_SESSION['invoiceNumber'];

$invoiceNumber= $_POST['invoiceNumber'];

$ornamentType = $_POST['ornamentType'];
$count = $_POST['count'];
$grossW = $_POST['grossW'];
$stoneW = $_POST['stoneW'];
$pur = $_POST['pur'];
$amount = $_POST['amount'];

$netW=($grossW-$stoneW);
$GrossAmount=($netW*$amount);

$date = date('Y-m-d');


$sql = "INSERT INTO pledge_ornament (invoiceId, ornamentType, count, grossW, stoneW, purity, amount, date) VALUES ('$invoiceNumber', '$ornamentType', '$count', '$grossW', '$stoneW', '$pur','$GrossAmount','$date')";

if (mysqli_query($con, $sql)) {
    
    echo "Data inserted successfully";
 
    $result = mysqli_query($con, "SELECT * FROM pledge_ornament WHERE id = " . mysqli_insert_id($con));
    $row = mysqli_fetch_assoc($result);

    $newRow = '<tr>' .
		'<td>' . $row['ornamentType'] . ' (' . $row['count'] . ')' . '</td>' .
        '<td>' . $row['grossW'] . '</td>' .
        '<td>' . $row['stoneW'] . '</td>' .
        '<td>' . ($row['grossW'] - $row['stoneW']) . '</td>' . 
        '<td>' . $row['purity'] . '</td>' .
        '<td>' . $row['amount'] . '</td>' .
		'<td><b><a class="text-danger" title="Delete Record" onclick="delete_ornament(' . $row['id'] . ')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a></b></td>' . 		
		'</tr>';

    echo $newRow;

} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($con);
}

?>




