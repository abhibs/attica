<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type=="Master"){
        include("header.php");
    	include("menumaster.php");
    }
    else if($type=='ITMaster'){
	    include("header.php");
	    include("menuItMaster.php");
	}
	else if($type=='Software'){
	    include("header.php");
	    include("menuSoftware.php");
	}
    else{
        include("logout.php");
    }
	include("dbConnection.php");
	date_default_timezone_set("Asia/Kolkata");
	$date=date('Y-m-d');

	if(isset($_POST["authorize_ws_access"])) 
	{
		$authaccessArray=array();
		$accessArray=array();
		$authArray=array();
		$accessArray=explode(',', $_POST["authCheck"]);
		$authArray=explode(',', $_POST["authType"]);
		
		foreach($accessArray as $key => $val){
			
			$id = $val;		
			if($authArray[$key]=="1"){
				$ws_access = 0;
			}else{
				$ws_access = 1;
			}
			
			$update_ws_access="Update branch set ws_access='$ws_access' where id = '$id'";
			mysqli_query($con,$update_ws_access);	
			
			
		}

	}
	
	
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
		color:#b8860b;
	}
	.row{
	    margin-left:0px;
	    margin-right:0px;
	}
	tfoot{
		background-color:#efeff5;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-8">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i class="fa_Icon fa fa-balance-scale"></i> Authorize Weighing Scale Access </h3>
				</div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<div class="table-responsive">
						<table id="ws-access-datatable" class="table table-bordered">
							<thead>
								<tr class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th>Branch</th>
									<th>City</th>
									<th>State</th>
									<th style='text-align:center;'>Access Status</th>
									<th style='text-align:center;'>Modify Status</th>									
								</tr>
							</thead>
							<tbody id="authAccessResponse">
								<?php
									$query = mysqli_query($con,"SELECT id,branchId, branchName,city,state,ws_access FROM branch where Status=1 order by branchId");
									$i = 1;
									while($row = mysqli_fetch_assoc($query)){
										if($row['ws_access']==0){
											$ws_access = "<td style='text-align:center;color:#e74c3c;'>Inactive</td>";
											$rowClass ="bg-danger";
											$authType = "<input type='hidden' name='authType' id='authType_".$row['id']."' value='0'/>";
										}
										else{
											$ws_access = "<td style='text-align:center;color:#008000;'>Access Given</td>";
											$rowClass ="bg-success";
											$authType = "<input type='hidden' name='authType' id='authType_".$row['id']."' value='1'/>";
										}	
										echo "<tr class='$rowClass'>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branchName'] . "<p>". $row['branchId'] ."</p></td>";

										echo "<td>" . $row['city'] ."</td>";
										echo "<td>" . $row['state'] ."</td>";
										echo $ws_access;
										echo "<td style='text-align:center'><input type='checkbox' class='authAccess' name='access[]' id='box_".$row['id']."' value='".$row['id']."'/>".$authType."</td>";
										
										echo "</tr>";
										$i++;
									}
								?>
							</tbody>
							<tfoot>
								<tr>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th style='text-align:center;'><button class='btn btn-success' id="accessBtn">Submit</button></th>								
								</tr>									
							</tfoot>
						</table>
					</div>
					

			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php"); ?>
<script>

$(document).ready(function(){
	
	$('#ws-access-datatable').DataTable({
		responsive: true
	});

	$('.authAccess').click(function() {	
		if ($('.authAccess:checked').length == $('.authAccess').length) {
			$('#select_all').prop('checked', true);
		} else {
			$('#select_all').prop('checked', false);
		}
	});

	$('#accessBtn').click(function() {

		var authCheck = new Array();
		var authType = new Array();
		$(".authAccess").each(function() {
			 if ($(this).prop('checked')==true){ 
				var id=$(this).val();				
				authCheck.push($(this).val());
				authType.push($('#authType_'+id).val());
			}
		});
		
		$.ajax({
			type: "POST",
			url: "ws_access.php",
			dataType: 'html',
			data: 'authorize_ws_access="authorize_ws_access"&authType='+authType+'&authCheck='+authCheck,
			success: function(data){
				location.reload();
			}
		});

	});
	

});


</script>