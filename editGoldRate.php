<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else if ($type == 'ApprovalTeam') {
    include("header.php");
    include("menuapproval.php");
} else if ($type == 'AccHead') {
    include("header.php");
    include("menuaccHeadPage.php");
} else if ($type == 'Zonal') {
    include("header.php");
    include("menuZonal.php");
} else if ($type == 'SubZonal') {
    include("header.php");
    include("menuSubZonal.php");
} else if ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} else if ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} else if ($type == 'Accounts IMPS') {
    include("header.php");
    include("menuimpsAcc.php");
} else {
    include("logout.php");
}
include("dbConnection.php");
$id = $_GET['id'];
$row = mysqli_fetch_assoc(mysqli_query($con, "SELECT 
        g.id,
        g.cash,
        g.transferRate,
        g.type,
        g.city,
        b.branchName
    FROM gold g
    JOIN branch b 
        ON b.branchId = g.city
    WHERE g.id = '$id'"));
?>
<style>
    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 16px;
        color: #123C69;
    }

    #wrapper .panel-body {
        border: 5px solid #fff;
        padding: 15px;
        box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px;
        background-color: #f5f5f5;
        border-radius: 3px;
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

    tbody {
        font-weight: 600;
    }

    .trInput:focus-within {
        outline: 3px solid #990000;
    }

    .fa {
        color: #34495e;
        font-size: 16px;
    }

    .btn {
        background-color: transparent;
    }
</style>
<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <h3 class="text-success"><span class="fa fa-pencil-square" style="color:#990000"></span><b> EDIT Gold Rate</b></h3>
                </div>
                <div class="panel-body">
                    <table id="user" class="table table-bordered table-striped" style="clear: both">
                        <tbody>
                            <!-- <tr> -->
                            <!-- <th class="text-success" width="35%" style="padding-top:17px">BRANCH ID</th> -->
                            <!-- <td width="65%"> -->
                            <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
                            <!--  -->

                            <!-- </td> -->
                            <!-- </tr> -->





                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Branch Name</th>
                                <td width="100%">
                                    <div class="input-group trInput">
                                        <input type="text" class="form-control" value="<?php echo $row['branchName']; ?>" autocomplete="off" readonly>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Cash</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="cash" class="form-control" value="<?php echo $row['cash']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateGoldRateData(this)"><i class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">IMPS</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="transferRate" class="form-control" value="<?php echo $row['transferRate']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateGoldRateData(this)"><i class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
    <script>
        let id = document.getElementById('id').value;

        function updateGoldRateData(button) {
            let colValue = button.parentNode.previousElementSibling.value,
                colName = button.parentNode.previousElementSibling.name;
            $.ajax({
                url: "editAjax.php",
                type: "POST",
                data: {
                    editGoldRate: 'editGoldRate',
                    id: id,
                    colName: colName,
                    colValue: colValue
                },
                success: function(e) {
                    if (e == '1') {
                        alert('Successfully Updated');
                    } else {
                        alert('Oops!!! Something went wrong');
                    }
                }
            });
        }
    </script>
    <?php include("footer.php"); ?>
