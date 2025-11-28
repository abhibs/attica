<?php
    session_start();
    date_default_timezone_set("Asia/Kolkata");
    include("dbConnection.php");
    $date = date('Y-m-d');
    $time = date("h:i:s");
	
	// GOLD RATE UPDATE
	if(isset($_POST['goldRate'])){
		$goldRate = $_POST['goldRate'];		
		$url = "http://14.97.3.235/Alpha_Attica/Api/gold_rate_update";
		$rowTSC = array();
		$rowTSC['RATE'] = $goldRate;
		$chs = curl_init($url);
		$a = json_encode($rowTSC);
		curl_setopt($chs, CURLOPT_POSTFIELDS,$a);
		curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($chs);
		curl_close($chs);
		$result = json_decode($res,true);
		if($result['Status'] == "Success"){
			echo "<script type='text/javascript'>alert('Gold Rate Updated')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		}
		else{
			echo "<script type='text/javascript'>alert('Error Updating Gold Rate!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		} 
	}
	
	//ANNOUNCEMENT UPDATE
	else if(isset($_POST['ann'])){
		$announce = $_POST['ann'];		
		$url = "http://14.97.3.235/Alpha_Attica/Api/announcement_update";
		$rowTSC = array();
		$rowTSC['TEXT'] = $announce;
		$chs = curl_init($url);
		$a = json_encode($rowTSC);
		curl_setopt($chs, CURLOPT_POSTFIELDS,$a);
		curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($chs);
		curl_close($chs); 
		$result = json_decode($res,true);
    	if($result['Status'] == "Success"){
    		$insconn = "INSERT INTO announcement_update(thoughtoftheday,date,time) VALUES ('$announce','$date','$time')";
			if(mysqli_query($con,$insconn)){
				echo "<script type='text/javascript'>alert('Announcement Updated')</script>";
    			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
			}
			else{
			    echo "<script type='text/javascript'>alert('Error Inserting Data!!!')</script>";
			    echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		    } 
    	}
	    else{
			echo "<script type='text/javascript'>alert('Error Updating Announcement!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		} 	
	}
?>