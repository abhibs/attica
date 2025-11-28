<?php
	session_start();
	date_default_timezone_set("Asia/Kolkata");
	include("dbConnection.php");
	$branchId = $_SESSION['branchCode'];
	$date = date('Y-m-d');
	
	$reportData = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(id) as billCount FROM trans WHERE staDate='$date' AND sta='Checked' AND branchId='$branchId' AND status='Approved' AND metal='Silver'"));
	
	if($reportData['billCount'] > 0){
		require('fpdf/fpdf.php');
		
		$branchData = mysqli_fetch_assoc(mysqli_query($con,"SELECT b.branchName,e.empId,e.name FROM branch b,users u,employee e WHERE b.branchId='$branchId' AND b.branchId=u.branch AND u.employeeId=e.empId"));
		
		class PDF extends FPDF{
			
			private $branchName;
			private $manager;
			private $employeeID;
			private $date;
			
			protected $ProcessingTable=false;
			protected $aCols=array();
			protected $TableX;
			protected $HeaderColor;
			protected $RowColors;
			protected $ColorIndex;
			
			protected $B;
			protected $I;
			protected $U;
			protected $HREF;
			protected $fontList;
			protected $issetfont;
			protected $issetcolor;
			
			function __construct($orientation='P', $unit='mm', $format='A4')
			{
				//Call parent constructor
				parent::__construct($orientation,$unit,$format);
				
				//Initialization
				$this->B=0;
				$this->I=0;
				$this->U=0;
				$this->HREF='';
				
				$this->tableborder=0;
				$this->tdbegin=false;
				$this->tdwidth=0;
				$this->tdheight=0;
				$this->tdalign="L";
				$this->tdbgcolor=false;
				
				$this->oldx=0;
				$this->oldy=0;
				
				$this->fontlist=array("arial","times","courier","helvetica","symbol");
				$this->issetfont=false;
				$this->issetcolor=false;
			}
			function Header()
			{		
				$this->SetFont('Arial','B',15);
				$this->line(10, 5, 210-10, 5);
				$this->Image("attica.jpg",10,6,60,40);
				$this->Cell(80);
				
				$this->Cell(40,10,'SILVER SEND REPORT',0,2,'L');
				$this->Cell(0,10,$this->branchName,0,2,'L');
				$this->SetFontSize(10);
				$this->Cell(0,10,'BM : '.$this->manager.' ('.$this->employeeID.')',0,2,'L');
				$this->Cell(0,10,'Date : '.$this->date,0,2,'L');
				$this->line(10, 53, 210-10, 53);
				$this->ln(5);
				
				if($this->ProcessingTable)
        		$this->TableHeader();
			}
			function Footer()
			{
				$this->SetY(-15);
				$this->SetFont('Arial','I',8);
				$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
			}
			
			/*  MULTI PAGE TABLE */
			function TableHeader()   // CHANGE HEADER ATTRIBUTES HERE
			{
				$this->SetFont('Arial','B',8);
				$this->SetX($this->TableX);
				$fill=!empty($this->HeaderColor);
				if($fill)
				$this->SetFillColor($this->HeaderColor[0],$this->HeaderColor[1],$this->HeaderColor[2]);
				foreach($this->aCols as $col)
				$this->Cell($col['w'],8,$col['c'],1,0,'C',$fill);
				$this->Ln();
			}
			
			function Row($data)		// CHANGE TABLE ROW ATTRIBUTES HERE
			{
				$this->SetX($this->TableX);
				$ci=$this->ColorIndex;
				$fill=!empty($this->RowColors[$ci]);
				if($fill)
				$this->SetFillColor($this->RowColors[$ci][0],$this->RowColors[$ci][1],$this->RowColors[$ci][2]);
				foreach($this->aCols as $col)
				$this->Cell($col['w'],7,$data[$col['f']],1,0,$col['a'],$fill);
				$this->Ln();
				$this->ColorIndex=1-$ci;
			}
			
			function CalcWidths($width, $align)
			{
				// Compute the widths of the columns
				$TableWidth=0;
				foreach($this->aCols as $i=>$col)
				{
					$w=$col['w'];
					if($w==-1)
					$w=$width/count($this->aCols);
					elseif(substr($w,-1)=='%')
					$w=$w/100*$width;
					$this->aCols[$i]['w']=$w;
					$TableWidth+=$w;
				}
				// Compute the abscissa of the table
				if($align=='C')
				$this->TableX=max(($this->w-$TableWidth)/2,0);
				elseif($align=='R')
				$this->TableX=max($this->w-$this->rMargin-$TableWidth,0);
				else
				$this->TableX=$this->lMargin;
			}
			
			function AddCol($field=-1, $width=-1, $caption='', $align='L')
			{
				// Add a column to the table
				if($field==-1)
				$field=count($this->aCols);
				$this->aCols[]=array('f'=>$field,'c'=>$caption,'w'=>$width,'a'=>$align);
			}
			
			function Table($link, $query, $prop=array())
			{
				// Execute query
				$res=mysqli_query($link,$query) or die('Error: '.mysqli_error($link)."<br>Query: $query");
				// Add all columns if none was specified
				if(count($this->aCols)==0)
				{
					$nb=mysqli_num_fields($res);
					for($i=0;$i<$nb;$i++)
					$this->AddCol();
				}
				// Retrieve column names when not specified
				foreach($this->aCols as $i=>$col)
				{
					if($col['c']=='')
					{
						if(is_string($col['f']))
						$this->aCols[$i]['c']=ucfirst($col['f']);
						else
						$this->aCols[$i]['c']=ucfirst(mysqli_fetch_field_direct($res,$col['f'])->name);
					}
				}
				// Handle properties
				if(!isset($prop['width']))
				$prop['width']=0;
				if($prop['width']==0)
				$prop['width']=$this->w-$this->lMargin-$this->rMargin;
				if(!isset($prop['align']))
				$prop['align']='C';
				if(!isset($prop['padding']))
				$prop['padding']=$this->cMargin;
				$cMargin=$this->cMargin;
				$this->cMargin=$prop['padding'];
				if(!isset($prop['HeaderColor']))
				$prop['HeaderColor']=array();
				$this->HeaderColor=$prop['HeaderColor'];
				if(!isset($prop['color1']))
				$prop['color1']=array();
				if(!isset($prop['color2']))
				$prop['color2']=array();
				$this->RowColors=array($prop['color1'],$prop['color2']);
				// Compute column widths
				$this->CalcWidths($prop['width'],$prop['align']);
				// Print header
				$this->TableHeader();
				// Print rows
				$this->SetFont('Arial','',9);
				$this->ColorIndex=0;
				$this->ProcessingTable=true;
				while($row=mysqli_fetch_array($res))
				$this->Row($row);
				$this->ProcessingTable=false;
				$this->cMargin=$cMargin;
				$this->aCols=array();
			}
			
			// SET THE HEADER CUSTOM DATA
			function setHeaderData($branchName,$branchManager,$empID,$date){
				$this->branchName = $branchName;
				$this->manager = $branchManager;
				$this->employeeID = $empID;
				$this->date = $date;
			}
		}
		
		$pdf = new PDF();
		$pdf->AliasNbPages();
		$pdf->setHeaderData($branchData['branchName'],$branchData['name'],$branchData['empId'],$date);
		$pdf->SetFillColor(230, 255, 255);
		$pdf->AddPage();
		
		$pdf->Ln(2);
		
		$pdf->AddCol('billId',21,'billId','C');
		$pdf->AddCol('Bill_Date',22,'Bill Date','C');
		$pdf->AddCol('Gross_Weight',21,'Gross Weight','C');
		$pdf->AddCol('Net_Weight',21,'Net Weight','C');
		$pdf->AddCol('Gross_Amount',26,'Gross Amount','C');
		$pdf->AddCol('Net_Amount',26,'Net Amount','C');
		$pdf->AddCol('Purity',15,'Purity','C');	
		$pdf->AddCol('Rate',21,'Rate','C');	
		$pdf->AddCol('Margin',17,'Margin%','C');
		
		$prop = array('HeaderColor'=>array(230, 238, 255),'padding'=>1);
		$pdf->Table($con,"SELECT billId,date AS Bill_Date,
        ROUND(grossW,2) AS Gross_Weight,
        ROUND(netW,2) AS Net_Weight,
        grossA AS Gross_Amount,
        netA AS Net_Amount,
        ROUND(purity,2) AS Purity,
        rate AS Rate,
        ROUND(comm,2) AS Margin
		FROM trans
		WHERE staDate='$date' AND sta='Checked' AND metal='Silver' AND status='Approved' AND branchId='$branchId';",$prop);		
		
		$consQuery = mysqli_fetch_assoc(mysqli_query($con,"SELECT
        ROUND(SUM(grossW),2) AS Gross_Weight,
        ROUND(SUM(netW),2) AS Net_Weight,
        SUM(grossA) AS Gross_Amount,
        SUM(netA) AS Net_Amount,
        ROUND(SUM(margin),2) AS Margin,
		AVG(rate) AS rate
		FROM trans
		WHERE staDate='$date' AND sta='Checked' AND metal='Silver' AND status='Approved' AND branchId='$branchId'"));
		$PurityTot = ROUND((($consQuery['Gross_Amount']/$consQuery['Net_Weight'])/$consQuery['rate'])*100,2);
		$marginTot = ROUND(($consQuery['Margin']/$consQuery['Gross_Amount']) * 100,2);
		
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(43,7,'Total',1,0,'C',1);
		$pdf->Cell(21,7,$consQuery['Gross_Weight'],1,0,'C');
		$pdf->Cell(21,7,$consQuery['Net_Weight'],1,0,'C');
		$pdf->Cell(26,7,$consQuery['Gross_Amount'],1,0,'C');
		$pdf->Cell(26,7,$consQuery['Net_Amount'],1,0,'C');
		$pdf->Cell(15,7,$PurityTot,1,0,'C');
		$pdf->Cell(21,7,'Packets:'.$reportData['billCount'],1,0,'C',1);
		$pdf->Cell(17,7,$marginTot,1,1,'C');
		
		$pdf->Ln(5);
		$currentYpos = $pdf->getY();
		$remainingSize = 286.93 - $currentYpos;
		if($remainingSize < 50){
			$pdf->AddPage();
		}
		
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(31.66,10,'Date',1,0,'C');
		$pdf->Cell(31.66,10,$date,1,0,'C');
		$pdf->Cell(31.66,10,'Tare Weight',1,0,'C');
		$pdf->Cell(31.66,10,'',1,0,'C');
		$pdf->Cell(31.66,10,'Place',1,0,'C');
		$pdf->Cell(31.66,10,'',1,1,'C');
		
		$pdf->Cell(63.33,10,'BM Name With Sign',1,0,'C',1);
		$pdf->Cell(63.33,10,'ABM Name With Sign',1,0,'C',1);
		$pdf->Cell(63.33,10,'Silver Carrying Person',1,1,'C',1);
		$pdf->Cell(63.33,20,'',1,0,'C');
		$pdf->Cell(63.33,20,'',1,0,'C');
		$pdf->Cell(63.33,20,'',1,1,'C');
		
		$filename= "SilverSendReport".$date.".pdf";
		$pdfdoc =$pdf->Output($filename,'I');
		
	}
	else{
		echo "<script type='text/javascript'>alert('No Bills To Generate Report On ".$date."')</script>";
		echo "<script>open(location, '_self').close();</script>";
	}
?>