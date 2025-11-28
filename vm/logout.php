<?php 
	session_start();
	$_SESSION = array();
	session_destroy();
	//if(!isset($_SESSION['login_username'])){
	header("location:index.php");
	//exit();
    //}	
?>

