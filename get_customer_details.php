
<?php
include("dbConnection.php");

if (isset($_POST['searchId'])) {
    $searchId = mysqli_real_escape_string($con, $_POST['searchId']);
    
    $stmt = mysqli_prepare($con, "SELECT billId, invoiceId, name, contact,amount,rateAmount FROM pledge_bill WHERE invoiceId = ? OR billId = ?");
    mysqli_stmt_bind_param($stmt, "ss", $searchId, $searchId);
    mysqli_stmt_execute($stmt);

    // Bind the result columns to variables
    mysqli_stmt_bind_result($stmt, $billId, $invoiceId, $name, $contact,$amount,$rateAmount);

    // Fetch the results
    mysqli_stmt_fetch($stmt);

    // Check if any data was found
    if ($billId === null) {
        echo json_encode(['error' => 'Customer details not found']);
    } else {
        // Return the data as JSON
        echo json_encode([
            'billId' => $billId,
            'invoiceId' => $invoiceId,
            'name' => $name,
            'contact' => $contact,
            'amount'=> $amount,
            'rateAmount'=>$rateAmount
        ]);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}
?>