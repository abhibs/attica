<?php
session_start();
$type = $_SESSION['usertype'];
if ($type != 'Software') {
    include("logout.php");
    exit;
}
include("header.php");
include("menuSoftware.php");
include("dbConnection.php");

$date = date("Y-m-d");
if (isset($_GET['getReport'])) {
    $date = $_GET['date'];
}

// Fetch city level data
$cityBillsQuery = mysqli_fetch_all(mysqli_query(
    $con,
    "SELECT b.city, COUNT(t.id) AS bills, COALESCE(SUM(netW), 0) AS netW, COALESCE(SUM(netA), 0) AS netA 
    FROM trans t 
    RIGHT JOIN branch b ON (t.branchId=b.branchId AND t.date='$date' AND t.status='Approved' AND t.metal='Gold')
    WHERE b.city IN ('Bengaluru', 'Chennai', 'Hyderabad')
    GROUP BY b.city"
), true);

// Fetch state level data
$stateBillsQuery = mysqli_fetch_all(mysqli_query(
    $con,
    "SELECT b.state, COUNT(t.id) AS bills, COALESCE(SUM(netW), 0) AS netW, COALESCE(SUM(netA), 0) AS netA 
    FROM trans t 
    RIGHT JOIN branch b ON (t.branchId=b.branchId AND t.date='$date' AND t.status='Approved' AND t.metal='Gold')
    WHERE b.city NOT IN ('Bengaluru', 'Chennai', 'Hyderabad')
    GROUP BY b.state"
), true);

$totalBills = 0;
$totalNetWeight = 0;
$totalNetAmount = 0;
$cityBillData = [];
$stateBillData = [];
foreach ($cityBillsQuery as $value) {
    $totalBills += $value['bills'];
    $totalNetWeight += $value['netW'];
    $totalNetAmount += $value['netA'];
    $cityBillData[$value['city']] = $value;
}
foreach ($stateBillsQuery as $value) {
    $totalBills += $value['bills'];
    $totalNetWeight += $value['netW'];
    $totalNetAmount += $value['netA'];
    $stateBillData[$value['state']] = $value;
}

$vmTimerQuery = mysqli_fetch_all(mysqli_query(
$con,"SELECT e.name AS agent_name,
SUM(CASE WHEN ec.vmtime <= CAST('00:05:00' AS TIME) THEN 1 ELSE 0 END) AS five,
SUM(CASE WHEN ec.vmtime > CAST('00:05:00' AS TIME) AND ec.vmtime <= CAST('00:10:00' AS TIME) THEN 1 ELSE 0 END) AS ten,
SUM(CASE WHEN ec.vmtime > CAST('00:10:00' AS TIME) AND ec.vmtime <= CAST('00:15:00' AS TIME) THEN 1 ELSE 0 END) AS fifteen,
SUM(CASE WHEN ec.vmtime > CAST('00:15:00' AS TIME) THEN 1 ELSE 0 END) AS more
FROM everycustomer ec
JOIN employee e ON ec.agent = e.empId
WHERE ec.vmtime IS NOT NULL AND ec.date = '$date' AND ec.status IN ('Billed', 'Release')
GROUP BY e.name
ORDER BY e.name"
), true);

$applTimerQuery = mysqli_fetch_all(mysqli_query(

$con,

"SELECT
u.agent AS Approval_Team,
-- Time partitions on vmtime
SUM(CASE WHEN t.livetimer <= CAST('00:05:00' AS TIME) THEN 1 ELSE 0 END) AS five,
SUM(CASE WHEN t.livetimer > CAST('00:05:00' AS TIME) AND t.livetimer <= CAST('00:10:00' AS TIME) THEN 1 ELSE 0 END) AS ten,
SUM(CASE WHEN t.livetimer > CAST('00:10:00' AS TIME) AND t.livetimer <= CAST('00:15:00' AS TIME) THEN 1 ELSE 0 END) AS fifteen,
SUM(CASE WHEN t.livetimer > CAST('00:15:00' AS TIME) THEN 1 ELSE 0 END) AS more
FROM trans t
JOIN users u ON t.remarks = u.username
WHERE t.livetimer IS NOT NULL
AND t.date = CURDATE()
GROUP BY u.agent
ORDER BY u.agent"
), true);

