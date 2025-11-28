<?php 
	session_start();
	include("dbConnection.php");
	$current_date = date('Y-m-d');

	

	if(isset($_POST["action"]) && $_POST["action"]=="get_current_stock"){
		
		$jsonData=array();
		$prodQuery=mysqli_query($con,"SELECT product_name,stock,inventory_received,inventory_shipped FROM inventory where id=".$_POST["product_id"]);
		$count=mysqli_num_rows($prodQuery);
		if($count>0){
		$res=mysqli_fetch_assoc($prodQuery);
		
			$jsonData["stock"]=$res["stock"];
			$jsonData["product_name"]=$res["product_name"];			
			$jsonData["inventory_received"]=$res["inventory_received"];			
			$jsonData["inventory_shipped"]=$res["inventory_shipped"];			
			
		}else{
			$jsonData["stock"]=0;
			$jsonData["product_name"]="";
			$jsonData["inventory_received"]=0;
			$jsonData["inventory_shipped"]=0;
			
		}
		
		echo json_encode($jsonData);
	}
	
?>