<?php

error_reporting(E_ERROR | E_PARSE);
session_start();
$type = $_SESSION['usertype'];
if ($type == 'ZonalMaster') {
    include("header.php");
    include("menuzonalMaster.php");
}
 else if ($type == 'Reportor') {
     include("header.php");
     include("menureportor.php");
 }
else {
    include("logout.php");
}
include("dbConnection.php");
date_default_timezone_set("Asia/Kolkata");

//Date
if (isset($_GET['date'])) {
    $date = $_GET['date'];
} else {
    $date = date('Y-m-d');
}

/* *************    TRANS DATA   ************* */
$newCustomer = 0;
$marginAmount = 0;
$margin = 0;
$cashCount = 0;
$impsCount = 0;
$cashAmount = 0;
$impsAmount = 0;
$busBranches = [];

/*   BANGALORE   */
$bang_bills = 0;
$bang_grossW = 0;
$bang_grossA = 0;
$bang_netA = 0;
/*   KARNATAKA   */
$kar_bills = 0;
$kar_grossW = 0;
$kar_grossA = 0;
$kar_netA = 0;
/*   CHENNAI   */
$chn_bills = 0;
$chn_grossW = 0;
$chn_grossA = 0;
$chn_netA = 0;
/*   TANILNADU & PONDICHERRY   */
$tn_bills = 0;
$tn_grossW = 0;
$tn_grossA = 0;
$tn_netA = 0;
/*   HYDERABAD   */
$hyd_bills = 0;
$hyd_grossW = 0;
$hyd_grossA = 0;
$hyd_netA = 0;
/*   ANDHRA PRADESH & TELANGANA   */
$apt_bills = 0;
$apt_grossW = 0;
$apt_grossA = 0;
$apt_netA = 0;

/*   SILVER   */
$sil_bills = 0;
$sil_grossW = 0;
$sil_netA = 0;

