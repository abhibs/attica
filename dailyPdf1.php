<?php
	session_start();
	include("dbConnection.php");
	require('fpdf/fpdf.php');
	
	$branchId = $_GET['branch'];
	$date = $_GET['date'];
	
	$closing = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM closing WHERE branchId='$branchId' AND date='$date' ORDER BY closingID DESC LIMIT 1"));
	$one = ($closing['one'] == '') ? 0 : $closing['one'] * 2000;
	$two = ($closing['two'] == '') ? 0 : $closing['two'] * 500;
	$three = ($closing['three'] == '') ? 0 : $closing['three'] * 200;
	$four = ($closing['four'] == '') ? 0 : $closing['four'] * 100;
	$five = ($closing['five'] == '') ? 0 : $closing['five'] * 50;
	$six = ($closing['six'] == '') ? 0 : $closing['six'] * 20;
	$seven = ($closing['seven'] == '') ? 0 : $closing['seven'] * 10;
	$eight = ($closing['eight'] == '') ? 0 : $closing['eight'] * 5;
	$nine = ($closing['nine'] == '') ? 0 : $closing['nine'] * 2;
	$ten = ($closing['ten'] == '') ? 0 : $closing['ten'];
	
	$branch = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName FROM branch WHERE branchId='$branchId'"));
	$tranfer = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(transferAmount) AS transfer FROM trare WHERE branchId='$branchId' AND date='$date' AND status='Approved'"));
	$received = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(transferAmount) AS request FROM trare WHERE branchTo='$branch[branchName]' AND date='$date' AND status='Approved'"));
	
	$pdf = new FPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFillColor(224,235,255);
	
	$currentY = $pdf->getY();
	$pdf->SetFont('Arial','B',11);
	$pdf->Cell(55, 16, 'DAILY CLOSING REPORT', 1, 1, 'L', true);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(55, 16, 'BRANCH ID : '.$branchId, 1, 1, 'L', true);
	
	$pdf->SetXY(65, $currentY);
	$pdf->Cell(65,32, $pdf->Image('attica-gold-pvt-ltd.png', $pdf->GetX(), $pdf->GetY(),65),1, 'C', false );
	
	$pdf->SetXY(130, $currentY);	
	$pdf->Cell(0, 16, $branch['branchName'], 1, 2, 'L', true);
	$pdf->Cell(0, 16, 'DATE & TIME : '.$closing['date']." / ".$closing['time'], 1, 2, 'L', true);
	$pdf->ln(4);
	
	$pdf->Cell(45, 10, 'Total Amount for the day', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['totalAmount'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Total Transactions', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['transactions'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'TotalTransactionAmount', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['transactionAmount'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Actual Net Amount', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['netAG'] + $closing['netAS'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'Total Expense', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['expenses'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Balance Amount', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['balance'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'Gross Weight', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['grossWG'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Net Weight', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['netWG'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'Gross Amount', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['grossAG'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Net Amount', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['netAG'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'Gross Weight Silver', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['grossWS'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Net Weight Silver', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['netWS'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'Gross Amount Silver', 1, 0, 'L');
	$pdf->Cell(45, 10, $closing['grossAS'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Net Amount Silver', 1, 0, 'L');
	$pdf->Cell(0, 10, $closing['netAS'], 1, 1, 'L');
	
	$pdf->Cell(45, 10, 'Funds Received', 1, 0, 'L');
	$pdf->Cell(45, 10, $received['request'], 1, 0, 'L');
	$pdf->Cell(45, 10, 'Funds Transfered', 1, 0, 'L');
	$pdf->Cell(0, 10, $tranfer['transfer'], 1, 1, 'L');
	
	$pdf->ln(4);
	$pdf->Cell(45, 10, '2000 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['one'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $one, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '500 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['two'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $two, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '200 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['three'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $three, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '100 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['four'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $four, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '50 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['five'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $five, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '20 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['six'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $six, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '10 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['seven'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $seven, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '5 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['eight'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $eight, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '2 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['nine'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $nine, 1, 1, 'R');
	
	$pdf->Cell(45, 10, '1 ', 1, 0, 'R');
	$pdf->Cell(10, 10, 'X', 1, 0, 'C');
	$pdf->Cell(45, 10, $closing['ten'], 1, 0, 'R');
	$pdf->Cell(10, 10, '=', 1, 0, 'C');
	$pdf->Cell(0, 10, $ten, 1, 1, 'R');
	
	$pdf->Cell(110, 12, 'TOTAL DENOMINATION AMOUNT', 1, 0, 'C');
	$pdf->Cell(0, 12, $closing['total'], 1, 1, 'R');
	
	$pdf->Cell(110, 12, 'DIFFERENCE IN DENOMINATIONS', 1, 0, 'C');
	$pdf->Cell(0, 12, $closing['diff'], 1, 1, 'R');
	
	$filename="bill".date('d-m-Y').".pdf";
	$pdfdoc =$pdf->Output($filename,'I');
	
?>