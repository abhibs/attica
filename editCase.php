<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Legal') {
	include("header.php");
	include("menulegal.php");
} else {
	include("logout.php");
}
include("dbConnection.php");
$id = $_GET['id'];
$row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM cases WHERE id='$id'"));
?>
<style>
	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 16px;
		color: #123C69;
	}

	#wrapper .panel-body {
		border: 5px solid #fff;
		padding: 15px;
		box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
		background-color: #f5f5f5;
		border-radius: 3px;
	}

	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
		background-color: #fffafa;
	}

	.text-success {
		color: #123C69;
		text-transform: uppercase;
		font-weight: bold;
		font-size: 12px;
	}

	tbody {
		font-weight: 600;
	}

	.trInput:focus-within {
		outline: 3px solid #990000;
	}

	.fa {
		color: #34495e;
		font-size: 16px;
	}

	.btn {
		background-color: transparent;
	}
</style>
<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">
			<div class="hpanel">
				<div class="panel-heading">
					<h3 class="text-success"><span class="fa fa-pencil-square" style="color:#990000"></span><b> EDIT Case DETAILS</b></h3>
				</div>
				<div class="panel-body">
					<table id="user" class="table table-bordered table-striped" style="clear: both">
						<tbody>
							<!-- <tr> -->
							<!-- <th class="text-success" width="35%" style="padding-top:17px">BRANCH ID</th> -->
							<!-- <td width="65%"> -->
							<input type="hidden" name="id" id="id" class="form-control" value="<?php echo $row['id']; ?>" readonly>
							<!-- </td> -->
							<!-- </tr> -->
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">Case Name</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" autocomplete="off">
										<span class="input-group-btn">
											<button type="submit" class="btn" onclick="updateCaseData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">Case Date</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="date" name="date" class="form-control" value="<?php echo $row['date']; ?>" autocomplete="off">
										<span class="input-group-btn">
											<button type="submit" class="btn" onclick="updateCaseData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>
							<tr>
								<th class="text-success" width="35%" style="padding-top:17px">Content</th>
								<td width="65%">
									<div class="input-group trInput">
										<input type="text" name="content" class="form-control" autocomplete="off" value="<?php echo $row['content']; ?>">
										<span class="input-group-btn">
											<button type="submit" class="btn" onclick="updateCaseData(this)"><i class="fa fa-paint-brush"></i></button>
										</span>
									</div>
								</td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
	<script>
		let id = document.getElementById('id').value;

		function updateCaseData(button) {
			let colValue = button.parentNode.previousElementSibling.value,
				colName = button.parentNode.previousElementSibling.name;
			$.ajax({
				url: "editAjax.php",
				type: "POST",
				data: {
					editCase: 'editCase',
					id: id,
					colName: colName,
					colValue: colValue
				},
				success: function(e) {
					if (e == '1') {
						alert('Successfully Updated');
					} else {
						alert('Oops!!! Something went wrong');
					}
				}
			});
		}
	</script>
	<?php include("footer.php"); ?>
