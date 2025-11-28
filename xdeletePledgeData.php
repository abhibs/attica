
<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $sqlA = mysqli_query($con,"DELETE FROM pledge_ornament WHERE id = '$id'");
    if($sqlA){
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error Deleting Data!'));
    }
}
?>

