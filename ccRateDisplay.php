<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];

if ($type == 'CallDisplay' || $type == 'Master' || $type == 'Software' || $type == 'Call Centre') {

	include("dbConnection.php");

	$data = [];
	$sql = mysqli_query($con, "SELECT * FROM misc WHERE purpose IN ('Gold Rate','Silver Rate','KA Closed','TN Closed','APT Closed','Special Rate')");
	while ($row = mysqli_fetch_assoc($sql)) {
		switch ($row['purpose']) {
			case "Gold Rate":
				$data['goldRate'] = $row['day'];
				break;

			case "Silver Rate":
				$data['silverRate'] = $row['day'];
				break;

			case "KA Closed":
				$data['kaClosed'] = $row['day'];
				break;

			case "TN Closed":
				$data['tnClosed'] = $row['day'];
				break;

			case "APT Closed":
				$data['aptClosed'] = $row['day'];
				break;

			case "Special Rate":
				$data['special'] = $row['day'];
				break;

			default:
				break;
		}
	}

	$kaList = ($data['kaClosed'] != "") ? explode(",", $data['kaClosed']) : [];
	$tnList = ($data['tnClosed'] != "") ? explode(",", $data['tnClosed']) : [];
	$aptList = ($data['aptClosed'] != "") ? explode(",", $data['aptClosed']) : [];

	$str = "";
	if (count($kaList) > 0) {
		$str .= join(",", array_map(function ($ka) {
			return "'" . $ka . "'";
		}, $kaList)) . ",";
	}
	if (count($tnList) > 0) {
		$str .= join(",", array_map(function ($ka) {
			return "'" . $ka . "'";
		}, $tnList)) . ",";
	}
	if (count($aptList) > 0) {
		$str .= join(",", array_map(function ($ka) {
			return "'" . $ka . "'";
		}, $aptList));
	}

	$branchData = [];
	$branchSql = mysqli_query($con, "SELECT branchId, branchName FROM branch WHERE branchId IN (" . $str . ")");
	while ($row = mysqli_fetch_assoc($branchSql)) {
		$branchData[$row['branchId']] = $row['branchName'];
	}

?>
	<!DOCTYPE html>
	<html>

	<!-- Mirrored from webapplayers.com/homer_admin-v2.0/light-shadow/register.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 14 Nov 2017 04:58:07 GMT -->

	<head>

		<meta http-equiv="refresh" content="900">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<!-- Page title -->
		<title>Attica Gold</title>

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
		<link rel="shortcut icon" type="image/png" href="images/favicon.png" />

		<!-- Vendor styles -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

		<style>
			.tr-back {
				background-color: #080d38 !important;
			}

			.card .list-group-item {
				font-weight: 700;
				font-size: 20px;
			}

			.card .list-group-item-tn {
				background-color: #6A1E55 !important;
			}

			.card .list-group-item-ka {
				background-color: #003161 !important;
			}

			.card .list-group-item-apt {
				background-color: #9B3922 !important;
			}
		</style>

	</head>

	<body>

		<div class="container-fluid" style="height: 100vh">
			<div class="row h-100">

				<div class="col-lg-7 h-100 d-flex flex-column justify-content-center align-items-center pt-4" style="background-color: #080d38;">
					<div class="d-flex justify-content-evenly w-100 border-bottom">
						<h1><span class="badge text-bg-success text-white rounded-0">Gold : <?php echo $data['goldRate'] ?></span></h1>
						<h1><span class="badge text-bg-success text-white rounded-0">Silver : <?php echo $data['silverRate'] ?></span></h1>
					</div>
					<div class="d-flex justify-content-evenly w-100 mt-1">
						<div class="card" style="width: 95%; background-color: transparent">
							<div class="card-body p-0">
								<h4 class="card-title text-center p-1 text-white" style="font-weight: 700;">Special Rate : <?php echo $data['special']; ?></h4>
								<div class="d-flex justify-content-evenly w-100">
									<ul class="list-group list-group-flush" style="width: 33%">
										<li class="list-group-item list-group-item-tn p-1 text-white">T Nagar</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Tambaram</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Velachery</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Padi</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Perambur</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Avadi</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Valasaravakkam</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">ECR</li>
										<li class="list-group-item list-group-item-tn p-1 text-white">Ashok Pillar</li>
									</ul>
									<ul class="list-group list-group-flush" style="width: 33%">
										<li class="list-group-item list-group-item-ka p-1 text-white">Bannerghatta</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Basaveshwara Nagar</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Hosa road</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Indiranagar</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Peenya-Jalahalli</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Jayanagar</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Kathriguppe</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Kengeri</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Lingarajapuram</li>
									</ul>
									<ul class="list-group list-group-flush" style="width: 33%">
										<li class="list-group-item list-group-item-ka p-1 text-white">Marathalli</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Yelahanka</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">KR Puram</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Whitefield</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Seva nagar</li>
										<li class="list-group-item list-group-item-ka p-1 text-white">Ganganagar(CBI)</li>
										<li class="list-group-item list-group-item-apt p-1 text-white">All Hyderabad</li>
										<li class="list-group-item list-group-item-apt p-1 text-white">All Vishakapatnam</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-5 h-100 d-flex flex-column justify-content-center align-items-center pt-4" style="background-color: #CDC2A5;">
					<h4 class="text-center">Closed Branches</h4>
					<ul class="list-group m-1 list-group-flush" style="width: 70%">
						<li class="list-group-item disabled active text-center tr-back" aria-disabled="true"><b>KA</b></li>
						<?php
						foreach ($kaList as $key => $value) {
							echo '<li class="list-group-item h5 m-0">' . $branchData[$value] . '</li>';
						}
						?>
					</ul>
					<ul class="list-group m-1 list-group-flush" style="width: 70%">
						<li class="list-group-item disabled active text-center tr-back" aria-disabled="true"><b>TN</b></li>
						<?php
						foreach ($tnList as $key => $value) {
							echo '<li class="list-group-item h5 m-0">' . $branchData[$value] . '</li>';
						}
						?>
					</ul>
					<ul class="list-group m-1 list-group-flush" style="width: 70%">
						<li class="list-group-item disabled active text-center tr-back" aria-disabled="true"><b>AP & TS</b></li>
						<?php
						foreach ($aptList as $key => $value) {
							echo '<li class="list-group-item h5 m-0">' . $branchData[$value] . '</li>';
						}
						?>
					</ul>
				</div>

			</div>
		</div>

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>

	</html>
<?php
} else {
	include("logout.php");
}
?>