// Fetch enquiry data
$stateEnquiryQuery = mysqli_fetch_all(mysqli_query(
    $con,
    "SELECT b.state, COUNT(DISTINCT mobile) AS enquiry
    FROM walkin w
    RIGHT JOIN branch b ON (w.branchId=b.branchId AND w.date='$date' AND w.issue!='Rejected')
    GROUP BY b.state"
), true);

$totalEnquiry = 0;
$stateEnquiryData = [];
foreach ($stateEnquiryQuery as $value) {
    $totalEnquiry += $value['enquiry'];
    $stateEnquiryData[$value['state']] = $value;
}

$totalWalkin = $totalBills + $totalEnquiry;
$COG = $totalWalkin > 0 ? round(($totalBills / $totalWalkin) * 100, 2) : 0;
$WCOG = $totalWalkin > 0 ? round((($totalBills + ($totalEnquiry * 0.5)) / $totalWalkin) * 100, 2) : 0;

$approvalQuery = mysqli_fetch_all(mysqli_query(
    $con,
    "SELECT t.remarks, u.agent AS agent_name, COUNT(*) AS bills
    FROM trans t
    LEFT JOIN users u ON t.remarks = u.employeeId
    WHERE t.date='$date' AND t.status='Approved' AND t.metal='Gold' AND u.type='ApprovalTeam'
    GROUP BY t.remarks, u.agent
    ORDER BY bills DESC"
), true);

$impslTimerQuery = mysqli_fetch_all(mysqli_query(
$con,
"SELECT
u.agent AS IMPS_Team,
-- Time partitions on vmtime
SUM(CASE WHEN t.accTimer <= CAST('00:05:00' AS TIME) THEN 1 ELSE 0 END) AS five,
SUM(CASE WHEN t.accTimer > CAST('00:05:00' AS TIME) AND t.accTimer <= CAST('00:10:00' AS TIME) THEN 1 ELSE 0 END) AS ten,
SUM(CASE WHEN t.accTimer > CAST('00:10:00' AS TIME) AND t.accTimer <= CAST('00:15:00' AS TIME) THEN 1 ELSE 0 END) AS fifteen,
SUM(CASE WHEN t.accTimer > CAST('00:15:00' AS TIME) THEN 1 ELSE 0 END) AS more
FROM trans t
JOIN users u ON t.imps_empid = u.employeeId
WHERE t.accTimer IS NOT NULL
AND t.date = CURDATE()
GROUP BY u.agent
ORDER BY u.agent"
), true);

?>

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        background: #f8f8f8;
    }

    .report-container {
        width: 700px;
        margin: 20px auto;
        background: #fff;
        padding: 20px;
        box-shadow: 0 0 10px #ccc;
        border-radius: 8px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 15px;
    }

    th,
    td {
        border: 1px solid #999;
        padding: 5px 8px;
        text-align: center;
        font-size: 12px;
    }

    th {
        background-color: #004080;
        color: #fff;
    }

    .subheader {
        background-color: rgb(255, 252, 252);
        font-weight: bold;
    }

    .section-title {
        background-color: #004080;
        color: white;
        text-align: left;
        padding-left: 10px;
        font-size: 13px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        font-size: 18px;
    }

    h3 {
        background-color: #004080;
        color: white;
        padding: 8px;
        font-size: 14px;
        margin-bottom: 10px;
    }
