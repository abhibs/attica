<?php
session_start();
$type = $_SESSION['usertype'] ?? '';

if ($type == 'Zonal') {
    require("header.php");
    include("menuZonal.php");
} elseif ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} elseif ($type == 'VM-WAIT') {
    include("header.php");
    include("menuvmWait.php");
} elseif ($type == 'Issuecall') {
    include("header.php");
    include("menuissues.php");
} elseif ($type == 'SundayUser') {
    include("header.php");
    include("menuSundayUser.php");
} elseif ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
} else {
    include("logout.php");
    exit;
}

include("dbConnection.php");
$date = date('Y-m-d');

$branchList = '';
$state = '';

if ($type == 'Issuecall') {
    if ($_SESSION['branchCode'] == "TN") {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state IN ('Tamilnadu','Pondicherry') AND status=1");
        $state = "SELECT branchId FROM branch WHERE state IN ('Tamilnadu','Pondicherry') AND status=1";
    } elseif ($_SESSION['branchCode'] == "KA") {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state='Karnataka' AND status=1");
        $state = "SELECT branchId FROM branch WHERE state='Karnataka' AND status=1";
    } elseif ($_SESSION['branchCode'] == "AP-TS") {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state IN ('Andhra Pradesh','Telangana') AND status=1");
        $state = "SELECT branchId FROM branch WHERE state IN ('Andhra Pradesh','Telangana') AND status=1";
    } else {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
        $state = "SELECT branchId FROM branch WHERE status=1";
    }
} else {
    if ($_SESSION['branchCode'] == "TN") {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state IN ('Tamilnadu','Pondicherry') AND status=1");
        $state = "SELECT branchId FROM branch WHERE state IN ('Tamilnadu','Pondicherry') AND status=1";
    } elseif ($_SESSION['branchCode'] == "KA") {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state='Karnataka' AND status=1");
        $state = "SELECT branchId FROM branch WHERE state='Karnataka' AND status=1";
    } elseif ($_SESSION['branchCode'] == "AP-TS") {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE state IN ('Andhra Pradesh','Telangana') AND status=1");
        $state = "SELECT branchId FROM branch WHERE state IN ('Andhra Pradesh','Telangana') AND status=1";
    } else {
        $branchList = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
        $state = "SELECT branchId FROM branch WHERE status=1";
    }
}
?>
<style>
    #wrapper h3 {
        text-transform: uppercase;
        font-weight: 600;
        font-size: 17px;
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
        font-size: 12px;
    }
    .fa_Icon {
        color: #8B2030;
    }
	/* HO side: new message glow on Message button */
	.btn-msg-branch.btn-msg-new {
		background-color: #e60023;
		border-color: #e60023;
		color: #fff;
		box-shadow: 0 0 10px rgba(230, 0, 35, 0.9),
					0 0 18px rgba(230, 0, 35, 0.7);
		animation: chatGlowBtn 1s ease-in-out infinite alternate;
	}
	@keyframes chatGlowBtn {
		0% {
			box-shadow: 0 0 10px rgba(230, 0, 35, 0.6),
						0 0 16px rgba(230, 0, 35, 0.4);
		}
		100% {
			box-shadow: 0 0 16px rgba(230, 0, 35, 1),
						0 0 26px rgba(230, 0, 35, 0.9);
		}
	}
    thead {
        text-transform: uppercase;
        background-color: #123C69;
    }
    thead tr {
        color: #f2f2f2;
        font-size: 10px;
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
    .hpanel .panel-body {
        box-shadow: 10px 15px 15px #999;
        border-radius: 3px;
        padding: 20px;
        background-color: #f5f5f5;
    }
    .table_td_waiting {
        color: #990000;
    }
    .table_td_external_link {
        color: #123C69;
        font-size: 17px;
    }
    .table_td_reg {
        color: #840bde;
    }
    /* Gold image thumb */
    .gold-img-thumb {
        max-width: 50px;
        max-height: 50px;
        border-radius: 3px;
        cursor: pointer;
    }
    /* Chat modal styles */
    .chat-modal .modal-dialog {
        width: 360px;
        max-width: 95%;
        margin: 60px auto;
    }
    .chat-messages {
        max-height: 320px;
        overflow-y: auto;
        padding: 8px;
        background: #f5f5f5;
        border-radius: 4px;
    }
    .chat-bubble {
        padding: 6px 10px;
        margin: 3px 0;
        border-radius: 12px;
        max-width: 80%;
        clear: both;
        font-size: 12px;
    }
    .chat-bubble-me {
        background: #123C69;
        color: #fff;
        margin-left: auto;
        text-align: right;
    }
    .chat-bubble-them {
        background: #e4e6eb;
        margin-right: auto;
        text-align: left;
    }
    .chat-time {
        display: block;
        font-size: 10px;
        opacity: 0.7;
        margin-top: 2px;
    }
</style>

<!-- DATA LIST - BRANCH LIST -->
<datalist id="branchList">
    <?php while ($branchL = mysqli_fetch_array($branchList)) { ?>
        <option value="<?php echo $branchL['branchId']; ?>" label="<?php echo $branchL['branchName']; ?>"></option>
    <?php } ?>
</datalist>

<div id="wrapper">
    <div class="content">

        <div class="row">
            <div class="col-lg-9">
                <h3 class="text-success no-margins">
                    <a type='button' class='btn btn-success' href="displayQuotation.php">
                        <span style="color:#ffcf40" class="fa fa-share-square-o"></span> QUOTATION
                    </a>
                </h3>
            </div>
            <div class="col-lg-3">
                <form action="" method="POST">
                    <div class="input-group">
                        <input list="branchList" class="form-control" name="branchId" placeholder="Select Branch" required style="border-right:3px solid grey">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-success" style="border:none;">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <?php
        if (isset($_POST['branchId']) && $_POST['branchId'] != '') {
            $branchId = $_POST['branchId'];
            $branchData = mysqli_fetch_assoc(mysqli_query($con, "SELECT branchName FROM branch WHERE branchId='$branchId'"));
            $bmData = mysqli_fetch_assoc(mysqli_query($con, "SELECT name,contact FROM employee WHERE empId=(SELECT employeeId FROM users WHERE branch='$branchId')"));
            ?>
            <div class="row" style="margin-top:10px">
                <div class="col-lg-3">
                    <div class="hpanel">
                        <div class="panel-body">
                            <div class="panel-heading" align=center>
                                <h3 class="text-success"><?php echo $branchData['branchName']; ?></h3>
                            </div>
                            <hr style="margin:0px">
                            <div class="col-sm-12" style="margin-top:15px">
                                <label class="text-success">BRANCH MANAGER</label>
                                <input type="text" readonly class="form-control" value="<?php echo $bmData['name']; ?>">
                            </div>
                            <div class="col-sm-12" style="margin-top:15px;margin-bottom:15px">
                                <label class="text-success">CONTACT</label>
                                <input type="text" readonly class="form-control" value="<?php echo $bmData['contact']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="hpanel">
                        <div class="panel-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Bills</th>
                                        <th>Time</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Quot</th>
                                        <!-- Optional: message button here too if you want -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $query = mysqli_query($con, "SELECT customer,
                                                                      CONCAT('XXXXXX', RIGHT(contact, 4)) AS contact,
                                                                      quotation,
                                                                      status,
                                                                      extra,
                                                                      time
                                                                 FROM everycustomer
                                                                 WHERE date='$date' AND branch='$branchId'
                                                                 ORDER BY Id ASC");
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        echo "<tr>";

                                        echo "<td>" . $i . "</td>";
                                        echo "<td>" . $row['customer'] . "</td>";
                                        echo "<td>" . $row['contact'] . "</td>";

                                        // BILLS
                                        $extra = json_decode($row['extra'], true);
                                        $bills = isset($extra['bills']) ? $extra['bills'] : 0;
                                        echo "<td><a target='_blank' href='existing.php?phone=" . $row['contact'] . "'>" . $bills . "</a></td>";

                                        echo "<td>" . $row['time'] . "</td>";

                                        // STATUS
                                        if ($row['status'] == '0') {
                                            echo "<td class='table_td_waiting text-center'>Waiting</td>";
                                        } elseif ($row['status'] == 'Begin') {
                                            echo "<td class='table_td_reg text-center'>Registered</td>";
                                        } else {
                                            echo "<td class='text-center'>" . $row['status'] . "</td>";
                                        }

                                        // QUOTATION BUTTON
                                        if ($row['quotation'] == '') {
                                            echo "<td></td>";
                                        } else {
                                            $q = json_decode($row['quotation'], true);
                                            $imgName = isset($q['image']) ? $q['image'] : '';
                                            echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $imgName . "'><span class='fa fa-external-link'></span></a></td>";
                                        }

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

        <?php } else { ?>
            <div class="row" style="margin-top:5px">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="tab" role="tabpanel">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="active">
                                    <a data-toggle="tab" href="#tab-1">
                                        <i class="fa_Icon fa fa-refresh"></i> Waiting
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="tab-1" class="tab-pane active">
                                    <div class="panel-body">
                                        <table id="example5" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Branch</th>
                                                    <th>Zonal Name</th>
                                                    <th>VM Name</th>
                                                    <th>Customer</th>
                                                    <th>Contact</th>
                                                    <th>GrossW</th>
                                                    <th>Type</th>
                                                    <th>WalkinType</th>
                                                    <th>Bills</th>
                                                    <th>Time</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Quot</th>
                                                    <th class="text-center">Message</th>
                                                    <th class="text-center">Gold Image</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $i = 1;
                                                $sql = "SELECT 
                                                            w.customer,
                                                            CONCAT('XXXXXX', RIGHT(w.contact, 4)) AS contact,
                                                            w.quotation,
                                                            w.status,
                                                            w.time,
                                                            w.extra,
                                                            w.type,
                                                            w.walkinType,
                                                            w.ornament_docs,
                                                            w.branch AS branchId,
                                                            b.branchName,
                                                            e.name AS zonal_name,
                                                            vm_emp.name AS vmname
                                                        FROM everycustomer w
                                                        LEFT JOIN branch b ON w.branch = b.branchId
                                                        LEFT JOIN employee e ON b.ezviz_vc = e.empId
                                                        LEFT JOIN vmagent vma ON FIND_IN_SET(w.branch, REPLACE(vma.branch, ' ', '')) > 0
                                                        LEFT JOIN employee vm_emp ON vma.agentId = vm_emp.empId
                                                        WHERE w.date='$date'
                                                          AND w.status IN ('0','Blocked','Begin')
                                                          AND w.branch IN (" . $state . ")
                                                        ORDER BY w.Id DESC";
                                                $query = mysqli_query($con, $sql);
                                                while ($value = mysqli_fetch_assoc($query)) {

                                                    $extra = json_decode($value['extra'], true);
                                                    $bills = isset($extra['bills']) ? $extra['bills'] : 0;

                                                    if ($value['status'] == 'Blocked') {
                                                        echo "<tr style='background-color: #F2DEDE;'>";
                                                    } else {
                                                        if ($bills > 0) {
                                                            echo "<tr style='background-color: #CCEAFD;'>";
                                                        } else {
                                                            echo "<tr>";
                                                        }
                                                    }

                                                    echo "<td>" . $i . "</td>";
                                                    echo "<td>" . $value['branchName'] . "</td>";
                                                    echo "<td>" . $value['zonal_name'] . "</td>";
                                                    echo "<td>" . $value['vmname'] . "</td>";
                                                    echo "<td>" . $value['customer'] . "</td>";
                                                    echo "<td>" . $value['contact'] . "</td>";
                                                    echo "<td>" . (isset($extra['GrossW']) ? $extra['GrossW'] : '') . "</td>";
                                                    echo "<td>" . $value['type'] . "</td>";
                                                    echo "<td>" . ($value['walkinType'] ?? '') . "</td>";
                                                    echo "<td><a target='_blank' href='existing.php?phone=" . $value['contact'] . "'>" . $bills . "</a></td>";
                                                    echo "<td>" . $value['time'] . "</td>";

                                                    // STATUS
                                                    if ($value['status'] == 'Blocked') {
                                                        echo "<td class='text-center'>Blocked</td>";
                                                    } elseif ($value['status'] == '0') {
                                                        echo "<td class='table_td_waiting text-center'>Waiting</td>";
                                                    } elseif ($value['status'] == 'Begin') {
                                                        echo "<td class='table_td_reg text-center'>Registered</td>";
                                                    }

                                                    // QUOTATION BUTTON
                                                    if ($value['quotation'] == '') {
                                                        echo "<td></td>";
                                                    } else {
                                                        $q = json_decode($value['quotation'], true);
                                                        $imgName = isset($q['image']) ? $q['image'] : '';
                                                        echo "<td class='text-center'><a class='table_td_external_link' target='_BLANK' href='QuotationImage/" . $imgName . "'><span class='fa fa-external-link'></span></a></td>";
                                                    }

                                                    // MESSAGE BUTTON (NEW)
                                                    $branchIdRow = htmlspecialchars($value['branchId'], ENT_QUOTES, 'UTF-8');
                                                    $branchNameRow = htmlspecialchars($value['branchName'], ENT_QUOTES, 'UTF-8');
                                                    echo "<td class='text-center'>
                                                            <button type='button'
                                                                    class='btn btn-xs btn-info btn-msg-branch'
                                                                    data-branch-id='" . $branchIdRow . "'
                                                                    data-branch-name=\"" . $branchNameRow . "\">
                                                                <i class='fa fa-comments'></i>
                                                            </button>
                                                          </td>";

                                                    // GOLD IMAGE (ornament_docs)
                                                    if (!empty($value['ornament_docs'])) {
                                                        $ornFile = htmlspecialchars($value['ornament_docs'], ENT_QUOTES, 'UTF-8');
                                                        echo "<td class='text-center'>
                                                                <a href='OrnamentDocs/{$ornFile}' target='_blank'>
                                                                    <img src='OrnamentDocs/{$ornFile}' alt='Gold Image' class='gold-img-thumb'>
                                                                </a>
                                                              </td>";
                                                    } else {
                                                        echo "<td class='text-center'>-</td>";
                                                    }

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
                </div>
            </div>
        <?php } ?>

    </div>

<?php include("footer.php"); ?>
