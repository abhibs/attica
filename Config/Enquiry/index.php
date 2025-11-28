<?php


error_reporting(E_ERROR | E_PARSE);

date_default_timezone_set("Asia/Kolkata");

require "../../dbConnection.php";


// submit sith mobile or system
function detectDeviceType() {
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $isMobile = preg_match('/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/', $userAgent);
    return $isMobile ? 'mobile' : 'desktop';
}



if (isset($_POST['name']) && isset($_POST['mobile']) && isset($_POST['type']) && isset($_POST['state'])) {
   
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $type = $_POST['type'];
    $state = $_POST['state'];
    
    $date = date('Y-m-d');
    $time = date("h:i:s");
    $status='Pending';
    $remarks='';
    $followup='';
    $comments='';
    $updateDate='';
    $device = detectDeviceType();
    
    $sql = "INSERT INTO enquiry (name, mobile, type, state, date, time, status, remarks,comments,updateDate, followup,device) VALUES ('$name', '$mobile', '$type', '$state', '$date', '$time', '$status', '$remarks','$comments','$updateDate','$followup','$device')";

    
    
     // Check if any field is empty
    if (empty($name) || empty($mobile) || empty($type) || empty($state)) {
       
         echo "<script>setTimeout(\"location.href = 'https://atticagold.in/Config/Enquiry/thankyou3.html';\", 150);</script>";
    
    }else{
    
    if (mysqli_query($con, $sql)) {  
        
     
        //echo "<script type='text/javascript'>alert('Thank you!!!')</script>";  
        
        //header("Location: https://atticacancercare.com/");
        
        echo "<script>setTimeout(\"location.href = 'https://atticagold.in/Config/Enquiry/thankyou.html';\", 150);</script>";
       
    
   
    } else {
        echo "<script type='text/javascript'>alert('ERROR OCCURRED!!!')</script>";
    }
}
}


?>


