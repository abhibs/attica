<?php
session_start();
include("dbConnection.php");
require('fpdf/fpdf.php');
$branchName=$_GET['branch'];
$a=$_GET['a'];
$b=$_GET['b'];
$c=$_GET['c'];
$d=$_GET['d'];
$e=$_GET['e'];
$f=$_GET['f'];
$g=$_GET['g'];
$h=$_GET['h'];
$i=$_GET['i'];
$j=$_GET['j'];
if(isset($_GET['date']))
{
$date=$_GET['date'];
}
else
{
	$date=date('Y-m-d');
}
$sql="select * from branch where branchId='$branchName'";
$res=mysqli_query($con,$sql);
$row=mysqli_fetch_array($res);
$branchId=$row['branchId'];
$branchn=$row['branchName'];
$sql1="select * from closing where branchId='$branchId' AND date='$date'";
$res1=mysqli_query($con,$sql1);
$row1=mysqli_fetch_array($res1);
$time=$row1['time'];
$total=$row1['totalAmount'];
$totalTransaction=$row1['transactions'];
$totalTamount=$row1['transactionAmount'];
$totalExpense=$row1['expenses'];
$balanceamount=$row1['balance'];
$netW=$row1['netWG'];
$grossW=$row1['grossWG'];
$netA=$row1['netAG'];
$grossA=$row1['grossAG'];
$grossWS=$row1['grossWS'];
$netAS=$row1['netAS'];
$netWS=$row1['netWS'];
$grossAS=$row1['grossAS'];
$actual=$netA+$netAS;
$one=$row1['one'];
$two=$row1['two'];
$three=$row1['three'];
$four=$row1['four'];
$five=$row1['five'];
$six=$row1['six'];
$seven=$row1['seven'];
$eight=$row1['eight'];
$nine=$row1['nine'];
$ten=$row1['ten'];
$total1=$row1['total'];
$diff=$row1['diff'];
$sql17="select SUM(transferAmount) as transfer from trare where date='$date' AND branchId='$branchName'";
	   $res17=mysqli_query($con,$sql17);
	   $row17=mysqli_fetch_array($res17);
	   $transf=$row17['transfer'];
	   if($transf=="")
	   {
		   $transf=0;
	   }
	   else{
		  $transf=$transf; 
	   }
	   $sql18="select SUM(request) as requests from fund where date='$date' AND status='Approved' AND branch='$branchName' ";
	   $res18=mysqli_query($con,$sql18);
	   $row18=mysqli_fetch_array($res18);
	   $receive=$row18['requests'];
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
    $this->SetX(10);
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

 
}
 

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$Y_Fields_Name_position = 56.5;
//Table position, under Fields Name
$Y_Table_Position = 66.5;
$Y_Table8 =60;
$Y_Table7 =80;
$Y_Table2 =37;
$Y_Table3 =41.5;
$Y_Table4 =46.5;
$Y_Table5 =51.5;
$Y_Table6 =75;
$Y_Table1 =15;
$Y_Table12 =10;
$pdf->SetY($Y_Table12);
$pdf->setX(10);
$pdf->SetFillColor(224,235,255);
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(55,16,"DAILY CLOSING REPORT",1,1,'C',true);
$pdf->SetFont('Arial','B',10);
$pdf->MultiCell(55,16,"BRANCH ID: ".$branchName,1,1,'C',true);
$pdf->SetY($Y_Table12);
$pdf->setX(65);
$pdf->Cell(65,32, $pdf->Image('attica-gold-pvt-ltd.png', $pdf->GetX(), $pdf->GetY(),65),1, 'C', false );
$pdf->setX(130);
$pdf->SetFont('Arial','B',10);
$pdf->MultiCell(65,16,$branchn,1,1,'C',true);
$pdf->setX(130);
$pdf->MultiCell(65,16,"DATE & TIME: ".$date." / ".$time,1,1,'C',true);
$pdf->SetY($Y_Table12);
$Y_Table25=47;
$pdf->SetY($Y_Table25);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Total amount for the day','1','L');
$pdf->SetY($Y_Table25);
$pdf->SetX(55);
$pdf->Cell(45,10,$total,1,0,'L');
$pdf->SetY($Y_Table25);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Total Transactions",'1','L');
$pdf->SetY($Y_Table25);
$pdf->SetX(145);
$pdf->Cell(50,10,$totalTransaction,1,0,'L');
$Y_Table26=57;
$pdf->SetY($Y_Table26);
$pdf->setX(10);
$pdf->MultiCell(45,10,'TotalTransactionAmount','1','L');
$pdf->SetY($Y_Table26);
$pdf->SetX(55);
$pdf->Cell(45,10,$totalTamount,1,0,'L');
$pdf->SetY($Y_Table26);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Actual Net Amount",'1','L');
$pdf->SetY($Y_Table26);
$pdf->SetX(145);
$pdf->Cell(50,10,$actual,1,0,'L');
$Y_Table27=67;
$pdf->SetY($Y_Table27);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Total Expense','1','L');
$pdf->SetY($Y_Table27);
$pdf->SetX(55);
$pdf->Cell(45,10,$totalExpense,1,0,'L');
$pdf->SetY($Y_Table27);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Balance Amount",'1','L');
$pdf->SetY($Y_Table27);
$pdf->SetX(145);
$pdf->Cell(50,10,$balanceamount,1,0,'L');
$Y_Table28=77;
$pdf->SetY($Y_Table28);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Gross Weight','1','L');
$pdf->SetY($Y_Table28);
$pdf->SetX(55);
$pdf->Cell(45,10,$grossW,1,0,'L');
$pdf->SetY($Y_Table28);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Net Weight",'1','L');
$pdf->SetY($Y_Table28);
$pdf->SetX(145);
$pdf->Cell(50,10,round($netW,2),1,0,'L');
$Y_Table29=87;
$pdf->SetY($Y_Table29);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Gross Amount','1','L');
$pdf->SetY($Y_Table29);
$pdf->SetX(55);
$pdf->Cell(45,10,$grossA,1,0,'L');
$pdf->SetY($Y_Table29);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Net Amount:",'1','L');
$pdf->SetY($Y_Table29);
$pdf->SetX(145);
$pdf->Cell(50,10,$netA,1,0,'L');
$Y_Table30=97;
$pdf->SetY($Y_Table30);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Gross Weight Silver','1','L');
$pdf->SetY($Y_Table30);
$pdf->SetX(55);
$pdf->Cell(45,10,$grossWS,1,0,'L');
$pdf->SetY($Y_Table30);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Net Weight Silver",'1','L');
$pdf->SetY($Y_Table30);
$pdf->SetX(145);
$pdf->Cell(50,10,$netWS,1,0,'L');
$Y_Table31=107;
$pdf->SetY($Y_Table31);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Gross Amount Silver','1','L');
$pdf->SetY($Y_Table31);
$pdf->SetX(55);
$pdf->Cell(45,10,$grossAS,1,0,'L');
$pdf->SetY($Y_Table31);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Net Amount Silver",'1','L');
$pdf->SetY($Y_Table31);
$pdf->SetX(145);
$pdf->Cell(50,10,$netAS,1,0,'L');

