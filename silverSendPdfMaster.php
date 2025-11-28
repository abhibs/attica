<?php
session_start();
include("dbConnection.php");
require('fpdf/fpdf.php');
$branchName=$_SESSION['b'];
$d1=$_SESSION['d1'];
$d2=$_SESSION['d2'];
//echo $branchName;
$date=date('Y-m-d');
$sql3="select * from branch where branchName='$branchName'";
	   $res3=mysqli_query($con,$sql3);
	   $row3=mysqli_fetch_array($res3);
	   $branch1=$row3['branchId'];
$billId=$branch1;
//echo $billId;
$sql1="select sta,staDate from trans where sta='Checked' AND staDate='$date'";
$res1=mysqli_query($con,$sql1);
$count1=mysqli_num_rows($res1);

$column_slno="";
$id="";
$dat="";
$grossW="";
$netW="";
$grossA="";
$netA="";
$st="";
$bid="";
$pur="";
$gold="";
$action="";
$remarks="";
$purity="";
$query=mysqli_query($con,"SELECT * FROM trans where date between '$d1' and '$d2' AND metal='Silver' AND branchId='$billId' AND sta = 'Checked'");
$count=mysqli_num_rows($query);
 
 $sql="select billId, sta, date, SUM(netW) as netW,SUM(grossW) as grossW , SUM(netA) as netA,SUM(grossA) as grossA,COUNT(id) as id from trans where date between '$d1' and '$d2' AND metal='Silver' AND branchId='$billId' AND sta = 'Checked'";
	   $res=mysqli_query($con,$sql);
	   $row=mysqli_fetch_array($res);
$grw=$row['grossW'];	
$new=$row['netW'];  
$gra=$row['grossA'];
$nea=$row['netA']; 
$co=$row['id'];
for($j=1;$j<=$count;$j++)
{
$row=mysqli_fetch_array($query);
$purity1=round($row['purity'],2);	
$purity=$purity.$purity1."\n";
$column_slno=$column_slno.$j."\n";
$id1=$row['id'];
$id=$id.$id1."\n";
$bid1=$row['billId'];
$bid=$bid.$bid1."\n";
$dat1=$row['date'];
$dat=$dat.$dat1."\n";
$grossW1=round($row['grossW'],2);
$grossW=$grossW.$grossW1."\n";
$netW1=round($row['netW'],2);
$netW=$netW.$netW1."\n";
$grossA1=round($row['grossA'],0);
$grossA=$grossA.$grossA1."\n";
$netA1=round($row['netA'],0);
$netA=$netA.$netA1."\n";
$st1=$row['status'];
$st=$st.$st1."\n";   
$bill=$row['billId'];
$branch=$_SESSION['branchCode'];
$sql13="select priceId from branch where branchId='$billId'";
$res13=mysqli_query($con,$sql13);
$row13=mysqli_fetch_array($res13);
$priceId=$row13['priceId'];
if($priceId==1 )
	{
		$price='Bangalore';
	}
	else if($priceId==2)
	{
		$price='Karnataka';
	}
	else if($priceId==3)
	{
    
		$price='Andhra Pradesh';
	
	}
	else if($priceId==4)
	{
    
		$price='Telangana';
	
	}
	else if($priceId==5)
	{
    
		$price='Pondicherry';
	
	}
	else if($priceId==6)
	{
    
		$price='Tamilnadu';
	
	}
	//echo $price;
$sql20="select cash from gold where type='Silver' AND city='$price' order by id DESC";
					   $res20=mysqli_query($con,$sql20);
					   $row20=mysqli_fetch_array($res20);	
					   $gold1=$row20['cash'];
					$gold=$gold.$gold1."\n"; 
					   					   
$action1="Move to HO";
$action=$action.$action1."\n";
$remarks1=" ";
$remarks=$remarks.$remarks1."\n";
$pur="";
$pur=($gra/$new)/$row20['cash']*100;

}
class PDF extends FPDF
{
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
if(!empty($_FILES["file"]))
  {
$uploaddir = "logo/";
$nm = $_FILES["file"]["name"];
$random = rand(1,99);
move_uploaded_file($_FILES["file"]["tmp_name"], $uploaddir.$random.$nm);
$this->Image($uploaddir.$random.$nm,10,10,20);
unlink($uploaddir.$random.$nm);
}
 
$this->SetFont('Arial','B',12);
$this->Ln(1);
}
function Footer()
{
$this->SetY(-15);
$this->SetFont('Arial','I',8);
//$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
function ChapterTitle($num, $label)
{
$this->SetFont('Arial','B',12);
$this->SetFillColor(224,235,255);
$this->Cell(0,6,"$num :$label",0,1,'L',true);
$this->Ln(0);
}
function ChapterTitle2($num, $label)
{
$this->SetFont('Arial','B',12);
$this->SetFillColor(224,235,255);
$this->Cell(0,6,"$num :$label",0,1,'L',true);
$this->Ln(0);
}
function ChapterBody($file)
{
	

    // Read text file
    $txt = file_get_contents($file);
    // Font
    $this->SetFont('Arial','',11);
    // Output text in a 6 cm width column
	
    $this->MultiCell(800,5,$txt,'R');
	
   
    // Mention
    $this->SetFont('','I');
   
}
function PrintChapter($file)
{
    // Add chapter
  
  
    $this->ChapterBody($file);
}
function PrintChapter1($file)
{
    // Add chapter
  
  
    $this->ChapterBody1($file);
}
function ChapterBody1($file)
{
	

    // Read text file
    $txt = file_get_contents($file);
    // Font
	$Y_Table18 =94;
   $this->SetY($Y_Table18);
    $this->SetX(9);
    $this->SetFont('Arial','',8);
    // Output text in a 6 cm width column
	
    $this->MultiCell(120,3,$txt,1,1,'L');
    $this->SetFont('','I');
   
}
function WriteHTML($html)
{
    // HTML parser
    $html = str_replace("\n",' ',$html);
    $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            // Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,$e);
        }
        else
        {
            // Tag
            if($e[0]=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                // Extract attributes
                $a2 = explode(' ',$e);
                $tag = strtoupper(array_shift($a2));
                $attr = array();
                foreach($a2 as $v)
                {
                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                        $attr[strtoupper($a3[1])] = $a3[2];
                }
                $this->OpenTag($tag,$attr);
            }
        }
    }
}
function PutLink($URL, $txt)
{
    // Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}
function OpenTag($tag, $attr)
{
    // Opening tag
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,true);
    if($tag=='A')
        $this->HREF = $attr['HREF'];
    if($tag=='BR')
        $this->Ln(5);
}

