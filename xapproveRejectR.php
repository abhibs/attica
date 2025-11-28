<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	
	// APPROVE CASH RELEASE ( @ approveReleaseData.php )
	if(isset($_POST['submitApproveCash'])){
	    $id = $_POST['id'];
	    $remark = $_POST['remark'];
		$sql = "UPDATE releasedata SET status='Approved',remarks='$remark' WHERE rid='$id'";
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Cash Release Approved!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Approving,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>"; 
		}
	}
	
	// REJECT CASH RELEASE ( @ approveReleaseData.php )
	else if(isset($_POST['submitRejectCash'])){
	    $id = $_POST['id'];
	    $remark = $_POST['remark'];
		$sql = mysqli_query($con,"UPDATE releasedata SET status='Rejected',remarks='$remark' WHERE rid='$id'");
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('Cash Release Rejected!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Rejecting,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>"; 
		}
	}
	
	// APPROVE IMPS RELEASE ( @ approveReleaseData.php )
	else if(isset($_POST['submitApproveIMPS'])){
	    $id = $_POST['id'];
	    $remark = $_POST['remark'];	   
		$cashA = $_POST['cashA'];
		$impsA = $_POST['impsA'];
		$amount = $cashA + $impsA;	
		$sql ="UPDATE releasedata SET status='Approved',remarks='$remark',amount='$amount',relCash='$cashA',relIMPS='$impsA' WHERE rid='$id'";
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('IMPS Release Approved!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Approving,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>";
		}
	}
	
	// REJECT IMPS RELEASE ( @ approveReleaseData.php )
	else if(isset($_POST['submitRejectIMPS'])){
	    $id = $_POST['id'];
	    $remark = $_POST['remark'];
		$sql = "UPDATE releasedata SET status='Rejected',remarks='$remark' WHERE rid='$id'";
		if(mysqli_query($con,$sql)){
			echo "<script type='text/javascript'>alert('IMPS Release Rejected!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>"; 
		}
		else{
			echo "<script type='text/javascript'>alert('Error While Rejecting,Try Again!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xapproveRelease.php';\",150);</script>";
		}
	}
?>