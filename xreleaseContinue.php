<?php	
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	
	if(isset($_POST['relForwardSubmit'])){
		if (!empty($_POST['rid'])) {
			$forward = $_POST['forward'];
			if($forward == 'contB'){
				$_SESSION['customerID'] = $_POST['cid'];
				$_SESSION['mobile'] = $_POST['phone'];
				$_SESSION['Rid'] =  $_POST['rid'];
				echo header("location:xaddTransactionRelease.php");
			}
			else if($forward == 'terminate'){
				echo header("location:xreleaseupdate.php?rid=".$_POST['rid']);
			}
			else if($forward == 'carryForward'){
				echo header("location:xreleaseCarryForward.php?rid=".$_POST['rid']);
			}
			else if($forward == 'noRelease'){
				$rid = $_POST['rid'];
				$sql = "UPDATE releasedata SET status='Rejected' WHERE rid='$rid'";
				if (mysqli_query($con, $sql)) {
					echo "<script type='text/javascript'>alert('Updated Successfully !!!')</script>";
					echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
				}
				else {
					echo "<script>alert(Failed TO Update, Try Again!!!')</script>";
					echo "<script>setTimeout(\"location.href = 'xreleaseStatus.php';\",150);</script>";
				}
			}
		}
	}
	else{
		include("logout.php");
	}