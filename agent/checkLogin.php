<?php
	session_start();
	include("../dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	
	$ipsAllowed = ['103.139.158.138', '14.97.4.86', '202.83.19.248'];
	$ip = $_SERVER['REMOTE_ADDR']; 
	
	if(array_search($ip, $ipsAllowed) !== false){
		if ((isset($_POST["username"])) && ($_POST["username"] != "") && (isset($_POST["password"])) && ($_POST["password"] != "")) {			
			$userSQL = mysqli_query($con, "SELECT * FROM users 
			WHERE username='$_POST[username]' AND password='$_POST[password]' AND type='VM-REG' 
			LIMIT 1");
			$userCount = mysqli_num_rows($userSQL);
			$userData = mysqli_fetch_assoc($userSQL);
			if($userCount > 0){
				$_SESSION['login_username'] = $userData['username'];
				$_SESSION['usertype'] = $userData['type'];
				$_SESSION['userid'] = $userData['id'];
				$_SESSION['employeeId'] = $userData['employeeId'];
				$_SESSION['branchCode'] = $userData['branch'];
							
				header("location:register.php");
			} 
			else {
				echo "<script type='text/javascript'>alert('Username and Password Is Incorrect!')</script>";
				echo "<script>setTimeout(\"location.href = 'index.php';\",150);</script>";
			}
		}
		else{
			echo "<script type='text/javascript'>alert('Username and Password Should not be Empty!')</script>";
			echo "<script>setTimeout(\"location.href = 'index.php';\",150);</script>";
		}
	}
	else{
		echo "<script type='text/javascript'>alert('IP Restricted!')</script>";
		echo "<script>setTimeout(\"location.href = 'index.php';\",150);</script>";
	}
?>