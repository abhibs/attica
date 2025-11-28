<?php
	error_reporting(E_ERROR | E_PARSE);
	session_start();
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date=date('Y-m-d');
	$time=date("h:i:s");
    if(isset($_GET['id'])){
	    $id = $_GET['id'];
		$bill=$_POST['billId'];
	    $sql = mysqli_query($con,"DELETE FROM ornament WHERE ornamentId='$id'");
		header("Location:xaddTransaction.php");

		
	}
	else if(isset($_GET['rid'])){
	    $id = $_GET['rid'];
	    $sql = mysqli_query($con,"DELETE FROM ornament WHERE ornamentId='$id'");
		header("Location:xaddTransactionRelease.php");
	}
	
	
	
	if($_POST['type']=="physical"){
		
	    $id = $_POST['id'];
		$bill=$_POST['billId'];
	    $sql = mysqli_query($con,"DELETE FROM ornament WHERE ornamentId='$id'");

			$ornamentList="";
			$bill = $_SESSION['bill'];
			$sql = mysqli_query($con,"SELECT * FROM ornament WHERE billId='$bill' AND date='$date'");
			if(mysqli_num_rows($sql)>0){
			while($row = mysqli_fetch_assoc($sql)){
				if($row['nine']== "916"){
					$ornamentList.= "<tr class='active-row'>";
				}
				else{
					$ornamentList.= "<tr class='passive-row'>";
				}
				$ornamentList.= "<td>" . $row['metal'] . "</td>";
				$ornamentList.= "<td>" . $row['type'] ."  (". $row['pieces']. ")</td>";
				$ornamentList.= "<td>" . $row['weight'] . "</td>";
				$ornamentList.= "<td>" . $row['sWaste'] . "</td>";
				$ornamentList.= "<td>" . $row['reading'] . "</td>";
				$ornamentList.= "<td>" . $row['purity'] . "</td>";
				$ornamentList.= "<td>" . $row['gross'] . "</td>";
				$ornamentList.= "<td><b><a class='text-danger' title='Delete Record' onclick='delete_ornament(".$row['ornamentId'].")'><i class='fa fa-trash' aria-hidden='true'></i> Delete</a></b></td>";
				$ornamentList.= "</tr>";
			}
			$ornamentList.= "<hr>";
			$totalsql = mysqli_query($con,"SELECT metal,SUM(weight) AS Weight,SUM(sWaste) AS sWaste, SUM(reading) AS reading,SUM(gross) AS Gross,SUM(pieces) as totalPieces FROM ornament WHERE billId='$bill' AND date='$date'");
			$totalResult = mysqli_fetch_assoc($totalsql);
			$totalPurity = round((($totalResult['Gross']/$totalResult['reading'])/$_SESSION['rate1'])*100,2);
			$ornamentList.= "<tr class='totalResult'><td>Total</td>";
			$ornamentList.= "<td>".$totalResult['totalPieces']."</td>";
			$ornamentList.= "<td>".round($totalResult['Weight'],3)."</td>";
			$ornamentList.= "<td>".round($totalResult['sWaste'],3)."</td>";
			$ornamentList.= "<td>".round($totalResult['reading'],3)."</td>";
			$ornamentList.= "<td>".$totalPurity."</td>";
			$ornamentList.= "<td>".$totalResult['Gross']."</td>";
			$ornamentList.= "<td></td></tr>"; 
			

			
			$marginP =3;
			$margin_amount = ($totalResult['Gross']*$marginP)/100;
			$net_amount = round($totalResult['Gross'] - $margin_amount);
			

			$jsonData["ornamentList"]=$ornamentList;
			$jsonData["grossW"]=round($totalResult['Weight'],3);
			$jsonData["netW"]=round($totalResult['reading'],3);
			$jsonData["grossA"]=$totalResult['Gross'];		
			$jsonData["margin"]=round($margin_amount);
			$jsonData["net1"]=$net_amount;
			$jsonData["marginP"]=$marginP;
			
			}else{
				
				$ornamentList.= "<tr class='totalResult'><td align='center' colspan='7' class='text-danger'>No ornament records found</td></tr>"; 
			
				$marginP =0;
				$margin_amount = 0;
				$net_amount = 0;

				$jsonData["ornamentList"]=$ornamentList;
				$jsonData["grossW"]=0;
				$jsonData["netW"]=0;
				$jsonData["grossA"]=0;		
				$jsonData["margin"]=round($margin_amount);
				$jsonData["net1"]=$net_amount;
				$jsonData["marginP"]=$marginP;

			}
			echo json_encode($jsonData);
		
	}


	if($_POST['type']=="release"){
		
	 	$id = $_POST['id'];
		$bill=$_POST['billId'];
	    $sql = mysqli_query($con,"DELETE FROM ornament WHERE ornamentId='$id'");

		$ornamentList="";
		$bill = $_SESSION['bill'];
		$sql = mysqli_query($con,"SELECT * FROM ornament WHERE billId='$bill' AND date='$date'");
		
		if(mysqli_num_rows($sql)>0){

			while($row = mysqli_fetch_assoc($sql)){
				if($row['nine']== "916"){
					$ornamentList.= "<tr class='active-row'>";
				}
				else{
					$ornamentList.= "<tr class='passive-row'>";
				}
				$ornamentList.= "<td>" . $row['metal'] . "</td>";
				$ornamentList.= "<td>" . $row['type'] ."  (". $row['pieces']. ")</td>";
				$ornamentList.= "<td>" . $row['weight'] . "</td>";
				$ornamentList.= "<td>" . $row['sWaste'] . "</td>";
				$ornamentList.= "<td>" . $row['reading'] . "</td>";
				$ornamentList.= "<td>" . $row['purity'] . "</td>";
				$ornamentList.= "<td>" . $row['gross'] . "</td>";
				$ornamentList.= "<td><b><a class='text-danger' title='Delete Record' onclick='delete_ornament(".$row['ornamentId'].")'><i class='fa fa-trash' aria-hidden='true'></i> Delete</a></b></td>";
				$ornamentList.= "</tr>";
			}
			
			$ornamentList.= "<hr>";
			$totalsql = mysqli_query($con,"SELECT metal,SUM(weight) AS Weight,SUM(sWaste) AS sWaste, SUM(reading) AS reading,SUM(gross) AS Gross,SUM(pieces) as totalPieces FROM ornament WHERE billId='$bill' AND date='$date'");
			$totalResult = mysqli_fetch_assoc($totalsql);
			$totalPurity = round((($totalResult['Gross']/$totalResult['reading'])/$_SESSION['rate1'])*100,2);
			$ornamentList.= "<tr class='totalResult'><td>Total</td>";
			$ornamentList.= "<td>".$totalResult['totalPieces']."</td>";
			$ornamentList.= "<td>".round($totalResult['Weight'],3)."</td>";
			$ornamentList.= "<td>".round($totalResult['sWaste'],3)."</td>";
			$ornamentList.= "<td>".round($totalResult['reading'],3)."</td>";
			$ornamentList.= "<td>".$totalPurity."</td>";
			$ornamentList.= "<td>".$totalResult['Gross']."</td>";
			$ornamentList.= "<td></td></tr>"; 
			
			
			$marginP =3;
			$margin_amount = ($totalResult['Gross']*$marginP)/100;
			$net_amount = round($totalResult['Gross'] - $margin_amount);

			$rid = $_SESSION['Rid'];
			$relData = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM releasedata WHERE rid='$rid'"));
			$impsA = $relData['relIMPS'];
			$cashA = $relData['relCash'];
			$release_amount = $relData['amount'];
			$net_payable = $net_amount - $release_amount;

			$jsonData["ornamentList"]=$ornamentList;
			$jsonData["grossW"]=round($totalResult['Weight'],3);
			$jsonData["netW"]=round($totalResult['reading'],3);
			$jsonData["grossA"]=$totalResult['Gross'];		
			$jsonData["margin"]=round($margin_amount);
			$jsonData["net1"]=$net_amount;
			$jsonData["marginP"]=$marginP;
			$jsonData["billStatus"]=$billStatus;
			$jsonData["net_payable"]=$net_payable;
			$jsonDate["impsA"]=$impsA;
			$jsonDate["cashA"]=$cashA;
		
		}else{
			
			$ornamentList.= "<tr class='totalResult'><td align='center' colspan='7' class='text-danger'>No ornament records found</td></tr>"; 
			$marginP =0;
			$margin_amount = 0;
			$net_amount = 0;
			$rid = $_SESSION['Rid'];
			$relData = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM releasedata WHERE rid='$rid'"));
			$impsA = $relData['relIMPS'];
			$cashA = $relData['relCash'];
			$release_amount = $relData['amount'];
			$net_payable = $net_amount - $release_amount;

			$jsonData["ornamentList"]=$ornamentList;
			$jsonData["grossW"]=0;
			$jsonData["netW"]=0;
			$jsonData["grossA"]=0;		
			$jsonData["margin"]=0;
			$jsonData["net1"]=$net_amount;
			$jsonData["marginP"]=$marginP;
			$jsonData["net_payable"]=$net_payable;
			$jsonDate["impsA"]=$impsA;
			$jsonDate["cashA"]=$cashA;
		}
			
			echo json_encode($jsonData);
		
	}	
?>