<?php
session_start();
$type = $_SESSION['usertype'];
if ($type == 'Task') {
    include("header.php");
    include("menuTast.php");
} else {
    include("logout.php");
}
include("dbConnection.php");
$id = $_GET['id'];
$row = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM task WHERE id='$id'"));
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
                    <h3 class="text-success"><span class="fa fa-pencil-square" style="color:#990000"></span><b> EDIT Task DETAILS</b></h3>
                </div>
                <div class="panel-body">
                    <table id="user" class="table table-bordered table-striped" style="clear: both">
                        <tbody>
                            <!-- <tr> -->
                            <!-- <th class="text-success" width="35%" style="padding-top:17px">BRANCH ID</th> -->
                            <!-- <td width="65%"> -->
                            <input type="hidden" name="id" id="id" class="form-control" value="<?php echo $row['id']; ?>" readonly>
                            <!-- </td> -->
                            <!-- </tr> -->

                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">BRANCH ID</th>
                                <td width="65%"><input type="text" name="branchId" id="branchId" class="form-control" value="<?php echo $row['branchId']; ?>" readonly></td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px">Remark</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="remark" class="form-control" value="<?php echo $row['remark']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateTaskData(this)"><i class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px"> Prioriyt</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="priority" class="form-control" value="<?php echo $row['priority']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateTaskData(this)"><i class="fa fa-paint-brush"></i></button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-success" width="35%" style="padding-top:17px"> STATUS</th>
                                <td width="65%">
                                    <div class="input-group trInput">
                                        <input type="text" name="Status" class="form-control" value="<?php echo $row['Status']; ?>" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn" onclick="updateTaskData(this)"><i class="fa fa-paint-brush"></i></button>
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

        function updateTaskData(button) {
            let colValue = button.parentNode.previousElementSibling.value,
                colName = button.parentNode.previousElementSibling.name;
            $.ajax({
                url: "editAjax.php",
                type: "POST",
                data: {
                    editTask: 'editTask',
                    id: id,
                    colName: colName,
                    colValue: colValue
                },
                success: function(e) {
                    if (e == '1') {
                        alert('Successfully Updated');
                    } else {
                        alert('Successfully Updated');
                    }
                }
            });
        }
    </script>
    <?php include("footer.php"); ?>
