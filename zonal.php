<?php
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type = $_SESSION['usertype'];
	if($type=="Master"){
        include("header.php");
    	include("menumaster.php");
    }
    else if($type=='AccHead'){
	    include("header.php");
	    include("menuaccHeadPage.php");
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
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><i class="fa_Icon fa fa-check-square-o"></i> Authorize Zonal Access </h3>
				</div>
				<div class="panel-body" style="border: 5px solid #fff;border-radius: 10px;padding: 20px;box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;background-color: #F5F5F5;">
					<div class="table-responsive">
						<table id="authAccess-datatable" class="table table-striped table-bordered">
							<thead>
								<tr class="theadRow">
									<th><i class="fa fa-sort-numeric-asc"></i></th>
									<th>DESIGNATION</th>
									<th>USER</th>
									<th style='text-align:center;'><input type="checkbox" name="select_all"  id="select_all" /></th>
									<th style='text-align:center;'>STATUS</th>
									<th style='text-align:center'>DELETE USER</th>
								</tr>
							</thead>
							<tbody id="authAccessResponse">
								<?php
									$query = mysqli_query($con,"SELECT * FROM (SELECT * FROM users WHERE type='SubZonal') A JOIN (SELECT empId, name FROM employee GROUP BY empId, name) B ON A.employeeId=B.empId");
									$i = 1;
									while($row = mysqli_fetch_assoc($query)){
										echo "<tr>";
										echo "<td>" . $i . "</td>";
										echo "<td>" . $row['branch'] . " ". $row['type'] ."</td>";
										echo "<td  style='text-transform:uppercase;'>" . $row['name'] . "</td>";

										echo "<td style='text-align:center'><input type='checkbox' class='authAccess' name='access[]' id='box_".$row['id']."' value='".$row['id']."'/></td>";
										if($row['date'] != '' && $row['date'] != $date){
											echo "<td style='text-align:center;color:#e74c3c;'><i class='fa fa-lock' aria-hidden='true'></i> Locked <input type='hidden' name='authType' id='authType_".$row['id']."' value='access-denied'/></td>";
										}
										else{
											echo "<td style='text-align:center;color:#008000;'><i class='fa fa-check' aria-hidden='true'></i> Unlocked <input type='hidden' name='authType' id='authType_".$row['id']."' value='access-granted'/></td>";
										}
										echo "<td style='text-align:center'><button class='btn btn-danger' onclick='deleteZonal(".$row['id'].")'><i class='fa fa-trash' aria-hidden='true' title='Delete this user'></i> </button></td>";
										echo "</tr>";
										$i++;
									}
								?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="3">
										<input type="hidden" name="un" id="un" value="<?php echo $_SESSION['login_username'];?>"/>
										<input type="hidden" name="ty" id="ty" value="<?php echo $_SESSION['usertype'];?>"/>
									</td>
									<td align="center">									
										<button class='btn btn-success' id="accessBtn">Submit</button>
									</td>
									<td colspan="2"></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
<?php include("footer.php"); ?>
<script>
function deleteZonal(zonal){
	
	var answer = window.confirm("Do you wish to delete this user?");
	if (answer){
		var un=$("#un").val();
		var ty=$("#ty").val();
		$.ajax({
			type: "POST",
			url: "edit.php",
			data: {action:'deleteZonal',zonal:zonal,un:un,ty:ty},
			success: function(response){
				if(response=="SUCCESS"){
					alert("The user data has been deleted successfully");
				}else{
					alert("Oops!! Error in deleting data");
				}
			}
		});
		location.reload();
	}
	
}
$(document).ready(function(){

	$('#select_all').click(function() {
	   if (this.checked) {
		  $('input[type="checkbox"]').each(function() {
			 this.checked = true;
		  });
	   } else {
		  $('input[type="checkbox"]').each(function() {
			 this.checked = false;
		  });
	   }
	});
	
	$('.authAccess').click(function() {	
		if ($('.authAccess:checked').length == $('.authAccess').length) {
			$('#select_all').prop('checked', true);
			} else {
			$('#select_all').prop('checked', false);
		}
	});

	$('#accessBtn').click(function() {
		var un=$("#un").val();
		var ty=$("#ty").val();
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
			url: "edit.php",
			dataType: 'html',
			data: 'authorize_access="authorize_access"&un='+un+'&ty='+ty+'&authType='+authType+'&authCheck='+authCheck,
			success: function(data){
				$('#select_all').removeAttr('checked')
				$('#authAccessResponse').html(data);
			}
		});

	});
	

});


</script>
