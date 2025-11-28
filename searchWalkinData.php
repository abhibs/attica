<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type=='Software'){
		include("header.php");
		include("menuSoftware.php");
	}
	else{
		include("logout.php");
	}
	
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date=date('Y-m-d');
?>
<style>
	#wrapper h3{
	text-transform:uppercase;
	font-weight:600;
	font-size: 20px;
	color:#123C69;
	}
	.hpanel .panel-body {
	background-color: #f5f5f5;
	box-shadow: 10px 15px 15px #999;
	border-radius: 3px;
	padding: 15px;
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
	color:#990000;
	}
	.fa-reject{
	color: #123C69;
	font-size: 20px;
	}
	.fa-delete{
	color: #990000;
	font-size: 20px;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3><i class="fa_Icon fa fa-edit" ></i> WALKIN DATA </h3>
				</div>
				<div class="panel-body">
					<table id="example5" class="table table-bordered">
						<thead>
							<tr class="theadRow">
								<th>#</th>
								<th>BRANCH</th>
								<th>TIME</th>
								<th>NAME</th>
								<th>TYPE</th>
								<th>HAVING</th>
								<th>GROSS_WT</th>
								<th>AMOUNT</th>
								<th width='25%'>REMARKS</th>
								<th>ISSUE</th>
								<th>REJECT</th>
								<th>DELETE</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$sqlQ = "SELECT w.id,w.name,w.mobile,w.time,w.gold,w.havingG,w.issue,w.gwt,w.ramt,w.remarks,b.branchName 
								FROM walkin w,branch b 
								WHERE w.date='$date' AND w.branchId=b.branchId 
								ORDER BY id DESC";
								$sqlA = mysqli_query($con,$sqlQ);
								$i=1;
								while($rowA = mysqli_fetch_assoc($sqlA)){
									echo "<tr id='row_".$rowA['id']."'>";
									echo "<td>".$i."</td>";
									echo "<td>".$rowA['branchName']."</td>";
									echo "<td>".$rowA['time']."</td>";
									echo "<td>".$rowA['name']."<br>".$rowA['mobile']."</td>";
									echo "<td>".$rowA['gold']."</td>";
									echo "<td>".$rowA['havingG']."</td>";
									echo "<td>".$rowA['gwt']."</td>";
									echo "<td>".$rowA['ramt']."</td>";
									echo "<td>".$rowA['remarks']."</td>";
									echo "<td class='status'>".$rowA['issue']."</td>";
									
									echo "<td><a class='btn' onClick='reject_enquiry(".$rowA['id'].")'><i class='fa fa-reject fa-exclamation-triangle'></i></a></td>";
									
									echo "<td><a class='btn' onClick='delete_enquiry(".$rowA['id'].")'><i class='fa fa-delete fa-remove'></i></a></td>";
									
									echo "</tr>";
									$i++;
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<script>
	
		//function reject_enquiry(row_id){
			//let conf = confirm("Are you sure you want to REJECT the enquiry?");
			//if(conf){
				//$.ajax({
					//url:"editAjax.php",
					//type:"POST",
					//data:{editWalkinReject:'editWalkinReject', row_id: row_id},
					//success: function(e){
						//if(e == '1'){							
							//let row = document.getElementById('row_'+row_id);
							//let row_status = row.querySelector('.status');
							//row_status.innerHTML = "Rejected";
						//}
						//else{
							//alert('Oops!!! Something went wrong');
						//}
					//}
				//});
			//}
		//}

		function reject_enquiry(row_id){
	        let conf = confirm("Are you sure you want to REJECT the enquiry?");
    if(conf){
        $.ajax({
            url: "editAjax.php",
            type: "POST",
            data: { editWalkinReject: 'editWalkinReject', row_id: row_id },
            success: function(e){
                if ($.trim(e) == '1') {
                    // Update row text (optional)
                    let row = document.getElementById('row_' + row_id);
                    if (row) {
                        let row_status = row.querySelector('.status');
                        if (row_status) {
                            row_status.innerHTML = "Rejected";
                        }
                    }

                    // Refresh the page
                    window.location.reload();   // or: location.reload();
                } else {
                    alert("Failed to reject enquiry. Please try again.");
                }
            },
            error: function() {
                alert("Server error. Please try again.");
            }
        });
    }
}
		function delete_enquiry(row_id){
			let conf = confirm("Are you sure you want to DELETE the enquiry?");
			if(conf){
				$.ajax({
					url:"editAjax.php",
					type:"POST",
					data:{editWalkinDelete:'editWalkinDelete', row_id: row_id},
					success: function(e){
						if(e == '1'){
							let row = document.getElementById('row_'+row_id);
							row.remove();
						}
						else{
							alert('Oops!!! Something went wrong');
						}
					}
				});
			}
		}
		
	</script>
<?php include("footer.php"); ?>
