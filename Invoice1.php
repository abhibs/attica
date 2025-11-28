<?php
	date_default_timezone_set('Asia/Calcutta');
	include("dbConnection.php");
	require('fpdf/fpdf.php');
	
	$invoiceId = base64_decode($_GET['id']);
	
	$trans = mysqli_fetch_assoc(mysqli_query($con,"SELECT billId, name, phone, branchId, netA, amountPaid, margin, releases, rate, metal, date, time
	FROM trans
	WHERE id='$invoiceId'"));
	
	$ornamentSQL = mysqli_query($con,"SELECT type, typeInfo, weight, sWaste, reading, purity, gross, nine
	FROM ornament 
	WHERE billId='$trans[billId]' AND date='$trans[date]'");
	
	$ornCount = mysqli_num_rows($ornamentSQL); 
	if($ornCount == '0'){
		echo "<script>alert('Ornament Details Not Available. Cannot Proceed Further');close();</script>";
		exit();
	}
	else{
		$totalGW = 0;
		$totalStone = 0;
		$totalNW = 0;
		$totalGA = 0;
	}
	
	$branch = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName, state, addr, gst
	FROM branch
	WHERE branchId='$trans[branchId]'"));
	
	$customer = mysqli_fetch_assoc(mysqli_query($con,"SELECT customerId, paline, pcity, pstate, ppin, pland, plocality, idNumber, addNumber, customerImage, cusThump, custSign
	FROM customer
	WHERE mobile='$trans[phone]'"));
	$customerImage = ($customer['customerImage'] == "" || !is_file($customer['customerImage'])) ? "images/images.jpg" : $customer['customerImage'];
	// $thumb = ($customer['cusThump'] == "" || !is_file('CustomerThumb/'.$customer['cusThump'])) ? "images/No.png" : 'CustomerThumb/'.$customer['cusThump'];
	$thumb = "images/No.png";
	$sign = ($customer['custSign'] == "" || !is_file('CustomerSignature/'.$customer['custSign'])) ? "images/No.png" : 'CustomerSignature/'.$customer['custSign'];
	$address = "ADDRESS : ".strtolower($customer['paline']).", \n".$customer['pcity'].", ".$customer['pstate']."-".$customer['ppin']."\n";
	//$address = "ADDRESS : ".strtolower($customer['paline']).", \n".strtolower($customer['plocality']).",".strtolower($customer['pland']).", ".$customer['pcity'].", ".$customer['pstate']."-".$customer['ppin']."\n";
	
	$logoImage = '';
	$termsAndConditionImage = '';
	if ($branch['state'] == 'Karnataka') {
		$logoImage = 'logos/kannadabill.jpg';
		$termsAndConditionImage = 'logos/kannada-terms.jpg';
	} 
	elseif ($branch['state']  == 'Tamilnadu' || $branch['state']  == 'Pondicherry') {
		$logoImage = 'logos/Tamilbill.jpg';
		$termsAndConditionImage = 'logos/tamil-terms.jpg';
	} 
	elseif ($branch['state']  == 'Telangana' || $branch['state']  == 'Andhra Pradesh') {
		$logoImage = 'logos/telugubill.jpg';
		$termsAndConditionImage = 'logos/telugu-terms.jpg';
	}
	
	/* --------------   FUNCTION TO CONVERT NUMBER TO WORDS    -------------- */
	function amountToWords(int $paidAmount){
		$paidd = round($paidAmount);
		$no = round($paidd);
		$point = round($no - $paidd, 2) * 100;
		$hundred = null;
		$digits_1 = strlen($no);
		$i = 0;
		if ($paidd > 0) {
			$str = array();
			$words = array('0' => '', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine', '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve', '13' => 'Thirteen', '14' => 'Fourteen', '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen', '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty', '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty', '60' => 'Sixty', '70' => 'Seventy', '80' => 'Eighty', '90' => 'Ninety');
			$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
			while ($i < $digits_1) {
				$divider = ($i == 2) ? 10 : 100;
				$number = floor($no % $divider);
				$no = floor($no / $divider);
				$i += ($divider == 10) ? 1 : 2;
				if ($number) {
					$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
					$hundred = ($counter == 1 && $str[0]) ? 'and ' : null;
					$str[] = ($number < 21) ? $words[$number] ." " . $digits[$counter] . $plural . " " . $hundred : $words[floor($number / 10) * 10]. " " . $words[$number % 10] . " ". $digits[$counter] . $plural . " " . $hundred;
				} else $str[] = null;
			}
			$str = array_reverse($str);
			$result = implode('', $str);
			$points = ($point) ? "." . $words[$point / 10] . " " .$words[$point = $point % 10] : '';
			$tot =  $result . "Only";
			return $tot;
		}	
	}
	
	/* --------------    FUNCTION THAT ADDS COMMAS ACCORDING TO INDIAN DECIMAL SYSTEM    -------------- */
	function numberSystem($number){
		return preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $number);
	}
	
	
	/* --------------    START OF PDF CODE    -------------- */
	$pdf = new FPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFillColor(224,235,255);
	
	/* --------------    LOGO AND ADDRESS    --------------- */
	$pdf->Image('logos/englishbill.jpg', 27, 8, 65, 21, 'JPG');
	$pdf->Image($logoImage, 117, 8, 65, 22, 'JPG');
	$pdf->ln(22);
	$pdf->SetFont('Arial', 'B', '10');
	$pdf->Cell(0, 3, "BRANCH : ".$branch['branchName']." , CONTACT: 8880300300 , GST NO: ".$branch['gst'], 0, 1, 'C');
	/* --------------   END OF - LOGO AND ADDRESS    --------------- */
	
	/* --------------    CUSTOMER INFORMATION   --------------- */
	$pdf->ln(2);
	$pdf->SetFont('Arial', 'B', '7');
	$pdf->Cell(0, 6, "ADDRESS : ".$branch['addr'], 1, 1, 'C');
	
	$pdf->SetFont('Arial', 'B', '8');
	$pdf->Cell(35, 8, "CUSTOMER ID", 1, 0, 'L', true);
	$pdf->Cell(54, 8, $customer['customerId'], 1, 0, 'L');
	$pdf->Cell(33, 8, "DATE / TIME", 1, 0, 'L', true);
	$pdf->Cell(33, 8, date("d-m-Y", strtotime($trans['date']))." ".$trans['time'], 1, 1, 'L');
	
	$pdf->Cell(35, 8, "CUSTOMER NAME", 1, 0, 'L', true);
	$pdf->Cell(54, 8, $trans['name'], 1, 0, 'L');
	$pdf->Cell(33, 8, "BILL ID", 1, 0, 'L', true);
	$pdf->Cell(33, 8, $trans['billId'], 1, 1, 'L');
	
	$pdf->Cell(35, 8, "CONTACT", 1, 0, 'L', true);
	$pdf->Cell(54, 8, $trans['phone'], 1, 0, 'L');
	$pdf->Cell(33, 8, strtoupper($trans['metal'])." PRICE - (24CT)", 1, 0, 'L', true);
	$pdf->Cell(33, 8, $trans['rate'], 1, 1, 'L');
	
	$pdf->SetXY(165, 43);
	$pdf->Cell(35, 24, $pdf->Image($customerImage, $pdf->GetX(), $pdf->GetY(), 35, 24), 1, false);
	$pdf->ln();
	
	$pdf->SetFont('Arial', 'B', '7');
	$pdf->MultiCell(124, 9, $address, 1, 'L');
	$pdf->SetXY(132, 67);
	$pdf->SetFont('Arial', 'B', '8');
	$pdf->Cell(33, 9, "ID PROOF", 1, 0, 'L', true);
	$pdf->Cell(35, 9, $customer['idNumber'], 1, 1, 'L');
	$pdf->SetXY(132, 76);
	$pdf->Cell(33, 9, "ADDRESS PROOF", 1, 0, 'L', true);
	$pdf->Cell(35, 9, $customer['addNumber'], 1, 1, 'L');
	$pdf->ln(1);
	/* --------------    END OF - CUSTOMER INFORMATION   --------------- */
	
	/* --------------    ORNAMENT LIST   --------------- */
	$pdf->SetFont('Arial', 'B', 7.5);
	$pdf->Cell(65, 6, 'ORNAMENT TYPE', 1, 0, 'C', true);
	$pdf->Cell(15, 6, 'CODE', 1, 0, 'C', true);
	$pdf->Cell(24, 6, 'GROSS WEIGHT', 1, 0, 'C', true);
	$pdf->Cell(20, 6, 'STONE / WAX', 1, 0, 'C', true);
	$pdf->Cell(20, 6, 'NET WEIGHT', 1, 0, 'C', true);
	$pdf->Cell(16, 6, 'PURITY', 1, 0, 'C', true);
	$pdf->Cell(30, 6, 'GROSS AMOUNT', 1, 1, 'C', true);
	
	$maxOrnaments = 30;
	$item = 1;
	while($row = mysqli_fetch_assoc($ornamentSQL)){
		if($item > $maxOrnaments){
			$pdf->AddPage();
			$pdf->Cell(0, 0, '', 1, 1);
			$item = 0;
		}
		
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(65, 6, (!empty($row["typeInfo"])) ? $row["type"]."  (".$row["typeInfo"].")" : $row["type"], 'L', 0, 'C');
		
		if($row['nine'] == "24Karat"){
			$pdf->Cell(15, 6, "1001", 'L', 0, 'C');
		}
		else if($row['nine'] == "916"){
			$pdf->Cell(15, 6, "1002", 'L', 0, 'C');
		}
		else if($row['nine'] == "22KNON916"){
			$pdf->Cell(15, 6, "1003", 'L', 0, 'C');
		}
		else if($row['nine'] == "22CT" || $row['nine'] == "OT"){
			$pdf->Cell(15, 6, "1004", 'L', 0, 'C');
		}
		else{
			$pdf->Cell(15, 6, "", 'L', 0, 'C');
		}
		
		$pdf->Cell(24, 6, $row['weight'], 'L', 0, 'C');
		$pdf->Cell(20, 6, $row['sWaste'], 'L', 0, 'C');
		$pdf->Cell(20, 6, $row['reading'], 'L', 0, 'C');
		$pdf->Cell(16, 6, $row['purity'], 'L', 0, 'C');
		$pdf->Cell(30, 6, numberSystem($row['gross']), 'LR', 1, 'C');
		
		$totalGW += $row['weight'];
		$totalStone += $row['sWaste'];
		$totalNW += $row['reading'];
		$totalGA += (int)$row['gross'];
		
		$item++;
		if($item > $maxOrnaments){
			$pdf->Cell(0, 0, '', 1, 1);
		}
	}
	
	$pdf->SetFont('Arial', 'B', 8);
	$pdf->Cell(80, 6, 'GRAND TOTAL', 1, 0, 'C', true);
	$pdf->Cell(24, 6, $totalGW, 1, 0, 'C', true);
	$pdf->Cell(20, 6, $totalStone, 1, 0, 'C', true);
	$pdf->Cell(20, 6, $totalNW, 1, 0, 'C', true);
	$pdf->Cell(16, 6, round((( $totalGA / $totalNW ) / $trans['rate']) * 100,2)."%", 1, 0, 'C', true);
	$pdf->Cell(30, 6, numberSystem($totalGA), 1, 1, 'C', true);
	/* --------------    END OF - ORNAMENT LIST   --------------- */
	
	$pdf->ln(1);
	$remPage = 273 - $pdf->GetY();
	if($remPage < 73){
		$pdf->AddPage();
	}
	$currentY = $pdf->GetY();
	
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->Cell(118, 5, "TERMS & CONDITIONS", 1, 1, 'C');
	$pdf->MultiCell(118, 3, file_get_contents('terms.txt'), 1, 1, 'L');
	$pdf->Cell(118, 38, $pdf->Image($termsAndConditionImage, $pdf->GetX(), $pdf->GetY(), 118, 38), 1, 1);
	
	$pdf->SetFont('Arial', 'B', 8);
	$pdf->SetXY(129, $currentY);
	$pdf->Cell(36, 8, "GROSS AMOUNT", 1, 2, 'R');
	$pdf->Cell(36, 8, "MARGIN", 1, 2, 'R');
	$pdf->Cell(36, 8, "NET AMOUNT", 1, 2, 'R');
	$pdf->Cell(36, 8, "RELEASE", 1, 2, 'R');
	$pdf->Cell(36, 8, "AMOUNT PAID", 1, 2, 'R');
	$pdf->Cell(36, 32, $pdf->Image($thumb, $pdf->GetX(), $pdf->GetY(), 36, 32), 1, 2);
	$pdf->Cell(36, 7, "THUMB IMPRESSION", 1, 0, 'C', true);
	
	$pdf->SetXY(165, $currentY);
	$pdf->Cell(35, 8, numberSystem($totalGA), 1, 2, 'C');
	$pdf->Cell(35, 8, numberSystem($trans['margin']), 1, 2, 'C');
	$pdf->Cell(35, 8, numberSystem($trans['netA']), 1, 2, 'C');
	$pdf->Cell(35, 8, numberSystem($trans['releases']), 1, 2, 'C');
	$pdf->Cell(35, 8, numberSystem($trans['amountPaid']), 1, 2, 'C');
	$pdf->Cell(35, 32, $pdf->Image($sign, $pdf->GetX(), $pdf->GetY(), 35, 32), 1, 2);
	$pdf->Cell(35, 7, "CUSTOMER SIGNATURE", 1, 1, 'C', true);
	
	$pdf->ln(1);
	$pdf->SetFont('Arial', 'B', '8');
	$pdf->Cell(190, 6, 'AMOUNT IN WORDS : ' . (($trans['amountPaid'] <= 0) ? 'NIL' : strtoupper(amountToWords($trans['amountPaid']))), 1, 0, 'C');
	$lastY = $pdf->GetY();
	
	$filename = "INVOICE_" . $customer['customerId'] . "-" . date('d-m-Y') . ".pdf";
	$pdf->Output($filename, 'I');
?>	