$Y_Table78=117;
$pdf->SetY($Y_Table78);
$pdf->setX(10);
$pdf->MultiCell(45,10,'Funds Received','1','L');
$pdf->SetY($Y_Table78);
$pdf->SetX(55);
$pdf->Cell(45,10,$receive,1,0,'L');
$pdf->SetY($Y_Table78);
$pdf->SetX(100);
$pdf->MultiCell(45,10,"Funds Transfered",'1','L');
$pdf->SetY($Y_Table78);
$pdf->SetX(145);
$pdf->Cell(50,10,$transf,1,0,'L');
$Y_Table32=127;
$pdf->SetY($Y_Table32);
$pdf->setX(10);
$pdf->MultiCell(45,10,'2000 X','1','R');
$pdf->SetY($Y_Table32);
$pdf->SetX(55);
$pdf->Cell(90,10,$one,1,0,'C');
$pdf->SetY($Y_Table32);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$a,'1','L');
$pdf->SetY($Y_Table32);
$Y_Table33=137;
$pdf->SetY($Y_Table33);
$pdf->setX(10);
$pdf->MultiCell(45,10,'500 X','1','R');
$pdf->SetY($Y_Table33);
$pdf->SetX(55);
$pdf->Cell(90,10,$two,1,0,'C');
$pdf->SetY($Y_Table33);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$c,'1','L');
$pdf->SetY($Y_Table33);
$Y_Table34=147;
$pdf->SetY($Y_Table34);
$pdf->setX(10);
$pdf->MultiCell(45,10,'200 X','1','R');
$pdf->SetY($Y_Table34);
$pdf->SetX(55);
$pdf->Cell(90,10,$three,1,0,'C');
$pdf->SetY($Y_Table34);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$b,'1','L');
$pdf->SetY($Y_Table34);
$Y_Table35=157;
$pdf->SetY($Y_Table35);
$pdf->setX(10);
$pdf->MultiCell(45,10,'100 X','1','R');
$pdf->SetY($Y_Table35);
$pdf->SetX(55);
$pdf->Cell(90,10,$four,1,0,'C');
$pdf->SetY($Y_Table35);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$d,'1','L');
$pdf->SetY($Y_Table35);
$Y_Table36=167;
$pdf->SetY($Y_Table36);
$pdf->setX(10);
$pdf->MultiCell(45,10,'50 X','1','R');
$pdf->SetY($Y_Table36);
$pdf->SetX(55);
$pdf->Cell(90,10,$five,1,0,'C');
$pdf->SetY($Y_Table36);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$e,'1','L');
$pdf->SetY($Y_Table36);
$Y_Table37=177;
$pdf->SetY($Y_Table37);
$pdf->setX(10);
$pdf->MultiCell(45,10,'20 X','1','R');
$pdf->SetY($Y_Table37);
$pdf->SetX(55);
$pdf->Cell(90,10,$six,1,0,'C');
$pdf->SetY($Y_Table37);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$j,'1','L');
$pdf->SetY($Y_Table37);
$Y_Table38=187;
$pdf->SetY($Y_Table38);
$pdf->setX(10);
$pdf->MultiCell(45,10,'10 X','1','R');
$pdf->SetY($Y_Table38);
$pdf->SetX(55);
$pdf->Cell(90,10,$seven,1,0,'C');
$pdf->SetY($Y_Table38);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$f,'1','L');
$pdf->SetY($Y_Table38);
$Y_Table39=197;
$pdf->SetY($Y_Table39);
$pdf->setX(10);
$pdf->MultiCell(45,10,'5 X','1','R');
$pdf->SetY($Y_Table39);
$pdf->SetX(55);
$pdf->Cell(90,10,$eight,1,0,'C');
$pdf->SetY($Y_Table39);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$g,'1','L');
$pdf->SetY($Y_Table39);
$Y_Table40=207;
$pdf->SetY($Y_Table40);
$pdf->setX(10);
$pdf->MultiCell(45,10,'2 X','1','R');
$pdf->SetY($Y_Table40);
$pdf->SetX(55);
$pdf->Cell(90,10,$nine,1,0,'C');
$pdf->SetY($Y_Table40);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$h,'1','L');
$pdf->SetY($Y_Table40);
$Y_Table41=217;
$pdf->SetY($Y_Table41);
$pdf->setX(10);
$pdf->MultiCell(45,10,'1 X','1','R');
$pdf->SetY($Y_Table41);
$pdf->SetX(55);
$pdf->Cell(90,10,$ten,1,0,'C');
$pdf->SetY($Y_Table41);
$pdf->SetX(145);
$pdf->MultiCell(50,10,"= ".$i,'1','L');
$pdf->SetY($Y_Table41);
$Y_Table43=227;
$pdf->SetY($Y_Table43);
$pdf->setX(10);
$pdf->MultiCell(90,10,'TOTAL DENOMINATION AMOUNT','1','R');
$pdf->SetY($Y_Table43);
$pdf->SetX(100);
$pdf->Cell(95,10,$total1,1,0,'L');
$pdf->SetY($Y_Table43);
$Y_Table42=237;
$pdf->SetY($Y_Table42);
$pdf->setX(10);
$pdf->MultiCell(90,10,'DIFFERENCE IN DENOMINATIONS','1','R');
$pdf->SetY($Y_Table42);
$pdf->SetX(100);
$pdf->Cell(95,10,$diff,1,0,'L');
$pdf->SetY($Y_Table42);
$filename="bill".date('d-m-Y').".pdf";
$pdfdoc =$pdf->Output($filename,'I');

?>