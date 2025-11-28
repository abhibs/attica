<?php
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set("Asia/Kolkata");

require "../../dbConnection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    var_dump($_POST);

    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $location = $_POST['location'];

    $type = $_POST['type'];

    $date = date('Y-m-d');
    $time = date("h:i:s");
    $status = 'Pending';

    
    var_dump($_FILES['resume']);

    if (isset($_FILES['resume'])) {
        $resume = $_FILES['resume']['name'];
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_error = $_FILES['resume']['error'];

        if ($file_error !== UPLOAD_ERR_OK) {
            echo "File upload error: $file_error";
        } else {
            $file_extn = pathinfo($resume, PATHINFO_EXTENSION);
            $folder = "../../jobDocuments/";
            $filename = date('Ymdhis');
            $final_file = $filename . 'resume' . '.' . $file_extn;

            
            if (move_uploaded_file($file_tmp, $folder . $final_file)) {
                $sql = "INSERT INTO job (name, mobile, email, location, resume, date, time, status, type) VALUES ('$name', '$mobile', '$email', '$location', '$final_file', '$date', '$time', '$status', '$type')";
                
                //echo "SQL Query: $sql";

                if (mysqli_query($con, $sql)) {
                    echo "<script>setTimeout(\"location.href = 'https://atticagold.in/Config/Enquiry/thankyou1.html';\", 150);</script>";
                } else {
                    echo "<script type='text/javascript'>alert('Error: " . mysqli_error($con) . "')</script>";
                }
            } else {
                echo "File upload failed.";
            }
        }
    } else {
        echo "No file uploaded.";
    }
}
?>
