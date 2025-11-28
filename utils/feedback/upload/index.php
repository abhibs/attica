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
                color: #000000 !important;
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
                    <h2 class="tm-section-header">Capture & Upload these Images</h2>
                </section>
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="branchId" value="<?php echo $branchId; ?>">
                    <input type="hidden" name="date" value="<?php echo $sms_date; ?>">
                    <?php $i=1; foreach($sms_arr as $value){ ?>
                        <section class="tm-section less-padding">
                            <h2 class="tm-section-header less-padding less-margin"><?php echo $value; ?> <br><small>(Upload only if it exists)</small></h2>
                            <div class="tm-special-items">
                                <figure class="tm-special-item" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                                    <div class="result" style="position:absolute; left: 50px;"></div>
                                    <div class="camera-attach"></div>
                                    <figcaption>
                                        <span class="tm-item-name" style="flex-direction: row;">
                                            <button type="button" class="capture-btn" onclick="take_snapshot(this)">Capture Image</button>
                                            <input type="text" name=<?php echo "image-".$i; ?> class="image-tag" style="opacity:0; width:0;">
                                        </span>                               
                                    </figcaption>
                                </figure>
                            </div>
                        </section>
                        <hr>
                        <br>
                    <?php $i++; } ?>
                    <section class="tm-section less-padding" style="text-align: center;">
                        <button type="submit" class="submit-btn">Upload Images</button>
                    </section>
                    <br>
                </form>
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
         <!-- Webcam JS File -->
        <script src="../../../scripts/webcam.min.js"></script>
        <script type="text/javascript">
            Webcam.set({
                width: 300,
                height: 300,
                image_format: 'jpeg',
                jpeg_quality: 100,
                constraints: {
                    facingMode: 'environment'
                }
            });         
            document.querySelectorAll(".camera-attach").forEach((camera, i)=>{
                Webcam.attach(camera);
            });
            function take_snapshot(btn) {
                const image_input = btn.parentElement.querySelector(".image-tag");
                const result = btn.parentElement.parentElement.parentElement.querySelector(".result");
                // console.log(image_input, result);
                Webcam.snap(function(data_uri) {
                    image_input.value = data_uri;
                    result.innerHTML = '<img src="' + data_uri + '"/>';
                });
            }
        </script>
    </body>
</html>