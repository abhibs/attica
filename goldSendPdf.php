<?php
	session_start();
	date_default_timezone_set("Asia/Kolkata");
	include("dbConnection.php");
	$branchId = $_SESSION['branchCode'];
	$date = date('Y-m-d');
	
	
	$billQuery = "SELECT t.billId, t.date, t.grossW, t.netW, t.grossA, t.netA, t.purity, t.rate, t.comm, t.margin, t.type, count(*) AS count,
	
	ROUND(SUM(CASE WHEN o.nine='24Karat' THEN o.weight ELSE 0 END), 3) AS pure_weight,
	ROUND(SUM(CASE WHEN o.nine='24Karat' THEN o.reading ELSE 0 END), 3) AS pure_net,
	SUM(CASE WHEN o.nine='24Karat' THEN o.gross ELSE 0 END) AS pure_gross,
	
	ROUND(SUM(CASE WHEN o.nine='916' THEN o.weight ELSE 0 END), 3) AS hall_weight,
	ROUND(SUM(CASE WHEN o.nine='916' THEN o.reading ELSE 0 END), 3) AS hall_net,
	SUM(CASE WHEN o.nine='916' THEN o.gross ELSE 0 END) AS hall_gross,
	
	ROUND(SUM(CASE WHEN o.nine='22KNON916' THEN o.weight ELSE 0 END), 3) AS non_weight,
	ROUND(SUM(CASE WHEN o.nine='22KNON916' THEN o.reading ELSE 0 END), 3) AS non_net,
	SUM(CASE WHEN o.nine='22KNON916' THEN o.gross ELSE 0 END) AS non_gross,
	
	ROUND(SUM(CASE WHEN (o.nine='22CT' OR o.nine='OT') THEN o.weight ELSE 0 END), 3) AS low_weight,
	ROUND(SUM(CASE WHEN (o.nine='22CT' OR o.nine='OT') THEN o.reading ELSE 0 END), 3) AS low_net,
	SUM(CASE WHEN (o.nine='22CT' OR o.nine='OT') THEN o.gross ELSE 0 END) AS low_gross
	
	FROM trans t LEFT JOIN ornament o ON (t.date=o.date AND t.billId=o.billId)
	WHERE t.staDate='$date' AND t.status='Approved' AND t.metal='Gold' AND t.branchId='$branchId'
	GROUP BY t.billId, t.date, t.grossW, t.netW, t.grossA, t.netA, t.purity, t.rate, t.comm, t.margin, t.type";
	
	$billSQL = mysqli_query($con, $billQuery);
	$billCount = mysqli_num_rows($billSQL);
	
	if($billCount > 0){
		
		$branchData = mysqli_fetch_assoc(mysqli_query($con,"SELECT b.branchName,e.empId,e.name FROM branch b,users u,employee e WHERE b.branchId='$branchId' AND b.branchId=u.branch AND u.employeeId=e.empId"));
		
		require('fpdf/fpdf.php');
		class PDF extends FPDF
		{
			function Footer()
			{
				$this->SetY(-15);					
				$this->SetFont('Times', '', 10);
				$this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
			}
		}
		
		
		/* --------------    START OF PDF CODE    -------------- */
		
		$pdf = new PDF();
		$pdf->AliasNbPages();
		$pdf->AddPage();
		$pdf->SetFillColor(224,235,255);
		
		/* --------------    LOGO AND BRANCH DATA    --------------- */
		
		$pdf->Image('logos/englishbill.jpg', 150, 15, 50, 20, 'JPG');
		
		$pdf->SetXY(10, 15);
		$pdf->SetFont('Times', 'B', '17');
		$pdf->Cell(100, 8, "Gold Send Report", 0, 1, "L", true);
		
		$pdf->SetXY(10, 25);
		$pdf->SetFont('Times', '', '11');
		
		$pdf->Cell(15, 6, "Branch", 0, 0, "L", false);
		$pdf->Cell(90, 6, ":  ".$branchData['branchName'], 0, 1, "L", false);
		
		$pdf->Cell(15, 6, "BM", 0, 0, "L", false);
		$pdf->Cell(90, 6, ":  ".$branchData['name']." / ".$branchData['empId'], 0, 1, "L", false);
		
		$pdf->Cell(15, 6, "Date", 0, 0, "L", false);
		$pdf->Cell(90, 6, ":  ".date("d-m-Y", strtotime($date)), 0, 1, "L", false);
		
		/* --------------    Bill List   --------------- */
		
		$pdf->ln(10);
		$pdf->SetFont('Times', 'B', '10');
		$pdf->Cell(5, 8, "#", 1, 0, "C", true);
		$pdf->Cell(20, 8, "Bill ID", 1, 0, "C", true);
		$pdf->Cell(24, 8, "Date", 1, 0, "C", true);
		$pdf->Cell(25, 8, "Gross Weight", 1, 0, "C", true);
		$pdf->Cell(23, 8, "Net Weight", 1, 0, "C", true);
		$pdf->Cell(25, 8, "Gross Amount", 1, 0, "C", true);
		$pdf->Cell(20, 8, "Net Amount", 1, 0, "C", true);
		$pdf->Cell(20, 8, "Purity", 1, 0, "C", true);
		$pdf->Cell(13, 8, "Rate", 1, 0, "C", true);
		$pdf->Cell(15, 8, "Margin", 1, 1, "C", true);
		$pdf->ln(2);
		
		$bill_No = 1;
		$physical_bills = 0;
		$release_bills = 0;
		
		$total_grossW = 0;
		$total_netW = 0;
		$total_grossA = 0;
		$total_netA = 0;
		$total_margin = 0;
		
		$rate = 1;
		
		$pdf->SetFont('Times', '', '10');
		while($row = mysqli_fetch_assoc($billSQL)){	
			
			$pdf->Cell(5, 8, $bill_No , 1, 0, "C", true);
			$pdf->Cell(20, 8, $row['billId'], 1, 0, "C", true);
			$pdf->Cell(24, 8, $row['date'], 1, 0, "C", true);
			$pdf->Cell(25, 8, $row['grossW'], 1, 0, "C", true);
			$pdf->Cell(23, 8, $row['netW'], 1, 0, "C", true);
			$pdf->Cell(25, 8, $row['grossA'], 1, 0, "C", true);
			$pdf->Cell(20, 8, $row['netA'], 1, 0, "C", true);
			$pdf->Cell(20, 8, $row['purity'], 1, 0, "C", true);
			$pdf->Cell(13, 8, $row['rate'], 1, 0, "C", true);
			$pdf->Cell(15, 8, $row['comm'], 1, 1, "C", true);
			
			if($row['pure_weight'] != 0){
				$pdf->Cell(5, 8, "" , 0, 0, "C");
				$pdf->Cell(44, 8, "Pure", 1, 0, "C");
				$pdf->Cell(25, 8, $row['pure_weight'], 1, 0, "C");
				$pdf->Cell(23, 8, $row['pure_net'], 1, 0, "C");
				$pdf->Cell(25, 8, $row['pure_gross'], 1, 0, "C");
				$pdf->Cell(20, 8, "", 0, 0, "C");
				$pdf->Cell(20, 8, ROUND((($row['pure_gross'] / $row['pure_net']) / $row['rate']) * 100, 2), 1, 1, "C");
			}
			if($row['hall_weight'] != 0){
				$pdf->Cell(5, 8, "" , 0, 0, "C");
				$pdf->Cell(44, 8, "916 Hallmark", 1, 0, "C");
				$pdf->Cell(25, 8, $row['hall_weight'], 1, 0, "C");
				$pdf->Cell(23, 8, $row['hall_net'], 1, 0, "C");
				$pdf->Cell(25, 8, $row['hall_gross'], 1, 0, "C");
				$pdf->Cell(20, 8, "", 0, 0, "C");
				$pdf->Cell(20, 8, ROUND((($row['hall_gross'] / $row['hall_net']) / $row['rate']) * 100, 2), 1, 1, "C");
			}
			if($row['non_weight'] != 0){
				$pdf->Cell(5, 8, "" , 0, 0, "C");
				$pdf->Cell(44, 8, "916 Non Hallmark", 1, 0, "C");
				$pdf->Cell(25, 8, $row['non_weight'], 1, 0, "C");
				$pdf->Cell(23, 8, $row['non_net'], 1, 0, "C");
				$pdf->Cell(25, 8, $row['non_gross'], 1, 0, "C");
				$pdf->Cell(20, 8, "", 0, 0, "C");
				$pdf->Cell(20, 8, ROUND((($row['non_gross'] / $row['non_net']) / $row['rate']) * 100, 2), 1, 1, "C");
			}
			if($row['low_weight'] != 0){
				$pdf->Cell(5, 8, "" , 0, 0, "C");
				$pdf->Cell(44, 8, "Low Purity", 1, 0, "C");
				$pdf->Cell(25, 8, $row['low_weight'], 1, 0, "C");
				$pdf->Cell(23, 8, $row['low_net'], 1, 0, "C");
				$pdf->Cell(25, 8, $row['low_gross'], 1, 0, "C");
				$pdf->Cell(20, 8, "", 0, 0, "C");
				$pdf->Cell(20, 8, ROUND((($row['low_gross'] / $row['low_net']) / $row['rate']) * 100, 2), 1, 1, "C");
			}
			
			$pdf->ln(2);
			
			$bill_No++;
			if($row['type'] == "Physical Gold"){
				$physical_bills++;
			}
			else{
				$release_bills++;
			}
			$total_grossW += $row['grossW'];
			$total_netW += $row['netW'];
			$total_grossA += $row['grossA'];
			$total_netA += $row['netA'];
			$total_margin += $row['margin'];
			$rate = $row['rate'];
		}
		
		$total_purity = ROUND((($total_grossA / $total_netW) / $rate) * 100, 2);
		$total_margin = ROUND(($total_margin / $total_grossA) * 100, 2);
		
		$pdf->ln(2);
		$pdf->SetFont('Times', 'B', '10');
		$pdf->Cell(49, 8, "Packets : ".($bill_No - 1) , 1, 0, "C", true);
		$pdf->Cell(25, 8, $total_grossW, 1, 0, "C", true);
		$pdf->Cell(23, 8, $total_netW, 1, 0, "C", true);
		$pdf->Cell(25, 8, $total_grossA, 1, 0, "C", true);
		$pdf->Cell(20, 8, $total_netA, 1, 0, "C", true);
		$pdf->Cell(20, 8, $total_purity, 1, 0, "C", true);
		$pdf->Cell(13, 8, "", "T", 0, "C");
		$pdf->Cell(15, 8, $total_margin, 1, 1, "C", true);
		
		$pdf->Cell(25, 8, "Physical", 1, 0, "C");
		$pdf->Cell(24, 8, $physical_bills, 1, 1, "C");
		$pdf->Cell(25, 8, "Release", 1, 0, "C");
		$pdf->Cell(24, 8, $release_bills, 1, 0, "C");
		
		$pdf->SetFont('Times', '', '11');
		$pdf->Cell(90, 8, "Tare Weight :", 0, 0, "R");
		$pdf->Cell(0, 8, "", "B", 1, "R");
		
		$pdf->ln(10);
		$pdf->Cell(15, 8, "BM :", 0, 0, "L");
		$pdf->Cell(40, 8, "", 0, 0, "L");
		$pdf->Cell(70, 8, "Place :", 0, 0, "R");
		$pdf->Cell(0, 8, "", 0, 1, "L");
		
		$pdf->ln(3);
		$pdf->Cell(15, 8, "ABM :", 0, 0, "L");
		$pdf->Cell(40, 8, "", 0, 0, "L");
		$pdf->Cell(70, 8, "Carrier :", 0, 0, "R");
		$pdf->Cell(0, 8, "", 0, 1, "L");
		
		$filename = "GoldSendReport".$date.".pdf";
		$pdfdoc = $pdf->Output($filename,'I');
		
	}
	else{
		echo "<script type='text/javascript'>alert('No Report Is Generated Report On ".$date."')</script>";
		echo "<script>open(location, '_self').close();</script>";
	}
	
?>