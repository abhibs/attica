<?php
	session_start();
	include("dbConnection.php");
	$billId=$_SESSION['bill'];
	$name=$_SESSION['name'];
	$phone=$_SESSION['mobile'];
	$grossW=$_SESSION['grossW'];
	$netW=$_SESSION['netW'];
	$paid=$_SESSION['amountP'];
	$rel=$_SESSION['rel'];
	$rate=$_SESSION['gold'];
	date_default_timezone_set("Asia/Kolkata");
	$time=date("H:i:s");
	$date=date("Y-m-d");
	//	$sql="insert into otp(customerName,mobile,message,date,time) values ('$name','$phone','Registration','$date','$time'";
	//	$res=mysqli_query($con,$sql);
	/*sql="select cash from gold where date='$date'";
		$res=mysqli_query($con,$sql);
		$row=mysqli_fetch_array($res);
	$rate=$row['cash'];*/
	if($rel!="")
	{
		//$otp = rand(100000,999999);
		$message ="Dear ".$name.", Thanks for billing in Attica Gold Pvt Ltd, Gold Gross Weight: ".$grossW.", Release Amount: ".$rel."Amount Paid: ".$paid;
// 		$url="http://api-alerts.solutionsinfini.com/v3/?method=sms&api_key=Ac1114feab4e31bdfaef1f02d3d3c23e7&to=".$phone."&sender=ATTICA&message=".$message;
		
		$url ="http://103.255.217.28:15181/BULK_API/SendMessage?loginID=attica_siht1&password=attica@123&mobile=".$phone."&text=".$message."&senderid=ATTICA&DLT_TM_ID=1001096933494158&DLT_CT_ID=&DLT_PE_ID=1201159127344394306&route_id=DLT_SERVICE_IMPLICT&Unicode=0&camp_name=attica_se";
	    $url1 = str_replace(" ",'%20', $url);
		
		
// 		http://103.255.217.28:15181/BULK_API/SendMessage?loginID=attica_siht1&password=attica@123&mobile=9361247663&text=hello&senderid=ATTICA&DLT_TM_ID=1001096933494158&DLT_CT_ID=&DLT_PE_ID=1201159127344394306&route_id=DLT_SERVICE_IMPLICT&Unicode=0&camp_name=attica_se
		$ch = curl_init();
		curl_setopt_array($ch, array(
		CURLOPT_URL => $url1,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		//CURLOPT_POSTFIELDS => $postData
		//,CURLOPT_FOLLOWLOCATION => true
		));
		//Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//get response
		$output = curl_exec($ch);
		//Print error if any
		if(curl_errno($ch))
		{
			echo 'error:' . curl_error($ch);
		}
		curl_close($ch);
		echo "<script>setTimeout(\"location.href = 'pending.php';\",0);</script>";
	}
	else
	{
		$message ="Dear ".$name.", Thanks for billing in Attica Gold Pvt Ltd, Gold Gross Weight: ".$grossW.", Amount Paid: ".$paid;
		$url="http://api-alerts.solutionsinfini.com/v3/?method=sms&api_key=Ac1114feab4e31bdfaef1f02d3d3c23e7&to=".$phone."&sender=ATTICA&message=".$message;
		$ch = curl_init();
		curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		//CURLOPT_POSTFIELDS => $postData
		//,CURLOPT_FOLLOWLOCATION => true
		));
		//Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//get response
		$output = curl_exec($ch);
		//Print error if any
		if(curl_errno($ch))
		{
			echo 'error:' . curl_error($ch);
		}
		curl_close($ch);
		echo "<script>setTimeout(\"location.href = 'pending.php';\",0);</script>";
		
	}
	
?>