$transQuery = mysqli_query($con, "SELECT *
	FROM
	(SELECT billCount,ROUND(grossW,2) AS grossW,ROUND(netA,2) AS netA,ROUND(grossA,2) AS grossA,branchId,type,margin,metal,paymentType,cashA,impsA FROM trans WHERE status='Approved' AND date='$date') A
	INNER JOIN 
	(SELECT branchId,state,city FROM branch WHERE status=1) B
	ON A.branchId=B.branchId");
while ($row = mysqli_fetch_assoc($transQuery)) {

    /* NEW CUSTOMERS */
    if ($row['billCount'] == 0) {
        $newCustomer++;
    }

    /* CASH/IMPS COUNT */
    if ($row['paymentType'] == 'Cash') {
        $cashCount++;
    } else if ($row['paymentType'] == 'NEFT/RTGS') {
        $impsCount++;
    }
    $cashAmount += $row['cashA'];
    $impsAmount += $row['impsA'];

    /* CITY/STATE WISE DATA */
    if ($row['metal'] == 'Gold') { /* GOLD DATA */

        /* MARGIN AMOUNT */
        $marginAmount += $row['margin'];

        if ($row['city'] == 'Bengaluru') {
            $bang_bills++;
            $bang_grossW += $row['grossW'];
            $bang_grossA += $row['grossA'];
            $bang_netA += $row['netA'];
        } else if ($row['city'] == 'Chennai') {
            $chn_bills++;
            $chn_grossW += $row['grossW'];
            $chn_grossA += $row['grossA'];
            $chn_netA += $row['netA'];
        } else if ($row['city'] == 'Hyderabad') {
            $hyd_bills++;
            $hyd_grossW += $row['grossW'];
            $hyd_grossA += $row['grossA'];
            $hyd_netA += $row['netA'];
        } else if ($row['city'] != 'Bengaluru' && $row['state'] == 'Karnataka') {
            $kar_bills++;
            $kar_grossW += $row['grossW'];
            $kar_grossA += $row['grossA'];
            $kar_netA += $row['netA'];
        } else if ($row['city'] != 'Chennai' && ($row['state'] == 'Tamilnadu' || $row['state'] == 'Pondicherry')) {
            $tn_bills++;
            $tn_grossW += $row['grossW'];
            $tn_grossA += $row['grossA'];
            $tn_netA += $row['netA'];
        } else if ($row['city'] != 'Hyderabad' && ($row['state'] == 'Telangana' || $row['state'] == 'Andhra Pradesh')) {
            $apt_bills++;
            $apt_grossW += $row['grossW'];
            $apt_grossA += $row['grossA'];
            $apt_netA += $row['netA'];
        }
    } else if ($row['metal'] == 'Silver') { /* SILVER DATA */
        $sil_bills++;
        $sil_grossW += $row['grossW'];
        $sil_netA += $row['netA'];
    }

    /* BUSINESS BRANCHES */
    $busBranches[] = $row['branchId'];
}

/* *************    BRANCH LIST   ************* */
$branches = [];
$activeBranches = 0;
$branchQuery = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
while ($row = mysqli_fetch_assoc($branchQuery)) {
    $activeBranches++;
    $branches[] = $row;
}

/* *************    OTHER DATA   ************* */
$otherData = mysqli_fetch_assoc(mysqli_query($con, "SELECT
	(SELECT COUNT(*) FROM walkin WHERE date='$date' AND havingG='with' AND branchId !='' AND status!='Rejected') AS withGold,
	(SELECT COUNT(*) FROM walkin WHERE date='$date' AND havingG='without' AND branchId !='' AND status!='Rejected') AS withoutGold,
	(SELECT SUM(amount) FROM expense WHERE date='$date' AND status='Approved') AS Expense,
	(SELECT SUM(relCash) FROM releasedata WHERE date='$date' AND status IN ('Approved','Billed')) AS relCash,
	(SELECT SUM(relIMPS) FROM releasedata WHERE date='$date' AND status IN ('Approved','Billed')) AS relIMPS,
	(SELECT cash FROM gold WHERE type='Gold' AND city='AGPL000' AND date='$date' ORDER BY id DESC LIMIT 1) AS goldCashRate,
	(SELECT transferRate FROM gold WHERE type='Gold' AND city='AGPL000' AND date='$date' ORDER BY id DESC LIMIT 1) AS goldIMPSRate,
	(SELECT cash FROM gold WHERE type='Silver' AND city='AGPL000' AND date='$date' ORDER BY id DESC LIMIT 1) AS silverRate
	"));






$sql = "
WITH ranked AS (
    SELECT 
        t.branchId, 
        DAYNAME(t.date) AS day_name, 
        CAST(t.grossW AS DECIMAL(10,2)) AS grossW_numeric,
        ROW_NUMBER() OVER (
            PARTITION BY t.branchId, DAYOFWEEK(t.date)
            ORDER BY CAST(t.grossW AS DECIMAL(10,2)) DESC
        ) AS rn
    FROM trans AS t
    WHERE t.date >= CURDATE() - INTERVAL 5 YEAR
),
top12 AS (
    SELECT branchId, day_name, grossW_numeric
    FROM ranked
    WHERE rn <= 12
),
branch_avgs AS (
    SELECT 
        b.branchId, 
        b.branchName, 
        SUM(t.grossW_numeric) AS sum_top12,
        AVG(t.grossW_numeric) AS avg_top12
    FROM top12 AS t
    JOIN branch AS b ON b.branchId = t.branchId
    WHERE t.day_name = DAYNAME(CURDATE()) AND b.Status = 1
    GROUP BY b.branchId, b.branchName
)
SELECT AVG(avg_top12) AS overall_avg_of_avgs FROM branch_avgs;
";

$result = mysqli_query($con, $sql);

// Initialize variable for output
$overall_avg = 0;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $overall_avg = $row['overall_avg_of_avgs'];
} else {
    echo "No data found or query error: " . mysqli_error($con);
}

// Close the DB connection
mysqli_close($con);




?>
<datalist id="branchList">
    <?php foreach ($branches as $key => $val) { ?>
        <option value="<?php echo $val['branchId']; ?>"><?php echo $val['branchName']; ?></option>
    <?php } ?>
</datalist>
<style>
    #wrapper {
        background: #f5f5f5;
    }

    .hpanel {
        margin-bottom: 5px;
        border-radius: 10px;
        box-shadow: 5px 5px 5px #999;
    }

    .text-success {
        color: #123C69;
        text-transform: uppercase;
        font-size: 20px;
    }

    .stats-label {
        text-transform: uppercase;
        font-size: 10px;
    }

    .list-item-container h3 {
        font-size: 14px;
    }

    .panel-footer {
        border-radius: 0px 0px 10px 10px;
        text-align: center;
    }

    .panel-footer>b {
        color: #990000;
    }

    #wrapper .panel-body {
        border-radius: 10px 10px 0px 0px;
    }

    .fa {
        color: #990000;
    }
</style>

