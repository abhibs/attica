<?php

	session_start();
	error_reporting(E_ERROR | E_PARSE);
	include("dbConnection.php");

	// ---------------------------------- BRANCH (30 DAYS COG) | @xeveryCustomer.php  -----------------------------------
	if(isset($_GET['branchcog'])){
		$branchId = $_GET['branch'];

		$today_date = date("Y-m-d");
		$last_date = date('Y-m-d', strtotime('-30 days'));

		$transQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT count(*) AS bills
			FROM trans
			WHERE date BETWEEN '$last_date' AND '$today_date' AND 
			status='Approved' AND
			branchId='$branchId'"));

		$enquiryQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry 
			FROM walkin
			WHERE date BETWEEN '$last_date' AND '$today_date' AND 
			issue NOT IN ('Rejected') AND
			branchId='$branchId' "));

		$bills = $transQuery['bills'] ? $transQuery['bills'] :  0;
		$enquiry = $enquiryQuery['enquiry'] ? $enquiryQuery['enquiry'] : 0;

		$cog = 0;
		if($bills == 0 && $enquiry == 0){
			$cog = 0;
		}
		else{
			$cog = ROUND(($bills/($enquiry + $bills)) * 100, 2);
		}

		echo $cog;
	}

	if (isset($_GET['thisMonthCOG'])) {
	$branchId = $_GET['branch'];

	$today_date = date("Y-m-d");
	$last_date = date("Y-m-01");

	$transQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT count(*) AS bills
			FROM trans
			WHERE date BETWEEN '$last_date' AND '$today_date' AND
			status='Approved' AND
			branchId='$branchId'"));

	$enquiryQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry
			FROM walkin
			WHERE date BETWEEN '$last_date' AND '$today_date' AND
			issue NOT IN ('Rejected') AND
			branchId='$branchId' "));

	$bills = $transQuery['bills'] ? $transQuery['bills'] : 0;
	$enquiry = $enquiryQuery['enquiry'] ? $enquiryQuery['enquiry'] : 0;

	$cog = 0;
	if ($bills == 0 && $enquiry == 0) {
		$cog = 0;
	} else {
		$cog = ROUND(($bills / ($enquiry + $bills)) * 100, 2);
	}

	echo $cog;
	}

	
	// ---------------------------------- BM (30 DAYS COG) | @xeveryCustomer.php -----------------------------------
	if(isset($_GET['bmcog'])){
		$bm = $_GET['empId'];

		$today_date = date("Y-m-d");
		// $last_date = date('Y-m-d', strtotime('-30 days'));
		$last_date = date("Y-m-d");

		$transQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT count(*) AS bills
			FROM trans
			WHERE date BETWEEN '$last_date' AND '$today_date' AND 
			flag='$bm' AND
			status='Approved'"));

		$enquiryQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry 
			FROM walkin
			WHERE date BETWEEN '$last_date' AND '$today_date' AND 
			emp_type='$bm' AND
			issue NOT IN ('Rejected')"));

		$bills = $transQuery['bills'] ? $transQuery['bills'] :  0;
		$enquiry = $enquiryQuery['enquiry'] ? $enquiryQuery['enquiry'] : 0 ;

		$cog = 0;
		if($bills == 0 && $enquiry == 0){
			$cog = 0;
		}
		else{
			$cog = ROUND(($bills/($enquiry + $bills)) * 100, 2);
		}

		echo $cog;
	}


	// ---------------------------------- ZONAL (ONE DAY COG) -----------------------------------
	if(isset($_GET['zonalOneDayCOG'])){
		$empId = $_GET['zonalEmpId'];
		$date = date("Y-m-d");

		$zonalTransData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(t.id) AS bills
			FROM trans t 
			JOIN branch b ON t.branchId=b.branchId
			WHERE b.ezviz_vc = '$empId' AND t.date='$date' AND t.status='Approved'"));

		$zonalWalkinData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry
			FROM walkin w
			JOIN branch b ON w.branchId=b.branchId 
			WHERE b.ezviz_vc = '$empId' AND w.date='$date' AND w.issue!='Rejected' "));

		$zonal_cog = 0;
		$zonal_bills = ($zonalTransData['bills'] === null) ? 0 : $zonalTransData['bills'];
		$zonal_enq = ($zonalWalkinData['enquiry'] === null) ? 0 : $zonalWalkinData['enquiry'];

		if(($zonal_bills + $zonal_enq) > 0){
			$zonal_cog = ROUND(($zonal_bills / ($zonal_bills + $zonal_enq)) * 100, 2);
		}

		echo $zonal_cog;
	}


	// ---------------------------------- ZONAL (30 DAYS COG) -----------------------------------
	if(isset($_GET['zonalThirtyDayCOG'])){
		$empId = $_GET['zonalEmpId'];

		$today_date = date("Y-m-d");
		$last_date = date('Y-m-d', strtotime('-30 days'));


		$zonalTransData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(t.id) AS bills
			FROM trans t 
			JOIN branch b ON t.branchId=b.branchId
			WHERE b.ezviz_vc = '$empId' AND t.date BETWEEN '$last_date' AND '$today_date' AND t.status='Approved'"));

		$zonalWalkinData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry
			FROM walkin w
			JOIN branch b ON w.branchId=b.branchId 
			WHERE b.ezviz_vc = '$empId' AND w.date BETWEEN '$last_date' AND '$today_date' AND w.issue!='Rejected' "));

		$zonal_cog = 0;
		$zonal_bills = ($zonalTransData['bills'] === null) ? 0 : $zonalTransData['bills'];
		$zonal_enq = ($zonalWalkinData['enquiry'] === null) ? 0 : $zonalWalkinData['enquiry'];

		if(($zonal_bills + $zonal_enq) > 0){
			$zonal_cog = ROUND(($zonal_bills / ($zonal_bills + $zonal_enq)) * 100, 2);
		}

		echo $zonal_cog;
	}

	//if (isset($_GET['zonalThisMonthCOG'])) {
	//$empId = $_GET['zonalEmpId'];

	//$today_date = date("Y-m-d");
	//$month_first_date = date("Y-m-01");


	//$zonalTransData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(t.id) AS bills
			//FROM trans t
			//JOIN branch b ON t.branchId=b.branchId
			//WHERE b.ezviz_vc = '$empId' AND t.date BETWEEN '$last_date' AND '$today_date' AND t.status='Approved'"));

	//$zonalWalkinData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT mobile) AS enquiry
			//FROM walkin w
			//JOIN branch b ON w.branchId=b.branchId
			//WHERE b.ezviz_vc = '$empId' AND w.date BETWEEN '$last_date' AND '$today_date' AND w.issue!='Rejected' "));

	//$zonal_cog = 0;
	//$zonal_bills = ($zonalTransData['bills'] === null) ? 0 : $zonalTransData['bills'];
	//$zonal_enq = ($zonalWalkinData['enquiry'] === null) ? 0 : $zonalWalkinData['enquiry'];

	//if (($zonal_bills + $zonal_enq) > 0) {
	//	$zonal_cog = ROUND(($zonal_bills / ($zonal_bills + $zonal_enq)) * 100, 2);
	//}

	//echo $zonal_cog;
	//}
	


	if (isset($_GET['zonalThisMonthCOG'])) {
    $empId = $_GET['zonalEmpId'];

    // Dynamic first and last date of current month
    $last_date = date('Y-m-01');
    $today_date = date('Y-m-t');

    // Get Bills count
    $zonalTransData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(t.id) AS bills
        FROM trans t
        JOIN branch b ON t.branchId = b.branchId
        WHERE b.ezviz_vc = '$empId'
        AND t.date BETWEEN '$last_date' AND '$today_date'
        AND t.status = 'Approved'"));

    // Get Walk-in Enquiry count
    $zonalWalkinData = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT w.mobile) AS enquiry
        FROM walkin w
        JOIN branch b ON w.branchId = b.branchId
        WHERE b.ezviz_vc = '$empId'
        AND w.date BETWEEN '$last_date' AND '$today_date'
        AND w.issue != 'Rejected'"));

    $zonal_bills = $zonalTransData['bills'] ?? 0;
    $zonal_enq = $zonalWalkinData['enquiry'] ?? 0;
    $zonal_cog = 0;

    if (($zonal_bills + $zonal_enq) > 0) {
        $zonal_cog = round(($zonal_bills / ($zonal_bills + $zonal_enq)) * 100, 2);
    }

    echo $zonal_cog;
}

?>
