<?php	
	date_default_timezone_set('Asia/Calcutta');
	include("dbConnection.php");
	require('fpdf/fpdf.php');
	
	$invoiceId = base64_decode($_GET['id']);
// 	$invoiceId = $_GET['id'];
	$pledge_bill = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pledge_bill WHERE id='$invoiceId'"));
	$plege_ornaments = mysqli_query($con, "SELECT * FROM pledge_ornament WHERE invoiceId='$pledge_bill[invoiceId]' AND date='$pledge_bill[date]'");	
	$branch = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName, addr, city, state, pincode, gst FROM branch WHERE branchId='$pledge_bill[branchId]'"));
	
	$branchString = "Branch Name : ".$branch['branchName']." ( ".$pledge_bill['branchId']." )\nPh No : 8880300300 , GSTIN : ".$branch['gst'];
	$customerImage = ($pledge_bill['customerImage'] == "" || !is_file('PledgeDocuments/'.$pledge_bill['customerImage'])) ? "images/images.jpg" : 'PledgeDocuments/'.$pledge_bill['customerImage'];
	$ornamentImage = ($pledge_bill['ornamentImage'] == "" || !is_file('PledgeDocuments/'.$pledge_bill['ornamentImage'])) ? "images/jewelry.jpg" : 'PledgeDocuments/'.$pledge_bill['ornamentImage'];
	
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
	
	$pdf->SetFont('Arial', '', '11');
	$pdf->Image('images/atticalogo.png', 10, 12, 50, 18, 'PNG');
	$pdf->SetXY(70, 12);
	$pdf->MultiCell(0, 9, $branchString, 0, 'L', false);
	
	$pdf->SetXY(10, 35);
	$pdf->SetFillColor(118, 1, 7);
	$pdf->SetTextColor(255, 255, 255);
	$pdf->SetFont('Arial', 'B', '10');
	$pdf->Cell(0, 5, "GOLD PLEDGE RECEIPT", 0, 1, "C", true);
	
	$pdf->ln(5);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->SetFont('Arial', '', '10');
	$pdf->Cell(30, 8, "Pledge No", 1, 0, "L");
	$pdf->Cell(90, 8, $pledge_bill['billId'], 1, 1, "L");
	$pdf->Cell(30, 8, "Date & Time", 1, 0, "L");
	$pdf->Cell(90, 8, date("d-m-Y", strtotime($pledge_bill['date']))."  ".date("h:i", strtotime($pledge_bill['time'])), 1, 1, "L");
	$pdf->Cell(30, 8, "Name", 1, 0, "L");
	$pdf->Cell(90, 8, $pledge_bill['name'], 1, 1, "L");
	$pdf->Cell(30, 8, "Contact", 1, 0, "L");
	$pdf->Cell(90, 8, $pledge_bill['contact'], 1, 1, "L");	
	
	$pdf->SetXY(130, 45);
	$pdf->Cell(35, 32, $pdf->Image($customerImage, 130, 45, 35, 32, 'JPG'), 1, false);
	$pdf->SetXY(165, 45);
	$pdf->Cell(35, 32, $pdf->Image($ornamentImage, 165, 45, 35, 32, 'JPG'), 1, false);	
	
	$pdf->SetXY(10, 77);
	$pdf->MultiCell(0, 8, "Address : ".$pledge_bill['address'], 1, "L");
	$pdf->Cell(25, 8, "City", 1, 0, "L");
	$pdf->Cell(70, 8, $pledge_bill['city'], 1, 0, "L");
	$pdf->Cell(25, 8, "Pincode", 1, 0, "L");
	$pdf->Cell(70, 8, $pledge_bill['pincode'], 1, 1, "L");
	$pdf->Ln(5);
	
	$pdf->SetFont('Arial', 'B', '10');
	$pdf->Cell(15, 8, "Sl No", 1, 0, "C");
	$pdf->Cell(65, 8, "Ornament Type", 1, 0, "C");
	$pdf->Cell(20, 8, "Count", 1, 0, "C");
	$pdf->Cell(30, 8, "Gross Weight", 1, 0, "C");
	$pdf->Cell(30, 8, "Stone Weight", 1, 0, "C");
	$pdf->Cell(30, 8, "Net Weight", 1, 1, "C");
	$pdf->SetFont('Arial', '', '10');
	$i = 1;
	$total_gross = 0;
	$total_stone = 0;
	while($row = mysqli_fetch_assoc($plege_ornaments)){
		$pdf->Cell(15, 8, $i, 1, 0, "C"); 
		$pdf->Cell(65, 8, $row['ornamentType'], 1, 0, "C");
		$pdf->Cell(20, 8, $row['count'], 1, 0, "C");
		$pdf->Cell(30, 8, $row['grossW'], 1, 0, "C");
		$pdf->Cell(30, 8, $row['stoneW'], 1, 0, "C");
		$pdf->Cell(30, 8, $row['grossW'] - $row['stoneW'], 1, 1, "C");
		
		$i++;
		$total_gross += $row['grossW'];
		$total_stone += $row['stoneW'];
	}
	$pdf->Cell(100, 8, "Total", 1, 0, "C");
	$pdf->Cell(30, 8, $total_gross, 1, 0, "C");
	$pdf->Cell(30, 8, $total_stone, 1, 0, "C");
	$pdf->Cell(30, 8, $total_gross - $total_stone, 1, 1, "C");
	
	$pdf->Cell(100, 8, "Notes :", "L", 0, "L");
	$pdf->Cell(40, 8, "Gross Weight", 1, 0, "C");
	$pdf->Cell(0, 8, $total_gross, 1, 1, "C");
	$pdf->Cell(100, 8, "", "L", 0, "C");
	$pdf->Cell(40, 8, "Net Weight", 1, 0, "C");
	$pdf->Cell(0, 8, $total_gross - $total_stone, 1, 1, "C");
	$pdf->Cell(100, 8, "", "L", 0, "C");
	$pdf->Cell(40, 8, "Rate of Interest", 1, 0, "C");
	$pdf->Cell(0, 8, $pledge_bill['rate'], 1, 1, "C");
	$pdf->Cell(100, 8, "", "L", 0, "C");
	$pdf->Cell(40, 8, "Service Charge", 1, 0, "C");
	$pdf->Cell(0, 8, "0 %", 1, 1, "C");
	$pdf->Cell(100, 8, "", "LB", 0, "C");
	$pdf->Cell(40, 8, "Pledge Amount", 1, 0, "C");
	$pdf->Cell(0, 8, "Rs. ".numberSystem($pledge_bill['amount']), 1, 1, "C");
	
	$pdf->SetFont('Arial', 'B', '8');
	$pdf->MultiCell(0, 10, 'AMOUNT IN WORDS : ' . (($pledge_bill['amount'] <= 0) ? 'NIL' : strtoupper(amountToWords($pledge_bill['amount']))), 1, 'C');
	
	$pdf->Ln(5);
	$pdf->SetFont('Arial', 'B', '10');
	$pdf->Cell(95, 10, "BM Signature :", 0, 0, "L");
	$pdf->Cell(95, 10, "Customer Signature :", 0, 0, "L");
	
	$filename = "PLEDGE_INVOICE_".date('d-m-Y') . ".pdf";
	$pdf->Output($filename, 'I');	
?>