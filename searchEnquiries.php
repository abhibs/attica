<?php

session_start();
include("dbConnection.php");

$type = $_SESSION['usertype'] ?? '';
$mobile = isset($_GET['mobile']) ? trim($_GET['mobile']) : '';


$branchData = [];
$branchQuery = mysqli_query($con, "SELECT branchId, branchName FROM branch");
while ($b = mysqli_fetch_assoc($branchQuery)) {
    $branchData[$b['branchId']] = $b['branchName'];
}

if ($mobile != '' && strlen($mobile) == 10) {
    $sql = mysqli_query($con, "SELECT * FROM walkin WHERE mobile = '$mobile' AND issue NOT IN ('Rejected') ORDER BY date DESC, id DESC");
    $i = 1;
    $rows = "";
    while ($row = mysqli_fetch_assoc($sql)) {
        $rows .= "<tr>";
        $rows .= "<td>" . $i . "</td>";
        $rows .= "<td>" . ($branchData[$row['branchId']] ?? '-') . "</td>";
        $rows .= "<td>" . $row['name'] . "</td>";
        $rows .= "<td>" . $row['mobile'] . "</td>";
        $rows .= "<td>" . $row['gold'] . "</td>";
        $rows .= "<td>" . $row['havingG'] . "</td>";
        $rows .= "<td>" . $row['metal'] . "</td>";
        $rows .= "<td>" . $row['gwt'] . "</td>";
        $rows .= "<td>" . $row['ramt'] . "</td>";
        $rows .= "<td>" . $row['remarks'] . "</td>";
        $rows .= "<td>" . $row['zonal_remarks'] . "</td>";
        $rows .= "<td>" . $row['comment'] . "</td>";
        $rows .= "<td>" . $row['issue'] . "</td>";
        $rows .= "<td>" . $row['agent_id'] . "<br>" . $row['followUp'] . "</td>";
        $rows .= "<td>" . $row['date'] . "</td>";
        $rows .= "<td>" . $row['time'] . "</td>";

        $editLink = ($type == 'SubZonal') ? "enquiryZonalRemark.php" : "enquiryComment.php";
        $rows .= "<td><b><a class='text-success' href='$editLink?mobile={$row['mobile']}&id={$row['id']}'><span class='fa fa-edit'></span></a></b></td>";

        $rows .= "</tr>";
        $i++;
    }

    if ($rows == "") {
        echo "<tr><td colspan='17' style='color:red;'>No enquiries found for this mobile.</td></tr>";
    } else {
        echo $rows;
    }
}
?>

