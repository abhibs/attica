<?php
    require('fpdf/fpdf.php');
	$pdf = new FPDF('P', 'mm', 'A4');
	$pdf->AddPage(); 
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell(100, 16, "Hello, World!");
	$pdf->Output();
?>