<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
$type = $_SESSION['usertype'];
if ($type == 'Task') {
    include("header.php");
    include("menuTast.php");
}
else if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
}else if ($type == 'BD') {
    include("header.php");
    include("menubd.php");
}
else {
    include("logout.php");
}
include("dbConnection.php");
$branchStatus = mysqli_fetch_assoc(mysqli_query($con, "SELECT 
	(SELECT COUNT(*) FROM branch WHERE Status=1 AND branchId != 'AGPL000') AS active,
	(SELECT COUNT(*) FROM branch WHERE Status=0) AS closed,
	(SELECT branchId FROM branch ORDER BY id DESC LIMIT 1) AS lastID"));
$lastID = (int) filter_var($branchStatus['lastID'], FILTER_SANITIZE_NUMBER_INT);
$lastID = $lastID + 1;

?>
<style>
    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 16px;
        color: #123C69;
    }

    .form-control[disabled],
    .form-control[readonly],
    fieldset[disabled] .form-control {
        background-color: #fffafa;
    }

    .text-success {
        color: #123C69;
        text-transform: uppercase;
        font-weight: 600;
    }

    .btn-primary {
        background-color: #123C69;
    }

    .theadRow {
        text-transform: uppercase;
        background-color: #123C69 !important;
        color: #f2f2f2;
        font-size: 11px;
    }

    .btn-success {
        display: inline-block;
        padding: 0.7em 1.4em;
        margin: 0 0.3em 0.3em 0;
        border-radius: 0.15em;
        box-sizing: border-box;
        text-decoration: none;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
        text-transform: uppercase;
        color: #fffafa;
        background-color: #123C69;
        box-shadow: inset 0 -0.6em 0 -0.35em rgba(0, 0, 0, 0.17);
        text-align: center;
        position: relative;
    }

    .fa_Icon {
        color: #990000;
    }

    #wrapper .panel-body {
        box-shadow: 10px 15px 15px #999;
        background-color: #f5f5f5;
        border-radius: 3px;
        padding: 15px;
    }

    .table-responsive .row {
        margin: 0px;
    }
</style>

<datalist id="branchList">
    <?php
    $branches = mysqli_query($con, "SELECT branchId,branchName FROM branch where status=1");
    while ($branchList = mysqli_fetch_array($branches)) {
    ?>
        <option value="<?php echo $branchList['branchId']; ?>" label="<?php echo $branchList['branchName']; ?>"></option>
    <?php } ?>
</datalist>
<!--   ADD BRANCH MODAL   -->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width:1050px;">
        <div class="modal-content">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
                <h3>ADD Task</h3>
            </div>
            <div class="modal-body" style="padding-right: 40px; background-color: #f5f5f5;">
                <form method="POST" class="form-horizontal" action="add.php">
                    <div class="row content">
                        <div class="col-sm-12">
                            <label class="text-success">BRANCH ID</label>
                            <input list="branchList" name="branchId" placeholder="SELECT BRANCH" required class="form-control">
                        </div>
                        <div class="col-sm-12">
                            <label class="text-success">Remark</label>
                            <textarea type="text" name="remark" class="form-control" autocomplete="off" required></textarea>
                        </div>

                        <div class="col-sm-12">
                            <label class="text-success">Priority</label>
                            <select class="form-control" name="priority" id="branchState" style="padding:0px 2px" required>
                                <option selected="true" disabled="disabled" value="">SELECT Priority</option>
                                <option value="Top">Top</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>

                        <div class="col-sm-9" align="right" style="padding-top:22px;">
                            <button class="btn btn-success" name="submitTask" id="submitTask" type="submit">
                                <span style="color:#ffcf40" class="fa fa-plus"></span> ADD Task
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
</div>


<div id="wrapper">
    <div class="row content">

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h3 class="font-light m-b-xs text-success">
                            <b><i class="fa_Icon fa fa-institution"></i> Branch Issue DETAILS</b>
                        </h3>
		    </div>
		    <?php if ($type !== 'Master'): ?>
                    <div class="card-body container-fluid" style="margin-top:24px;padding:0px;align:right">
                        <div class="col-lg-2">
                            <a data-toggle="modal" data-target="#addTaskModal">
                                <div class="panel-body text-center" style="margin-bottom:0px">
                                    <h3 class="m-xs" style="color: #990000;">
                                        <i class='fa fa-plus'></i>
                                    </h3>
                                    <h5 class="font-extra-bold no-margins text-success">
                                        ADD Task
                                    </h5>
                                </div>
                            </a>
                        </div>
		    </div>
		    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-hover table-bordered">
                            <thead>
                                <tr class="theadRow">
                                    <th>#</th>
				    <th>BRANCH Id</th>
				    <th>BRANCH Name</th>
                                    <th>State</th>
				    <th>Remark</th>
				    <th>Priority</th>
                                    <th>Date</th>
				    <th style="text-align:center;">STATUS</th>
				     <?php if ($type !== 'Master'): ?>
                                    	<th style="text-align:center;">EDIT</th>
				     <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $sql = mysqli_query($con, 'SELECT t.*, b.branchName, b.state FROM task t JOIN branch b ON t.branchId = b.branchId ORDER BY t.branchId;');
                                while ($row = mysqli_fetch_assoc($sql)) {
                                    echo "<tr>";
                                    echo "<td>" . $i . "</td>";
				    echo "<td>" . $row['branchId'] . "</td>";
				    echo "<td>" . $row['branchName'] . "</td>";
                                    echo "<td>" . $row['state'] . "</td>";
				    echo "<td>" . $row['remark'] . "</td>";
				    echo "<td>" . $row['priority'] . "</td>";
                                    echo "<td>" . $row['date'] . "</td>";

                                    if ($row['Status'] == 1) {
                                        echo "<td style='text-align:center'><a class='btn' type='button' title='Active'>Completed</td>";
                                    } else {
                                        echo "<td style='text-align:center'><a class='btn' type='button' title='Closed'>Pending</td>";
				    }
				    if ($type !== 'Master'):
					    echo "<td style='text-align:center'><a href='editTask.php?id=" . $row['id'] . "' class='btn' type='button'><i class='fa fa-pencil-square-o text-success'style='font-size:16px'></i></a></td>";
				    endif;
                                    echo "</tr>";
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php include("footer.php"); ?>