function CloseTag($tag)
{
    // Closing tag
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF = '';
}

function SetStyle($tag, $enable)
{
    // Modify style and select corresponding font
    $this->$tag += ($enable ? 1 : -1);
    $style = '';
    foreach(array('B', 'I', 'U') as $s)
    {
        if($this->$s>0)
            $style .= $s;
    }
    $this->SetFont('',$style);
}
function CellFit($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $scale=false, $force=true)
    {
        //Get string width
        $str_width=$this->GetStringWidth($txt);

        //Calculate ratio to fit cell
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $ratio = ($w-$this->cMargin*2)/$str_width;

        $fit = ($ratio < 1 || ($ratio > 1 && $force));
        if ($fit)
        {
            if ($scale)
            {
                //Calculate horizontal scaling
                $horiz_scale=$ratio*100.0;
                //Set horizontal scaling
                $this->_out(sprintf('BT %.2F Tz ET', $horiz_scale));
            }
            else
            {
                //Calculate character spacing in points
                $char_space=($w-$this->cMargin*2-$str_width)/max($this->MBGetStringLength($txt)-1, 1)*$this->k;
                //Set character spacing
                $this->_out(sprintf('BT %.2F Tc ET', $char_space));
            }
            //Override user alignment (since text will fill up cell)
            $align='';
        }

        //Pass on to Cell method
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);

        //Reset character spacing/horizontal scaling
        if ($fit)
            $this->_out('BT '.($scale ? '100 Tz' : '0 Tc').' ET');
    }

    //Cell with horizontal scaling only if necessary
    function CellFitScale($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w, $h, $txt, $border, $ln, $align, $fill, $link, true, false);
    }
 
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFillColor(224,235,255);
$Y_Fields_Name_position = 44;
$Y_Table_Position = 51;
$pdf->SetY(8);
$pdf->setX(77.5);
$pdf->Cell(55,25, $pdf->Image('attica.jpg', $pdf->GetX(),$pdf->GetY(),55),0,'C');
$pdf->SetY(31);
$pdf->SetX(9);
$pdf->SetFont('Arial','B','20');

