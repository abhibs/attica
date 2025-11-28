<?php
include("dbConnection.php");

$selectedEmpId = $_POST['empId'];
$query = "SELECT name FROM employee WHERE empId = '$selectedEmpId'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo $row['name'];
} else {
    echo 'Employee not found';
}