<meta http-equiv="refresh" content="300">
<div id="wrapper">
    <div class="content"> 
        <div class="row">

            <!--  DATE SELECTOR  -->
	
	    <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold text-center text-success">Date : <?php echo $date; ?></h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-calendar fa-2x"></i>
                            </div>
                        </div>
                        <div class="m-t-xl">
                            <div class="row">
				<form action="" method="GET">
                                    	<div class="col-xs-9">
                                        	<input type="date" class="form-control" name="date" placeholder="Branch" required>
                                   	 </div>
                                   	 <div class="col-xs-3">
                                        	<button class="btn btn-success"><i style="color:#FFFFFF" class="fa fa-search"></i></button>
				   	 </div>
                                </form>
                            </div>+
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i><a href="#"> View Details</a>
                    </div>
                </div>
            </div>

	    					
            <!--  TODAYS RATE  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">Today's Rate</h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-rupee fa-2x"></i>
                            </div>
                        </div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-8">
                                    <small class="stats-label">Gold Rate:</small>
                                    <h4><i class="fa fa-rupee"></i> <?php echo $otherData['goldCashRate'] . " / " . $otherData['goldIMPSRate']; ?></h4>
                                </div>
                                <div class="col-xs-4">
                                    <small class="stats-label">Silver Rate:</small>
                                    <h4><i class="fa fa-rupee"></i> <?php echo $otherData['silverRate']; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <i class="fa fa-angle-double-right"></i> <a href="viewGoldRate.php">View Details</a>
                    </div>
                </div>
            </div>

            <!--  TOTAL CUSTOMERS  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">Customers</h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                        </div>
                        <span class="font-bold no-margins"></span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">New Customers</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $newCustomer; ?></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stats-label">Total Bills</small>
                                    <h4><i class="fa fa-edit"></i> <?php echo ($bang_bills + $chn_bills + $hyd_bills + $kar_bills + $tn_bills + $apt_bills + $sil_bills); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <!-- <i class="fa fa-angle-double-right"></i>  -->
                        <!-- <a href="viewCustomers.php">View Details</a> -->
                    </div>
                </div>
            </div>

            <!--  ENQUIRY  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">Enquiry</h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-comments fa-2x"></i>
                            </div>
                        </div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> With Gold</small>
                                    <h4><?php echo $otherData['withGold']; ?></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Without Gold</small>
                                    <h4><?php echo $otherData['withoutGold']; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <!-- <i class="fa fa-angle-double-right"></i>  -->
                        <!-- <a href="walkinout.php">View Details</a> -->
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <!--  BRANCH REPORT  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">Report</h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-university fa-2x"></i>
                            </div>
                        </div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Business Branches</small>
                                    <h4><i class="fa fa-institution"></i> <?php echo count(array_unique($busBranches)); ?></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stats-label">Golden Ducks</small>
                                    <h4><i class="fa fa-institution"></i> <?php echo $activeBranches - count(array_unique($busBranches)); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <!-- <i class="fa fa-angle-double-right"></i>  -->
                        <!-- <a href="aggregate.php">View Details</a> -->
                    </div>
                </div>
            </div>

            <!--  KARNATAKA BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">KA Bills<?php echo " (" . ($bang_bills + $kar_bills) . ")";   ?></h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                        </div>
                        <span class="font-bold no-margins"></span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Bangalore</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $bang_bills; ?></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Karnataka</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $kar_bills; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <!-- <i class="fa fa-angle-double-right"></i>  -->
                        <!-- <a href="viewbill.php">View Details</a> -->
                    </div>
                </div>
            </div>

            <!--  TAMILNADU BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">TN Bills<?php echo " (" . ($chn_bills + $tn_bills) . ")";   ?></h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                        </div>
                        <span class="font-bold no-margins"></span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Chennai</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $chn_bills; ?></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> TamilNadu</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $tn_bills; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
                        <!-- <i class="fa fa-angle-double-right"></i>  -->
                        <!-- <a href="viewbill.php">View Details</a> -->
                    </div>
                </div>
            </div>

            <!--  AP/T BILLS  -->
            <div class="col-lg-3">
                <div class="hpanel stats">
                    <div class="panel-body">
                        <div class="row">
                            <div class="stats-title pull-left">
                                <h3 class="font-extra-bold no-margins text-success">AP/T Bills<?php echo " (" . ($hyd_bills + $apt_bills) . ")";   ?></h3>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                        </div>
                        <span class="font-bold no-margins"></span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Hyderabad</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $hyd_bills; ?></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> AP/T</small>
                                    <h4><i class="fa fa-user"></i> <?php echo $apt_bills; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; <!-- <i class="fa fa-angle-double-right"></i>  -->
                        <!-- <a href="viewbill.php">View Details</a> -->
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
 <!-- Include once on the page -->
  <style>
    .mask-toggle {
      cursor: pointer;
      user-select: none;
      border-bottom: 1px dashed rgba(0,0,0,0.15);
      padding-bottom: 2px;
      white-space: nowrap;
    }
    .mask-toggle .fa {
      font-size: 0.85em;
      margin-left: 6px;
      opacity: 0.6;
    }
  </style>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const AUTOHIDE_MS = 5000; // 5 seconds

    document.querySelectorAll('.mask-toggle').forEach(function (el) {
      // helper: show/hide with icon
      function render(masked) {
        el.textContent = masked ? '***' : (el.dataset.text || '');
        if (el.dataset.icon !== 'no') {
          const i = document.createElement('i');
          i.className = masked ? 'fa fa-eye-slash' : 'fa fa-eye';
          el.appendChild(i);
        }
        el.dataset.state = masked ? 'masked' : 'shown';
      }

      // init masked
      render(true);

      el.addEventListener('click', function () {
        const isShown = el.dataset.state === 'shown';

        // clear any existing autohide timer
        if (el._maskTimer) {
          clearTimeout(el._maskTimer);
          el._maskTimer = null;
        }

        if (isShown) {
          // hide immediately if it's already shown
          render(true);
        } else {
          // show now
          render(false);
          // schedule auto-hide
          el._maskTimer = setTimeout(function () {
            // only hide if still shown
            if (el.dataset.state === 'shown') render(true);
            el._maskTimer = null;
          }, AUTOHIDE_MS);
        }
      });
    });
  });
