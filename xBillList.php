<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type == 'Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type == 'ApprovalTeam'){
		include("header.php");
		include("menuapproval.php");
	}
	else if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else if($type=='Legal'){
		include("header.php");
		include("menulegal.php");
	}
	else{
		include("logout.php");
	}
	include("dbConnection.php");
	$date = date('Y-m-d');
	if($_GET["search_date"]==""){
		$date = date('Y-m-d');
		$today ="Todays";
	}else{
		$date = $_GET["search_date"];
		if($_GET["search_date"]==date('Y-m-d')){
		    $today ="Todays";
		}else{
		    $today = date("d-m-Y", strtotime($date));
		}
	}
	
	
	
	//  UPLOAD DOCS FROM PHYSICAL BILL ( @ xuploadDocs.php )
	if (isset($_POST['uploadCustomerDocs'])) {

		$date = date('Y-m-d');
		$time = date('h:i:s');
		$datetime= date('Y-m-dhis');
		$phone = $_POST['phone'];
		$custId = $_POST['custId'];
		$name = $_POST['name'];

		$search_sql=mysqli_query($con,"select id from customer_misc where phone='$phone'");
		$row = mysqli_fetch_assoc($search_sql);		

 		// DESTINATION FOLDER UPLOAD
 		$folderMisc = "CustomerMisc/";
			

		// PHOTO 1 UPLOAD
		$photo_1 = $_POST['photo_1'];      
		if (file_exists($_FILES['photo_1']['tmp_name'])) {  
			$file = $_FILES['photo_1']['name'];
			$file_loc = $_FILES['photo_1']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$photo_1 = str_replace($new_file_name, $datetime . $custId . 'photo_1' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $photo_1);
		} else {
			if($_POST['photo-1']==""){
				$photo_1 = '';
			}else{
				$photo_1 = $_POST['photo-1'];
			}
		}
		
		// PHOTO 2 UPLOAD
		$photo_2 = $_POST['photo_2'];   
		if (file_exists($_FILES['photo_2']['tmp_name'])) {  
			$file = $_FILES['photo_2']['name'];
			$file_loc = $_FILES['photo_2']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$photo_2 = str_replace($new_file_name, $datetime . $custId . 'photo_2' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $photo_2);
		} else {
			if($_POST['photo-2']==""){
				$photo_2 = '';
			}else{
				$photo_2 = $_POST['photo-2'];
			}
		}
		// DOC 1 UPLOAD
		$doc_1 = $_POST['doc_1'];   
		if (file_exists($_FILES['doc_1']['tmp_name'])) {  
			$file = $_FILES['doc_1']['name'];
			$file_loc = $_FILES['doc_1']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$doc_1 = str_replace($new_file_name, $datetime . $custId . 'doc_1' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $doc_1);
		} else {
			if($_POST['doc-1']==""){
				$doc_1 = '';
			}else{
				$doc_1 = $_POST['doc-1'];
			}
		}
		// DOC 2 UPLOAD
		$doc_2 = $_POST['doc_2'];   
		if (file_exists($_FILES['doc_2']['tmp_name'])) {  
			$file = $_FILES['doc_2']['name'];
			$file_loc = $_FILES['doc_2']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$doc_2 = str_replace($new_file_name, $datetime . $custId . 'doc_2' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $doc_2);
		} else {
			if($_POST['doc-2']==""){
				$doc_2 = '';
			}else{
				$doc_2 = $_POST['doc-2'];
			}
		}		
		// VIDEO 1 UPLOAD
		$video_1 = $_POST['video_1'];   
		if (file_exists($_FILES['video_1']['tmp_name'])) {  
			$file = $_FILES['video_1']['name'];
			$file_loc = $_FILES['video_1']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$video_1 = str_replace($new_file_name, $datetime . $custId . 'video_1' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $video_1);
		} else {
			if($_POST['video-1']==""){
				$video_1 = '';
			}else{
				$video_1 = $_POST['video-1'];
			}
		}
		// VIDEO 2 UPLOAD
		$video_2 = $_POST['video_2'];   
		if (file_exists($_FILES['video_2']['tmp_name'])) {  
			$file = $_FILES['video_2']['name'];
			$file_loc = $_FILES['video_2']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$video_2 = str_replace($new_file_name, $datetime . $custId . 'video_2' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $video_2);
		} else {
			if($_POST['video-2']==""){
				$video_2 = '';
			}else{
				$video_2 = $_POST['video-2'];
			}
		}		

		// AUDIO 1 UPLOAD
		$audio_1 = $_POST['audio_1'];   
		if (file_exists($_FILES['audio_1']['tmp_name'])) {  
			$file = $_FILES['audio_1']['name'];
			$file_loc = $_FILES['audio_1']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$audio_1 = str_replace($new_file_name, $datetime . $custId . 'audio_1' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $audio_1);
		} else {
			if($_POST['audio-1']==""){
				$audio_1 = '';
			}else{
				$audio_1 = $_POST['audio-1'];
			}
		}
		// AUDIO 2 UPLOAD
		$audio_2 = $_POST['audio_2'];   
		if (file_exists($_FILES['audio_2']['tmp_name'])) {  
			$file = $_FILES['audio_2']['name'];
			$file_loc = $_FILES['audio_2']['tmp_name'];
			$file_extn = substr($file, strrpos($file, '.') - 1);
			$new_file_name = strtolower($file);
			$audio_2 = str_replace($new_file_name, $datetime . $custId . 'audio_2' . $file_extn, $new_file_name);
			move_uploaded_file($file_loc, $folderMisc . $audio_2);
		} else {
			if($_POST['audio-2']==""){
				$audio_2 = '';
			}else{
				$audio_2 = $_POST['audio-2'];
			}
		}

		if($row["id"]==""){
			$sql = "INSERT INTO customer_misc(name,customerId,phone,photo_1,photo_2,doc_1,doc_2,video_1,video_2,audio_1,audio_2,date,time) VALUES ('$name','$custId','$phone','$photo_1' ,'$photo_2' ,'$doc_1','$doc_2','$video_1','$video_2','$audio_1','$audio_2','$date','$time');"; 
		}else{
			$sql = "UPDATE customer_misc SET photo_1='$photo_1',photo_2='$photo_2',doc_1='$doc_1',doc_2='$doc_2',video_1='$video_1',video_2='$video_2',audio_1='$audio_1',audio_2='$audio_2',date='$date',time='$time' where phone='$phone'"; 
		}
		
		if (mysqli_query($con, $sql)) {			
			echo "<script>alert('Upload Successfull');</script>";
		} else {
			echo "<script type='text/javascript'>alert('Error in Uploading!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'xBillUploadDoc.php?phone=".$phone."';\",150);</script>";
		}
		
		
	}
