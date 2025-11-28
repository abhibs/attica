<?php
session_start();
$type = $_SESSION['usertype'];
include("header.php");
include("menuvmadd.php");

include("dbConnection.php");

if (isset($_POST['submitNC'])) {

    $type = 'VM-HO';
    $username = $_POST['username'];
    $password = $_POST['password'];
    $employeeId = $_POST['employeeId'];
    $branch = $_POST['branch'];
    $branch1 = '';
    $branch2 = '';
    $date=date('Y-m-d');
    $type = 'VM-HO';

    $inscon = "INSERT INTO `users`(type,username,password,employeeId,branch,agent,date,ip) 
    VALUES ('$type','$username','$password','$employeeId','$branch','$branch1','$date','$branch2')";
    mysqli_query($con,$inscon);

}
?>

<style>
    #results img {
        width: 100px;
    }
</style>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header">
                <h3 class="text-success"><b>Add Fraud</b></h3>
            </div>
            <div class="modal-body">
                
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
</div>
<div id="wrapper">
    <div class="content">
        <div class="row-content">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <!--<div class="form-group row" style="padding-left:8px;padding-right:15px;">-->
                        <!--    <div class="col-xs-3" style="margin-top:20px;">-->
                        <!--        <div data-toggle="modal" data-target="#myModal" class='btn btn-success btn-user'>-->
                        <!--            <h4><b><i style="color:#990000" class="fa fa-user"></i> Add Fraud</b></h4>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                    </div>
                    <div class="hpanel">
                        
                        <form method="POST" class="form-horizontal" action="" enctype="multipart/form-data">
                    <div class="form-group row" style="padding-left:50px;">
                        <div class="col-sm-5">
                            <label class="text-success">Username</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
                                <input type="text" name="username" placeholder="Name" required  class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <label class="text-success">Password</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-phone-square"></span></span>
                                <input type="text" name="password" style="padding:0px 5px"  required placeholder=" Password " required class="form-control" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <label class="text-success">Employee ID</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
                                <input type="text" name="employeeId" style="padding:0px 5px" placeholder="Employee ID" class="form-control" autocomplete="off">
                            </div>
                        </div>
                         <div class="col-sm-5">
                            <label class="text-success">Branch Access</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span style="color:#990000" class="fa fa-user"></span></span>
                                <input type="text" name="branch" style="padding:0px 5px" placeholder="Branch Access" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-8"><br>
                            <button class="btn btn-success" name="submitNC" type="submit"><span style="color:#ffcf40" class="fa fa-save"></span> Submit</button>
                        </div>
                    </div>
                </form>
                        
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <?php include("footer.php"); ?>
        <!-- <script src="scripts/webcam.min.js"></script> -->
        <script language="JavaScript">
            Webcam.set({
                width: 210,
                height: 160,
                image_format: 'jpeg',
                jpeg_quality: 100
            });
            Webcam.attach('#my_camera');

            function take_snapshot() {
                Webcam.snap(function(data_uri) {
                    $(".image-tag").val(data_uri);
                    document.getElementById('results').innerHTML = '<img src="' + data_uri + '"/>';
                });
            }
        </script>
    </div>