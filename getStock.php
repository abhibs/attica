2025-08-26
2025-08-26
2025-08-26
<?php
$q = $_POST['data'];
include("dbConnection.php");
//$sql="select stock from inouts where code='$q' order by id DESC limit 1";

$sql="select ((SELECT SUM(quantity) FROM inouts WHERE status = 0 and code='$q')-(SELECT SUM(quantity) FROM inouts WHERE status = 1 and code='$q')) as quantity";
$res = mysqli_query($con,$sql);
$row = mysqli_fetch_array($res) ;
//$p=$row['stock'];
if($res==true)
{
	if($row['quantity'] == '')
	{
		$result = mysqli_query($con,"SELECT SUM(quantity) as quantity FROM inouts WHERE status = 0 and code='$q'");
		$rows = mysqli_fetch_array($result);
		$p=$rows['quantity'];
		echo $p;
	}
	else
	{
		$p=$row['quantity'];
		echo $p;
	}
}
?>