?>
<style>
	.tab{
	font-family: 'Titillium Web', sans-serif;
	}
	.tab .nav-tabs{
	padding: 0;
	margin: 0;
	border: none;
	}   
	.tab .nav-tabs li a{
	color: #123C69;
	background: #f8f8ff;
	font-size: 12px;
	font-weight: 600;
	text-align: center;
	letter-spacing: 1px;
	text-transform: uppercase;
	padding: 7px 10px 6px;
	margin: 5px 5px 0px 0px;
	border: none;
	border-bottom: 3px solid #123C69;
	border-radius: 0;
	position: relative;
	z-index: 1;
	transition: all 0.3s ease 0.1s;
	}
	.tab .nav-tabs li.active a,
	.tab .nav-tabs li a:hover,
	.tab .nav-tabs li.active a:hover{
	color: #f2f2f2;
	background: #123C69;
	border: none;
	border-bottom: 3px solid #ffa500;
	font-weight: 600;
	border-radius:3px;
	}
	.tab .nav-tabs li a:before{
	content: "";
	background: #f8f8ff;
	height: 100%;
	width: 100%;
	position: absolute;
	bottom: 0;
	left: 0;
	z-index: -1;
	transition: clip-path 0.3s ease 0s,height 0.3s ease 0.2s;
	clip-path: polygon(0 0, 100% 0, 100% 100%, 0% 100%);
	}
	.tab .nav-tabs li.active a:before,
	.tab .nav-tabs li a:hover:before{
	height: 0;
	clip-path: polygon(0 0, 0% 0, 100% 100%, 0% 100%);
	}
	.tab .tab-content{
	color: #0C1115;
	background: #f8f8ff;
	font-size: 12px;
	/* outline: 2px solid rgba(19,99,126,0.2); */
	position: relative;
	border: 3px solid #fff;
	border-radius: 10px;
	padding: 2px;
	box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
	}
	.tab-content h4{
	color: #123C69;
	font-weight:500;
	}	
	.tab-pane{
	background: #f8f8ff;
	padding: 10px 5px 50px 5px;
	min-height:350px;
	}	
	@media only screen and (max-width: 479px){
	.tab .nav-tabs{
	padding: 0;
	margin: 0 0 15px;
	}
	.tab .nav-tabs li{
	width: 100%;
	text-align: center;
	}
	.tab .nav-tabs li a{ margin: 0 0 5px; }
	}	
	#wrapper{
	background-color: #f8f8ff;
	}
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	thead {
	text-transform:uppercase;
	background-color:#123C69;
	}
	thead tr{
	color: #f2f2f2;
	}	
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
	}
	.btn-primary{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 12px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.btn-success{
	display:inline-block;
	padding:0.5em 1.0em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 10px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.btn-success:active:hover, .btn-success.active:hover,.btn-success:active.focus, .btn-success.active.focus,	.btn-success:hover, .btn-success:focus, .btn-success:active, .btn-success.active{
	background: #1c6eaf;
	border-color: #1c6eaf;
	border: 1px solid #1c6eaf;
	color: #fffafa;
	}	
	.fa_Icon{
	color: #ffa500;
	}
	.trans_Icon{
	color: #b8860b;
	}
	.text-success{
	font-weight:600;
	color: #123C69;
	}
	.panel-heading h3{
	text-transform:uppercase;
	}
	.hpanel .panel-body{
	padding: 5px 15px 5px;
	}	
	.dataTables_wrapper .row{
	margin-right: 0px;
	margin-left: 0px;
	}
	
	.btn-default{
		margin-bottom: 10px;
		background: #b8860b;
		color: aliceblue;
	}
	#search_date{
		padding: 10px;
		height: 50px;
		font-size: 16px;
		color: grey;
		box-sizing: border-box;
		border: 2px solid #ccc!important;
	}
	.row-danger{
	    background:#f08080;
        color: azure;
        font-size: 14px;
        font-weight: bold;
	}
	
	.row-warning{
        font-size: 14px;
        font-weight: bold;		    
	}
</style>

<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<div class="row">
					    <form action="" method="GET">
						    <div class="col-lg-12">				
							    <div class="col-lg-10">
								    <h3><i class="trans_Icon fa fa-edit"></i> <?php echo $today; ?> Bill Transactions </h3>
							    </div>				
						    	<div class="col-lg-2 pull-right">
						    	    <form action="<?php echo $_SERVER['REQUEST_URI'];?>">
						    	    <div class="input-group">
                                        <input type="date" name="search_date" id="search_date" placeholder="Select the date">
                                   	    <span class="input-group-btn">
										    <button class="btn btn-primary btn-block" style="height: 49px;" id="search_customer_bill" type="submit"> <i class="fa fa-search"></i> </button>
									    </span>
									</div>
									</form>
							    </div>
						    </div>
					    </form>	
					</div>					
				</div>			
				
	            <div class="tab" role="tabpanel">
					
					<div class="tab-content tabs">
						<div role="tabpanel" class="tab-pane fade in active" id="trans-panel">
							
							<table id="total-trans-datatable" class="table table-bordered">
								<thead>
									<tr>
										<th><i class="fa fa-sort-numeric-asc"></i></th>
										<th>State</th>
										<th>Branch Name</th>
										<th>Customer</th>
										<th>Mobile</th>
										<th>Gross Weight</th>
										<th>Net Weight</th>
										<th>Gross Amount</th>
										<th>Net Amount</th>
										<th>Amount Paid</th>
										<th>Bill Type</th>
										<th>Margin</th>
										<th>Total Bills</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$i = 1;
										$sql = mysqli_query($con,"SELECT B.state,B.branchName,T.id,T.billId,T.name,T.customerId,T.phone,T.billCount,T.releases,T.grossW,T.netW,T.grossA,T.netA,T.amountPaid,T.rate,T.time,T.date,T.branchId,T.type,T.comm,T.margin,T.releaseID, count(T.phone) as phone_count FROM trans T,branch B WHERE T.date='$date' AND T.status IN ('Pending','Approved') AND T.branchId=B.branchId group by T.phone ORDER BY T.id DESC");
										while($row = mysqli_fetch_assoc($sql)){
											if($row['billCount'] >= 2){
												
												if($row['phone_count']>1){
												    $current_billCount=$row['billCount']+2;
												}else{
												    $current_billCount=$row['billCount']+1;
												}												
												if($current_billCount>1 && $current_billCount<5){
													$class="row-warning";
												}												
												if($current_billCount>4){
													$class="row-danger";
												}
												
												echo "<tr class='$class'>";											
												echo "<td>".$i."</td>";
												echo "<td>".$row['state']."</td>";
												echo "<td>".$row['branchName']."</td>";
												echo "<td>".$row['name']."</td>";
												echo "<td>".$row['phone']."</td>";
												echo "<td>".ROUND($row['grossW'],2)."</td>";
												echo "<td>".ROUND($row['netW'],2)."</td>";
												echo "<td>".ROUND($row['grossA'],0)."</td>";
												echo "<td>".ROUND($row['netA'],0)."</td>";
												echo "<td>".ROUND($row['amountPaid'],0)."</td>";
												echo "<td>".$row['type']."<br>";
												if($row['type'] == 'Release Gold'){
													$relType = mysqli_fetch_assoc(mysqli_query($con,"SELECT type,relCash FROM releasedata WHERE releaseID='$row[releaseID]' AND date='$date'"));
													if($relType['type'] == 'CASH/IMPS'){
														echo ($relType['relCash'] == 0)?'<b>(IMPS)</b></td>':'<b>(CASH/IMPS)</b></td>';
													}
													else{
														echo "<b>(CASH)</b></td>";
													}
												}
												else{
													echo"</td>";
												}
												echo "<td>" . ROUND($row['margin'],0)."<br>(".ROUND($row['comm'],2)."%)</td>";
												if($row['billCount'] > 1){
													echo "<td class='text-center'><a style='font-size: 12px;' class='btn btn-success btn-md' target='_blank' href='existing.php?phone=".$row['phone']."'>" .$current_billCount. "</a></td>";
												}
												else{
													echo "<td class='text-center'>" .$row['billCount']. "</td>" ;
												}
												
												//echo "<td><a style='font-size: 12px;' class='btn btn-success btn-md' target='_blank' href='xBillUploadDoc.php?phone=".$row['phone']."'> Verify </a></td>";
												echo "<td><a style='font-size: 12px;' class='btn btn-success btn-md' target='_blank' href='https://192.168.1.10/agpl/xCustomerVerificationDoc.php?phone=".$row['phone']."&name=".$row['name']."&custId=".$row['customerId']."'> Verify </a></td>";
												echo "</tr>";
												$i++;
											
											}
										}
									?>
								</tbody>
								<div style="clear:both"></div>
							</table>
							
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
	<script>
		
		$('#total-trans-datatable').DataTable({
			responsive: true,
			dom: 'Bfrtip',
			buttons: [
				{extend: 'csv',text: 'EXPORT TO EXCEL', title: 'Bill Transaction Report', className: 'btn-md btn-info'},
			]
		});
		

	</script>	