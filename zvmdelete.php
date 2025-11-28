<?php
include("dbConnection.php");
if ((isset($_GET['empId']))) {
    $id = $_GET['empId'];
    $inscon = "DELETE FROM `vmagent` WHERE agentId='$id'";
    mysqli_query($con, $inscon);

    $sql1 = "DELETE FROM `employee` WHERE empId='$id'";
    mysqli_query($con, $sql1);

    $sql2 = "DELETE FROM `users` WHERE password='$id'";
    mysqli_query($con, $sql2);

    if ((mysqli_query($con, $sql2))) {

        echo "<script>alert('Employee: " . $_GET['empId'] . " Delete Done')</script>";
        echo "<script>setTimeout(\"location.href = 'zviewvm.php';\",150);</script>";
    } else {
        echo "<script type='text/javascript'>alert('Error Modifiying Data!')</script>";
        echo "<script>setTimeout(\"location.href = 'zviewvm.php';\",150);</script>";
    }
}
