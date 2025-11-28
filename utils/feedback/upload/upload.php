<?php

	// print_r($_POST);
	$branchId = $_POST['branchId'];
	$date = $_POST['date'];

	if (!file_exists('../../../FeedbackImages/'.$date)) {
    	mkdir('../../../FeedbackImages/'.$date, 0777, true);
	}

	foreach($_POST as $key=>$value){
		$sub_str = substr($key, 0, 5);		
		if($value != "" && $sub_str !== false && $sub_str == "image"){
			$image_parts = explode(";base64,", $value);
			$image_base64 = base64_decode($image_parts[1]);			

			$file_path = "../../../FeedbackImages/".$date."/".$branchId."&".$date."&".$key.".jpg";

			file_put_contents($file_path, $image_base64);
		}
	}

?>
<?php   
    session_start();
    date_default_timezone_set("Asia/Kolkata");

    include("dbConnection.php");
    $current_date = date("Y-m-d");

    $branchId = $_GET['branch'];
    $sms_date = $_GET['date'];
    $sms_string = $_GET['ids'];
    $sms_arr = explode(",", $sms_string);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>Attica Gold</title>
        <link rel="shortcut icon" type="image/png" href="../../../images/favicon.png" />
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600&display=swap" rel="stylesheet" />
        <link href="fontawesome/css/all.min.css" rel="stylesheet" />
        <link href="css/tooplate-chilling-cafe.css" rel="stylesheet" />
        <style type="text/css">
            .less-padding{
                padding-top: 3px;
                padding-bottom: 3px;
            }
            .less-margin{
                margin-top: 3px;
                margin-bottom: 3px;
            }
            body{
                background-image: linear-gradient(rgba(2,0,36,1) 0%, rgba(5,3,66,1) 95%, rgba(29,6,56,1) 100%);
            }
            .capture-btn{
                border: none;
                background-image: linear-gradient(135deg, #800793, #07CFBE);
                padding: 10px 20px;
                color: #EEEEEE;
            }
            .submit-btn{
                width: 200px;
                border-image: linear-gradient(135deg, #800793, #07CFBE) 1;
                border-width: 3px;
                border-style: solid;
                background-color: transparent;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .result img{
                width:100px;
            }
        </style>
    </head>
    <body>
        <div class="tm-container">
            <div class="tm-text-white tm-page-header-container">
                <h1 class="tm-page-header">Attica Gold</h1>
            </div>
            <div class="tm-main-content">
                <section class="tm-section less-padding">
                    <h2 class="tm-section-header">Images Uploaded Successfully <br> Thank you for taking the survey.</h2>
                </section>               
            </div>
            <footer>
                <p class="tm-text-white tm-footer-text">
                    Attica Gold Pvt Ltd
                    <br>
                    <a href="atticagoldcompany.com" class="tm-footer-link">ISO 9001:2015 Certified Company</a>
                </p>
            </footer>
        </div>
        <script src="js/jquery-3.4.1.min.js"></script>
    </body>
</html>