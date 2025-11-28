<?php
include("dbConnection.php");

if (isset($_POST['unblock_empId']) && isset($_POST['unblock_date'])) {
    $empId = $_POST['unblock_empId'];
    $date = $_POST['unblock_date'];

    // Update the status in the database
   // $query = "UPDATE attendance SET status = 0 WHERE empId = '$empId' AND date = '$date'";
    $query = "UPDATE attendance SET status = 0 WHERE empId = '$empId'";

    if (mysqli_query($con, $query)) {
        // Return success if the update is successful
        echo 'success';
    } else {
        // Return an error message if the update fails
        echo 'error';
    }
}
?>
