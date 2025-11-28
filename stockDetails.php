<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];

if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
}  else {
	include("logout.php");
}
include("dbConnection.php");

// $date = date("Y-m-d");
if(isset($_POST['stockDetails'])) {
		$date = $_POST['fromDate'];
	}
	else {
		$date = date('Y-m-d');
	}
$emp_id = $_SESSION['employeeId'];
?>
<style>
	#wrapper {
		background: #f5f5f5;
	}

	#wrapper h3 {
		text-transform: uppercase;
		font-weight: 600;
		font-size: 20px;
		color: #123C69;
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

	.fa_Icon {
		color: #ffa500;
	}

	thead {
		text-transform: uppercase;
		background-color: #123C69;
	}

	thead tr {
		color: #f2f2f2;
		font-size: 12px;
	}

	.dataTables_empty {
		text-align: center;
		font-weight: 600;
		font-size: 12px;
		text-transform: uppercase;
	}

	.btn-success {
		display: inline-block;
		padding: 0.7em 1.4em;
		margin: 0 0.3em 0.3em 0;
		border-radius: 0.15em;
		box-sizing: border-box;
		text-decoration: none;
		font-size: 12px;
		font-family: 'Roboto', sans-serif;
		text-transform: uppercase;
		color: #fffafa;
		background-color: #123C69;
		box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
		text-align: center;
		position: relative;
	}
</style>

<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
	<?php
	$branches = mysqli_query($con, "SELECT branchId,branchName FROM branch where status=1");
	while ($branchList = mysqli_fetch_array($branches)) {
		?>
		<option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
	<?php } ?>
</datalist>


<div id="wrapper">
	<div class="row content">
		<div class="col-lg-12">

			
			<div class="hpanel mt-5">
				<div class="panel-heading">
					<div class="col-sm-8">
						<h3>
							<i class="trans_Icon fa fa-edit"></i>
							Stock REPORT
							<span style='color:#990000'><?php echo " - " . $date; ?></span>
						</h3>
					</div>
					<div class="col-sm-4" style="margin-top: 5px, margin-top: 5px;">
						<form action="" method="POST">
							<div class="input-group">
								<input name="fromDate" value="" type="date" class=" form-control" required />
								<span class="input-group-btn">
									<input name="stockDetails" class="btn btn-primary btn-block" value="Search"
										type="submit" style="font-size: 11px;">
								</span>
							</div>
						</form>
					</div>

				</div>
			</div>
		

			<div class="hpanel mt-5">
				<div class="panel-body"
					style="box-shadow: rgb(50 50 93 / 25%) 0px 6px 12px -2px, rgb(0 0 0 / 30%) 0px 3px 7px -3px;border-radius: 10px;">
					<table id="example5" class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th><i class="fa fa-sort-numeric-asc"></i></th>
								<th>Item / Product</th>
								<th>Specification / Standards</th>
								<th>Item Count</th>
								<th>Location</th>
								<th>Responsible</th>
								<th>Assign To</th>
								<th>Vehicle Number</th>
								<th>Upload Photo</th>
							</tr>
						</thead>
						<tbody>

							<?php
							$i = 1;
							$query = mysqli_query($con, "SELECT * FROM `stockitem` WHERE `date`='$date'");

							while ($row = mysqli_fetch_assoc($query)) {
								echo "<tr>";
								echo "<td>" . $i . "</td>";
								echo "<td>" . $row['stock'] . "</td>";
								echo "<td>" . $row['standards'] . "</td>";
								echo "<td>" . $row['count'] . "</td>";
								echo "<td>" . $row['location'] . "</td>";
								echo "<td>" . $row['responsible'] . "</td>";
								echo "<td>" . $row['assign'] . "</td>";
								echo "<td>" . $row['vehicle'] . "</td>";
								echo "<td class='text-center'><a class='btn btn-success' target='_blank' href='StockManagement/" . $row['image'] . "'><i style='color:#ffcf40' class='fa fa-file-o'></i> Image</a></td>";
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
</div>


<?php include("footer.php"); ?>