</style>
<br><br><br><br>
<div class="report-container">
    <h2>DAILY REPORT</h2>

    <table>
        <tr>
            <th>WalkIn</th>
            <th>Bills</th>
            <th>COG (%)</th>
            <th>WCOG (%)</th>
        </tr>
        <tr>
            <td><?= $totalWalkin ?></td>
            <td><?= $totalBills ?></td>
            <td><?= $COG ?></td>
            <td><?= $WCOG ?></td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="6"><?= date('d-m-Y', strtotime($date)) ?></th>
            <th>NET WEIGHT</th>
        </tr>

        <!-- Karnataka -->
        <?php
        $bengaluruNetW = round($cityBillData['Bengaluru']['netW'] ?? 0);
        $karnatakaOtherNetW =  round($stateBillData['Karnataka']['netW'] ?? 0);
        $totalKarnatakaNetW = $karnatakaOtherNetW+$bengaluruNetW;

        $karEnquiry = $stateEnquiryData['Karnataka']['enquiry'] ?? 0;
        $karBills = ($cityBillData['Bengaluru']['bills'] ?? 0) + ($stateBillData['Karnataka']['bills'] ?? 0);
        $karWalkin = $karBills + $karEnquiry;
        $karCOG = $karWalkin > 0 ? round(($karBills / $karWalkin) * 100, 2) : 0;
        $karWCOG = $karWalkin > 0 ? round((($karBills + ($karEnquiry * 0.5)) / $karWalkin) * 100, 2) : 0;
        ?>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Bengaluru </td>
            <td colspan="3"><?= $bengaluruNetW ?></td>
        </tr>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Karnataka </td>
            <td colspan="3"><?= $karnatakaOtherNetW ?></td>
        </tr>
        <tr>
            <td>WalkIn: <?= $karWalkin ?></td>
            <td>Bills: <?= $karBills ?></td>
            <td>COG: <?= $karCOG ?>%</td>
            <td>WCOG: <?= $karWCOG ?>%</td>
            <td colspan="3" style='background-color:rgb(218, 212, 212);'><?= $totalKarnatakaNetW ?></td>
        </tr>

        <!-- Telangana -->
        <?php
        $hyderabadNetW = round($cityBillData['Hyderabad']['netW'] ?? 0);
        $telanganaOtherNetW = round(($stateBillData['Telangana']['netW'] ?? 0) - $hyderabad );
        $andrapradeshNetW = round(($stateBillData['Andhra Pradesh']['netW'] ?? 0));
        $totalTelanganaNetW = $hyderabadNetW + $telanganaOtherNetW + $andrapradeshNetW;



        $tsEnquiry = ($stateEnquiryData['Telangana']['enquiry'] ?? 0) + ($stateEnquiryData['Andhra Pradesh']['enquiry'] ?? 0);
        $tsBills = ($cityBillData['Hyderabad']['bills'] ?? 0) + ($stateBillData['Telangana']['bills'] ?? 0) + ($stateBillData['Andhra Pradesh']['bills'] ?? 0);
        $tsWalkin = $tsBills + $tsEnquiry;
        $tsCOG = $tsWalkin > 0 ? round(($tsBills / $tsWalkin) * 100, 2) : 0;
        $tsWCOG = $tsWalkin > 0 ? round((($tsBills + ($tsEnquiry * 0.5)) / $tsWalkin) * 100, 2) : 0;
        ?>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Hyderabad </td>
            <td colspan="3"><?= $hyderabadNetW ?></td>
        </tr>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Telangana </td>
            <td colspan="3"><?= $telanganaOtherNetW ?></td>
        </tr>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Andhra Pradesh</td>
            <td colspan="3"><?= $andrapradeshNetW ?></td>
        </tr>
        <tr>
            <td>WalkIn: <?= $tsWalkin ?></td>
            <td>Bills: <?= $tsBills ?></td>
            <td>COG: <?= $tsCOG ?>%</td>
            <td>WCOG: <?= $tsWCOG ?>%</td>
            <td colspan="3" style='background-color:rgb(218, 212, 212);'> <?= $totalTelanganaNetW ?></td>
        </tr>

        <!-- Tamil Nadu -->
        <?php
        $chennaiNetW = round($cityBillData['Chennai']['netW'] ?? 0);
        $pondicherryNetW = round(($stateBillData['Pondicherry']['netW'] ?? 0));
        $tamilOtherNetW = round( ($stateBillData['Tamilnadu']['netW'] ?? 0));
        $totalTamilNetW = $chennaiNetW + $tamilOtherNetW+ $pondicherryNetW;


        $tnEnquiry = ($stateEnquiryData['Tamilnadu']['enquiry'] ?? 0) + ($stateEnquiryData['Pondicherry']['enquiry'] ?? 0);
        $tnBills = ($cityBillData['Chennai']['bills'] ?? 0) + ($stateBillData['Tamilnadu']['bills'] ?? 0) + ($stateBillData['Pondicherry']['bills'] ?? 0);
        $tnWalkin = $tnBills + $tnEnquiry;
        $tnCOG = $tnWalkin > 0 ? round(($tnBills / $tnWalkin) * 100, 2) : 0;
        $tnWCOG = $tnWalkin > 0 ? round((($tnBills + ($tnEnquiry * 0.5)) / $tnWalkin) * 100, 2) : 0;
        ?>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Chennai </td>
            <td colspan="3"><?= $chennaiNetW ?></td>
        </tr>
        <tr class="subheader">
            <td colspan="4" style='text-align: left;'>Tamil Nadu </td>
            <td colspan="3"><?= $tamilOtherNetW ?></td>
        </tr>
        <tr class="subheader">
            <td colspan="4">Pondicherry</td>
            <td colspan="3"><?= $pondicherryNetW ?></td>
        </tr>
        <tr>
            <td>WalkIn: <?= $tnWalkin ?></td>
            <td>Bills: <?= $tnBills ?></td>
            <td>COG: <?= $tnCOG ?>%</td>
            <td>WCOG: <?= $tnWCOG ?>%</td>
            <td colspan="3" style='background-color:rgb(218, 212, 212);'> <?= $totalTamilNetW ?></td>
        </tr>
        </td>
        <td>
            <tr>
                <td colspan="5" class="section-title">GRAND TOTAL</td>
                <td colspan="2" style='background-color:rgb(218, 212, 212);'><?= round($totalNetWeight) ?></td>
            </tr>
            <tr>
                <td colspan="5" class="section-title">TOTAL NET AMOUNT</td>
                <td colspan="2" style='background-color:rgb(218, 212, 212);'><?= number_format($totalNetAmount, 0) ?>
                </td>
            </tr>
    </table>

    <h3>BILL APPROVAL COUNT</h3>
    <table>
        <tr>
            <th>SL.No</th>
            <th>Name</th>
            <th>Count</th>
        </tr>
        <?php $i = 1;
        foreach ($approvalQuery as $row) { ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $row['agent_name'] ?></td>
                <td><?= $row['bills'] ?></td>
            </tr>
        <?php } ?>
    </table>