$pdf->MultiCell(195,10,'Silver Send Report:'.$branchName,'0','C');
$pdf->SetY($Y_Fields_Name_position);
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(0,0,0);
$pdf->setX(9);
$pdf->Cell(9,7,'Sno',1,0,'C',1);
$pdf->SetX(18);
$pdf->Cell(14,7,'Bill Id',1,0,'C',1);
$pdf->SetX(32);
$pdf->Cell(20,7,'Bill Date',1,0,'C',1);
$pdf->SetX(52);
$pdf->Cell(26,7,'Gross Weight',1,0,'C',1);
$pdf->SetX(78);
$pdf->Cell(22,7,'Net Weight',1,0,'C',1);
$pdf->SetX(100);
$pdf->Cell(28,7,'Gross Amount',1,0,'C',1);
$pdf->SetX(128);
$pdf->Cell(23,7,'Net Amount',1,0,'C',1);
$pdf->SetX(151);
$pdf->Cell(14,7,'Purity',1,0,'C',1);
$pdf->SetX(165);
$pdf->Cell(20,7,'Silver Rate',1,0,'C',1);


$pdf->SetX(185);
$pdf->Cell(18,7,'Remarks',1,0,'C',1);
$pdf->Ln();
$pdf->SetFont('Arial','',9);
$pdf->SetY($Y_Table_Position);
$pdf->SetX(9);
$pdf->MultiCell(9,6,$column_slno,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(18);
$pdf->MultiCell(14,6,$bid,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(32);
$pdf->MultiCell(20,6,$dat,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(52);
$pdf->MultiCell(26,6,$grossW,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(78);
$pdf->MultiCell(22,6,$netW,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(100);
$pdf->MultiCell(28,6,$grossA,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(128);
$pdf->MultiCell(23,6,$netA,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(151);
$pdf->MultiCell(14,6,$purity,1,'C');
$pdf->SetY($Y_Table_Position);
$pdf->SetX(165);
$pdf->MultiCell(20,6,$gold,1,'C');

$pdf->SetY($Y_Table_Position);
$pdf->SetX(185);
$pdf->MultiCell(18,6,$remarks,1,'C');
//$pdf->SetY($Y_Table_Position);

//$n=27;
$Y_Table8 =$Y_Table_Position + ($count * 6);
$pdf->SetY($Y_Table8);
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(0,0,0);
$pdf->setX(9);
$pdf->Cell(43,7,'Total:',1,0,'R',1);
$pdf->SetX(52);
$pdf->Cell(26,7,round($grw,2),1,0,'C');
$pdf->SetX(78);
$pdf->Cell(22,7,round($new,2),1,0,'C');
$pdf->SetX(100);
$pdf->Cell(28,7,round($gra,0),1,0,'C');
$pdf->SetX(128);
$pdf->Cell(23,7,round($nea,0),1,0,'C');
$pdf->SetX(151);
$pdf->Cell(14,7,round($pur,2).'%',1,0,'C');
$pdf->SetX(165);
$pdf->Cell(20,7,'Packets:',1,0,'R',1);
$pdf->SetX(185);
$pdf->Cell(18,7,$co,1,0,'C');

$Y_Table9 =$Y_Table8 + 9;
$pdf->SetY($Y_Table9);
$pdf->SetX(9);
$pdf->SetFont('Arial','B','10');
$pdf->MultiCell(55,5,'B M Name With Sign:','0','L');
$pdf->SetY($Y_Table9);
$pdf->MultiCell(155,5,'Silver Carrying Person:','0','R');
$pdf->SetY($Y_Table9);
$Y_Table10 =$Y_Table9 + 30;
$pdf->SetY($Y_Table10);
$pdf->SetX(9);
$pdf->MultiCell(55,5,'ABM Name With Sign:','0','L');
$pdf->SetY($Y_Table10);
$pdf->MultiCell(155,5,'Tare Weight:','0','R');
$pdf->SetY($Y_Table10);
$Y_Table11 =$Y_Table9 + 15;
$pdf->SetY($Y_Table11);
$pdf->SetX(9);
$pdf->MultiCell(55,5,'DATE: '.$date,'0','L');
$pdf->SetY($Y_Table11);
$pdf->MultiCell(155,5,'Place:','0','R');
$pdf->SetY($Y_Table11);
$filename= "SilverSendReport".date('d-m-Y').".pdf";
$pdfdoc =$pdf->Output($filename,'I');
//echo $pur;
?>