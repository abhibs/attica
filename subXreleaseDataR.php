<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];	
	$employeeId = $_SESSION['employeeId'];


	if($type == 'SubZonal'){
	    include("header.php");
		include("menuSubZonal.php");
	}
	else{
		include("logout.php");
	}
	
	// if($type == 'SubZonal'){
	// 	$branch = $_SESSION['branchCode'];
	// 	if($branch=="KA"){
	// 		$state = "AND r.branchId IN (SELECT branchId FROM branch WHERE state='Karnataka')";
	// 	}
	// 	elseif($branch=="TN"){
	// 		$state = "AND r.branchId IN (SELECT branchId FROM branch WHERE state IN ('Tamilnadu','Pondicherry'))";
	// 	}
	// 	elseif($branch=="AP-TS"){
	// 	    $state = "AND r.branchId IN (SELECT branchId FROM branch WHERE state IN ('Telangana','Andhra Pradesh'))";
	// 	}
	// }
	// else{
	// 	$state = "";
	// }	
	
	include("dbConnection.php");
	$date=date('Y-m-d');
?>
<style>
	#wrapper{
	background: #f5f5f5;
	}	
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{
	background-color:#fffafa;
	}
	.text-success{
	color:#123C69;
	text-transform:uppercase;
	font-weight:bold;
	font-size: 12px;
	}
	.btn-primary{
	background-color:#123C69;
	}
	.theadRow {
	text-transform:uppercase;
	background-color:#123C69!important;
	color: #f2f2f2;
	font-size:11px;
	}	
	.dataTables_empty{
	text-align:center;
	font-weight:600;
	font-size:12px;
	text-transform:uppercase;
	}
	.btn-success{
	display:inline-block;
	padding:0.7em 1.4em;
	margin:0 0.3em 0.3em 0;
	border-radius:0.15em;
	box-sizing: border-box;
	text-decoration:none;
	font-size: 11px;
	font-family:'Roboto',sans-serif;
	text-transform:uppercase;
	color:#fffafa;
	background-color:#123C69;
	box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17);
	text-align:center;
	position:relative;
	}
	.fa_Icon {
	color:#ffcf40;
	}
	fieldset {
	margin-top: 1.5rem;
	margin-bottom: 1.5rem;
	border: none;
	border: 5px solid #fff;
	border-radius: 10px;
	padding: 5px;
	box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
	}
	legend{
	margin-left:8px;
	width:400px;
	background-color: #123C69;
	padding: 5px 15px;
	line-height:30px;
	font-size: 18px;
	color: white;
	text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
	transform: translateX(-1.1rem);
	box-shadow: -1px 1px 1px rgba(0,0,0,0.8);
	margin-bottom:0px;
	letter-spacing: 2px;
	}
	button {
	transform: none;
	box-shadow: none;
	}
	
	button:hover {
	background-color: gray;
	cursor: pointer;
	}
	
	legend:after {
	content: "";
	height: 0;
	width:0;
	background-color: transparent;
	border-top: 0.0rem solid transparent;
	border-right:  0.35rem solid black;
	border-bottom: 0.45rem solid transparent;
	border-left: 0.0rem solid transparent;
	position:absolute;
	left:-0.075rem;
	bottom: -0.45rem;
	}
	.row{
	margin-left:0px;
	margin-right:0px;
	}
	@media only screen and (max-width: 500px) {
	legend{
	width:390px;
	font-size: 12px;
	
	}
	}
	.submit-button {
	font-size:20px;
	}
</style>
</div>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<fieldset>
					<legend><i style="padding-top:15px" class="fa_Icon fa fa-edit"></i> GOLD RELEASE (CASH/IMPS)</legend>					
					<div class="panel-body" style="background:#f5f5f5;">
						<div class="table-responsive">
							<table id="example5" class="table table-striped table-bordered">
								<thead>
									<tr class="theadRow">
										<th>#</th>
										<th>BRANCH</th>
										<th>TIME</th>
										<th>NAME</th>
										<th>CONTACT</th>
										<th>TYPE</th>
										<th>AMOUNT</th>
										<th>RELEASE_TYPE</th>
										<th>RELEASE_PLACE</th>
										<th>STATUS</th>
										<th>REL_DOC1</th>
										<!--<th>REL_DOC2</th>-->
										<th>REL_DOC2</th>
										<!-- <th>DELETE</th>-->
									</tr>
								</thead>
								<tbody>
                                <?php
                             $sqlQ = "SELECT r.rid, r.name, r.phone, r.relPlaceType, r.relPlace, r.type, r.amount, r.status, r.time, r.relDoc1, r.relDoc2, r.relDoc3, b.branchName
                             FROM releasedata r
                             INNER JOIN branch b ON r.branchId = b.branchId AND b.ezviz_vc = '$employeeId'
                              WHERE r.date = '$date'"; 

                             $resultQ = mysqli_query($con, $sqlQ);

                             if ($resultQ) {
                             $i = 1;
                             while ($rowA = mysqli_fetch_assoc($resultQ)) {
                                  echo "<tr>";
                                  echo "<td>".$i."</td>";
                                  echo "<td>".$rowA['branchName']."</td>";
                                  echo "<td>".$rowA['time']."</td>";
                                  echo "<td>".$rowA['name']."</td>";
                                  echo "<td>".$rowA['phone']."</td>";
                                  echo "<td>".$rowA['type']."</td>";
                                  echo "<td>".$rowA['amount']."</td>";
                                  echo "<td>".$rowA['relPlaceType']."</td>";
                                  echo "<td>".$rowA['relPlace']."</td>";
                                  echo "<td>".$rowA['status']."</td>";
                                  echo "<td class='text-center'><a class='submit-button' target='_blank' href='ReleaseDocuments/" . urlencode($rowA['relDoc1']) . "'><i class='fa fa-file-text-o' style='color:#900;'></i></a> </td>";
                                 //echo "<td class='text-center'>";
                                // if ($rowA['relDoc2'] != "") {
                                // echo "<a class='submit-button' target='_blank' href='ReleseDocuments/" . urlencode($rowA['relDoc2']) . "'><i class='fa fa-file-text-o' style='color:#900;'></i></a>";                                             
                                //} else {
                                 //echo "<a class='submit-button'><i class='fa fa-file-text-o' style='color:#ffcf40;'></i></a>";
                                // }
                                // echo "</td>";
                                  echo "<td class='text-center'>";
                                   if ($rowA['relDoc3'] != "") {
                                   echo "<a class='submit-button' target='_blank' href='ReleaseDocuments/" . urlencode($rowA['relDoc3']) . "'><i class='fa fa-file-text-o' style='color:#900;'></i></a>";
                                   } else {
                                   echo "<a class='submit-button'><i class='fa fa-file-text-o' style='color:#ffcf40;'></i></a>";
                                   }
                                   echo "</td>";
                                  // echo "<td><a class='btn btn-outline btn-primary' onClick=\"javascript: return confirm('Please confirm to Delete');\" href='xdeletereleaseR.php?rid=".$rowA['rid']."'><i style='color:#ff0000' class='fa fa-times'></i></a></td>";
                                   echo "</tr>";
                                 $i++;
        
                                  }
                                  } 
                                  ?>

      							</tbody>
							</table>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<?php include("footer.php"); ?>