<h3>APPROVAL TIMER COUNT</h3>
        <table>
        <tr>
                <th>SL.No</th>
                <th>Name</th>
                <th>0-5 Min</th>
                <th>5-10 Min</th>
                <th>10-15 Min</th>
                <th>>15 Min</th>
        </tr>
        <?php $i = 1;
        foreach ($applTimerQuery as $row) { ?>
        <tr>
                <td><?= $i++ ?></td>
                <td><?= $row['Approval_Team'] ?></td>
                <td><?= $row['five'] ?></td>
                <td><?= $row['ten'] ?></td>
                <td><?= $row['fifteen'] ?></td>
                <td><?= $row['more'] ?></td>
        </tr>
        <?php } ?>
</table>
<h3>ACCOUNT TIMER COUNT</h3>

<table>
	<tr>
		<th>SL.No</th>
		<th>Name</th>
		<th>0-5 Min</th>
		<th>5-10 Min</th>
		<th>10-15 Min</th>
		<th>>15 Min</th>
	</tr>
	<?php $i = 1;
	foreach ($impslTimerQuery as $row) { ?>
	<tr>
		<td><?= $i++ ?></td>
		<td><?= $row['IMPS_Team'] ?></td>
		<td><?= $row['five'] ?></td>
		<td><?= $row['ten'] ?></td>
		<td><?= $row['fifteen'] ?></td>
		<td><?= $row['more'] ?></td>
	</tr>
	<?php } ?>
</table>	

<h3>VM TIMER COUNT</h3>
<table>
	<tr>
		<th>SL.No</th>
		<th>Name</th>
		<th>0-5 Min</th>
		<th>5-10 Min</th>
		<th>10-15 Min</th>
		<th>>15 Min</th>
	</tr>

	<?php $i = 1;
		foreach ($vmTimerQuery as $row) { ?>
		<tr>
			<td><?= $i++ ?></td>
			<td><?= $row['agent_name'] ?></td>
			<td><?= $row['five'] ?></td>
			<td><?= $row['ten'] ?></td>
			<td><?= $row['fifteen'] ?></td>
			<td><?= $row['more'] ?></td>
		</tr>
	<?php } ?>
</table>

</div>