</script>

            <!--  TOTAL GOLD  -->
<div class="col-lg-3">
    <div class="hpanel stats">
        <div class="panel-body">
            <div class="row">
                <div class="stats-title pull-left">
                    <h3 class="font-extra-bold no-margins text-success">Total Gold</h3>
                </div>
                <div class="stats-icon pull-right">
                    <i class="fa fa-balance-scale fa-2x"></i>
                </div>
            </div>
            <div class="m-t-xl">
                <div class="row">
                    <div class="col-xs-6">
                        <small class="stats-label">Gross Weight</small>
                        <h4>
                            <?php 
                                $total_grossW = number_format(($bang_grossW + $chn_grossW + $hyd_grossW + $kar_grossW + $tn_grossW + $apt_grossW), 2);
                                $total_grossW_esc = htmlspecialchars($total_grossW, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $total_grossW_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                    <div class="col-xs-6"></div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <b>Attica Gold Pvt Ltd</b>
        </div>
    </div>
</div>

<!--  KARNATAKA GOLD  -->
<div class="col-lg-3">
    <div class="hpanel stats">
        <div class="panel-body">
            <div class="row">
                <div class="stats-title pull-left">
                    <h3 class="font-extra-bold no-margins text-success"> KA Gold</h3>
                </div>
                <div class="stats-icon pull-right">
                    <i class="fa fa-balance-scale fa-2x"></i>
                </div>
            </div>
            <div class="m-t-xl">
                <div class="row">
                    <div class="col-xs-6">
                        <small class="stats-label"> Bangalore</small>
                        <h4>
                            <?php
                                $margin = ($bang_grossA == 0) ? 0 : ROUND((($bang_grossA - $bang_netA) / $bang_grossA) * 100, 1);
                                $txt = $bang_grossW . " (" . $margin . ")";
                                $txt_esc = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $txt_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                    <div class="col-xs-6">
                        <small class="stats-label"> Karnataka</small>
                        <h4>
                            <?php
                                $margin = ($kar_grossA == 0) ? 0 : ROUND((($kar_grossA - $kar_netA) / $kar_grossA) * 100, 1);
                                $txt = $kar_grossW . " (" . $margin . ")";
                                $txt_esc = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $txt_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <b>Attica Gold Pvt Ltd</b>
        </div>
    </div>
</div>

<!--  TN GOLD  -->
<div class="col-lg-3">
    <div class="hpanel stats">
        <div class="panel-body">
            <div class="row">
                <div class="stats-title pull-left">
                    <h3 class="font-extra-bold no-margins text-success"> TN Gold</h3>
                </div>
                <div class="stats-icon pull-right">
                    <i class="fa fa-balance-scale fa-2x"></i>
                </div>
            </div>
            <div class="m-t-xl">
                <div class="row">
                    <div class="col-xs-6">
                        <small class="stats-label"> Chennai</small>
                        <h4>
                            <?php
                                $margin = ($chn_grossA == 0) ? 0 : ROUND((($chn_grossA - $chn_netA) / $chn_grossA) * 100, 1);
                                $txt = $chn_grossW . " (" . $margin . ")";
                                $txt_esc = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $txt_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                    <div class="col-xs-6">
                        <small class="stats-label"> Tamilnadu</small>
                        <h4>
                            <?php
                                $margin = ($tn_grossA == 0) ? 0 : ROUND((($tn_grossA - $tn_netA) / $tn_grossA) * 100, 1);
                                $txt = $tn_grossW . " (" . $margin . ")";
                                $txt_esc = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $txt_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <b>Attica Gold Pvt Ltd</b>
        </div>
    </div>
</div>

<!--  AP/T GOLD  -->
<div class="col-lg-3">
    <div class="hpanel stats">
        <div class="panel-body">
            <div class="row">
                <div class="stats-title pull-left">
                    <h3 class="font-extra-bold no-margins text-success"> AP/T Gold</h3>
                </div>
                <div class="stats-icon pull-right">
                    <i class="fa fa-balance-scale fa-2x"></i>
                </div>
            </div>
            <div class="m-t-xl">
                <div class="row">
                    <div class="col-xs-6">
                        <small class="stats-label"> Hyderabad</small>
                        <h4>
                            <?php
                                $margin = ($hyd_grossA == 0) ? 0 : ROUND((($hyd_grossA - $hyd_netA) / $hyd_grossA) * 100, 1);
                                $txt = $hyd_grossW . " (" . $margin . ")";
                                $txt_esc = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $txt_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                    <div class="col-xs-6">
                        <small class="stats-label"> AP/TS</small>
                        <h4>
                            <?php
                                $margin = ($apt_grossA == 0) ? 0 : ROUND((($apt_grossA - $apt_netA) / $apt_grossA) * 100, 1);
                                $txt = $apt_grossW . " (" . $margin . ")";
                                $txt_esc = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
                            ?>
                            <span class="mask-toggle" data-text="<?php echo $txt_esc; ?>">
                                ***<i class="fa fa-eye-slash"></i>
                            </span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <b>Attica Gold Pvt Ltd</b>
        </div>
    </div>
</div>

</div>

<div class="row">

    <!--  TOTAL SILVER  -->
    <div class="col-lg-3">
        <div class="hpanel stats">
            <div class="panel-body">
                <div class="row">
                    <div class="stats-title pull-left">
                        <h3 class="font-extra-bold no-margins text-success">Total Silver</h3>
                    </div>
                    <div class="stats-icon pull-right">
                        <i class="fa fa-balance-scale  fa-2x"></i>
                    </div>
                </div>
                <div class="m-t-xl">
                    <div class="row">
                        <div class="col-xs-6">
                            <small class="stats-label">Gross Weight</small>
                            <h5>
                                <?php 
                                    $sil_grossW_esc = htmlspecialchars($sil_grossW, ENT_QUOTES, 'UTF-8');
                                ?>
                                <span class="mask-toggle" data-text="<?php echo $sil_grossW_esc; ?>">
                                    ***<i class="fa fa-eye-slash"></i>
                                </span>
                            </h5>
                        </div>
                        <div class="col-xs-6"></div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <b>Attica Gold Pvt Ltd</b>
            </div>
        </div>
    </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#branchIDsearch").click(function() {
                    var c = $('#brname').val();
                    if (c != '') {
                        var branchName = $('#branchList option[value=' + c + ']').text();
                        $('#branchName_Id').html("<b>" + branchName + "</b>");
                        $('#brname').val('');
                        var $req = $.ajax({
                            url: "dashboardAjax.php?branchId=" + c,
                            type: "GET",
                            dataType: 'JSON',
                            success: function(response) {
                                var len = response.length;
                                for (var i = 0; i < len; i++) {
                                    var gross = response[i].gross;
                                    var net = response[i].net;
                                    var gold = response[i].gold;
                                    var balance = response[i].balance;
                                    $("#grossWeight").html(gross);
                                    $("#netAMount").html(net);
                                    $("#goldRemain").html(gold);
                                    $("#balance").html(balance);
                                }
                            }
                        });
                    }
                });
            });
        </script>
        <?php include("footer.php"); ?>

