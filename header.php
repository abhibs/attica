<?php
error_reporting(E_ERROR | E_PARSE);
session_start();

if (!isset($_SESSION['login_username']) || empty($_SESSION['login_username'])) {
    $_SESSION = array();
    session_destroy();
    header("location:index.php");
    exit;
}

include("dbConnection.php");

date_default_timezone_set("Asia/Kolkata");
$date = date('Y-m-d');

$USER_TYPE = isset($_SESSION['usertype']) ? strtoupper(preg_replace('/\s+/', '', (string)$_SESSION['usertype'])) : '';

if ($USER_TYPE === "ZONAL") {
    $zonalLog = mysqli_fetch_assoc(mysqli_query($con, "SELECT date FROM users WHERE username='" . mysqli_real_escape_string($con, $_SESSION['login_username']) . "'"));
    if (!empty($zonalLog['date']) && $zonalLog['date'] != $date) {
        header("location:logout.php");
        exit;
    }
}

if (in_array($USER_TYPE, ["APPROVALTEAM", "MASTER"], true)) {
    $customerQuery = mysqli_fetch_assoc(mysqli_query($con, "SELECT count(*) as blocked FROM everycustomer WHERE date='$date' and status='Blocked'"));
    $customerCount = $customerQuery['blocked'] ?? 0;
}

$incentiveMsg = '';
if ($USER_TYPE !== "SOFTWARE" && $USER_TYPE !== "ITMASTER") {
    $branchcode = '';
    if (!empty($_SESSION['branchCode'])) {
        $branchcode = trim($_SESSION['branchCode']);
    } else {
        if (!empty($_SESSION['login_username'])) {
            if ($stmtB = mysqli_prepare($con, "SELECT branchId FROM users WHERE username=? LIMIT 1")) {
                mysqli_stmt_bind_param($stmtB, "s", $_SESSION['login_username']);
                mysqli_stmt_execute($stmtB);
                mysqli_stmt_bind_result($stmtB, $branchcode);
                mysqli_stmt_fetch($stmtB);
                mysqli_stmt_close($stmtB);
                $branchcode = trim((string)$branchcode);
            }
        }
    }

    $listA = ['AGPL104','AGPL095','AGPL030','AGPL060','AGPL056','AGPL031','AGPL029','AGPL144','AGPL017','AGPL005','AGPL130','AGPL176','AGPL051','AGPL216','AGPL079','AGPL159','AGPL078','AGPL080','AGPL226'];
    $listB = ['AGPL222','AGPL091','AGPL217','AGPL061','AGPL175','AGPL076','AGPL059','AGPL150','AGPL069','AGPL032','AGPL071','AGPL196','AGPL055','AGPL093','AGPL068','AGPL164','AGPL072','AGPL081','AGPL138','AGPL048','AGPL128','AGPL131','AGPL102','AGPL127','AGPL033','AGPL137','AGPL018','AGPL126','AGPL041','AGPL220','AGPL063','AGPL006','AGPL145','AGPL023','AGPL067','AGPL054','AGPL036','AGPL046','AGPL043','AGPL204','AGPL146','AGPL007','AGPL161','AGPL182','AGPL022'];
    $listC = ['AGPL149','AGPL049','AGPL234','AGPL038','AGPL066','AGPL045','AGPL019','AGPL101','AGPL120','AGPL165','AGPL034','AGPL082','AGPL106','AGPL094','AGPL089','AGPL037','AGPL203','AGPL012','AGPL118','AGPL026','AGPL027','AGPL020','AGPL209','AGPL221','AGPL170','AGPL207','AGPL110','AGPL119','AGPL064','AGPL124','AGPL147','AGPL215','AGPL225','AGPL148','AGPL129','AGPL111','AGPL199','AGPL039','AGPL143','AGPL151','AGPL123','AGPL065','AGPL044','AGPL113','AGPL047','AGPL035','AGPL042'];

    $getListTypeForBranch = function(string $branchId, array $listA, array $listB, array $listC): ?string {
        $bid = strtoupper(trim($branchId));
        if (in_array($bid, array_map('strtoupper',$listA), true)) return 'A';
        if (in_array($bid, array_map('strtoupper',$listB), true)) return 'B';
        if (in_array($bid, array_map('strtoupper',$listC), true)) return 'C';
        return null;
    };

    if (!empty($branchcode)) {
        $listType = $getListTypeForBranch($branchcode, $listA, $listB, $listC);
        $incentiveMap = ['A' => 3000, 'B' => 2000, 'C' => 1000];
        if ($listType !== null) {
            $bm_abm = $incentiveMap[$listType];
            $incentiveMsg = "Incentives — Branch Grade: {$listType}. BM and ABM ₹" . number_format($bm_abm) . " | TE ₹500";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#08347d">
    <title>Attica Gold</title>
    <link rel="shortcut icon" type="image/png" href="images/favicon.png" />
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.css" />
    <link rel="stylesheet" href="vendor/metisMenu/dist/metisMenu.css" />
    <link rel="stylesheet" href="vendor/animate.css/animate.css" />
    <link rel="stylesheet" href="vendor/bootstrap/dist/css/bootstrap.css" />
    <link rel="stylesheet" href="vendor/xeditable/bootstrap3-editable/css/bootstrap-editable.css" />
    <link rel="stylesheet" href="vendor/select2-3.5.2/select2.css" />
    <link rel="stylesheet" href="vendor/select2-bootstrap/select2-bootstrap.css" />
    <link rel="stylesheet" href="vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css" />
    <link rel="stylesheet" href="vendor/bootstrap-datepicker-master/dist/css/bootstrap-datepicker3.min.css" />
    <link rel="stylesheet" href="vendor/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" />
    <link rel="stylesheet" href="vendor/clockpicker/dist/bootstrap-clockpicker.min.css" />
    <link rel="stylesheet" href="vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
    <link rel="stylesheet" href="fonts/pe-icon-7-stroke/css/helper.css" />
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="vendor/summernote/dist/summernote.css" />
    <link rel="stylesheet" href="vendor/summernote/dist/summernote-bs3.css" />
    <link rel="stylesheet" href="styles/static_custom.css">
    <script src="scripts/jquery.js"></script>
    <style>
        .dropdown-menu { max-height: 500px; overflow: scroll; }
        .incentive-marquee {
            background: #111;
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            font: 600 14px/1.4 system-ui, -apple-system, "Segoe UI", Roboto, Arial;
        }
    </style>
</head>
<body class="fixed-navbar sidebar-scroll">
    <div id="header">
        <div class="color-line">
            <!-- <marquee style="font-weight:bold; font-size:20px; color:#900; line-height:30px; letter-spacing:1px; margin-left:250px; margin-right:200px; margin-top:10px; display:none;" onmouseover="this.stop();" onmouseout="this.start();">
                <!?php
                if (!empty($incentiveMsg)) {
                    echo '<span class="incentive-marquee">' . htmlspecialchars($incentiveMsg, ENT_QUOTES, 'UTF-8') . '</span>';
                } else {
                    echo "Welcome to Attica Gold Pvt Ltd";
                }
                ?>
	    </marquee> -->

	 <marquee style="font-weight:bold; font-size:20px; color:#900; line-height:30px; letter-spacing:1px; margin-left:250px; margin-right:200px; margin-top:10px;" onmouseover="this.stop();" onmouseout="this.start();">
					<!--    WRITE ANY IMPORTANT MESSAGES HERE    -->
					<?php
					if($_SESSION['usertype'] == "Branch"){
						echo "CUSTOMER SHOULD BE WELL INFORMED THAT IMPS/RTGS WILL BE DONE IMMEDIATELY. HOWEVER, DELAY IN CREDIT OF THEIR ACCOUNTS  DEPENDS/DUE TO  ON BANK SERVER AND WE ARE NO WAY CONNECTED WITH THE DELAY.";
					    }
					?>
				</marquee>
        </div>
        <a href="https://atticagoldcompany.com" target="_blank">
            <div id="logo" class="light-version">
                <b><span>Attica Gold Pvt Ltd</span></b>
            </div>
        </a>
        <nav role="navigation">
            <div class="header-link hide-menu"><i style="color:#990000" class="fa fa-bars"></i></div>
            <a href="https://atticagoldcompany.com" target="_blank">
                <div class="small-logo"><span class="text-primary">Attica Gold</span></div>
            </a>
            <form role="search" class="navbar-form-custom" method="post" action="searchbills.php">
                <div class="form-group">
                    <input type="text" placeholder="<?php echo date("l / d-M-Y / h:i A"); ?>" class="form-control" name="search">
                </div>
            </form>
            <div class="mobile-menu">
                <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
            <div class="navbar-right">
                <ul class="nav navbar-nav no-borders">
                    <li class="dropdown">
                        <a href="SOP.php" title="Standard Operations Procedure">
                            <b style="color:#900">SOP</b>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle label-menu-corner" href="#" data-toggle="dropdown">
                            <i style="color:#900" class="fa fa-phone"></i>
                            <span class="label label-success" style="color:white;"><i class="fa fa-exclamation"></i></span>
                        </a>
                        <ul class="dropdown-menu hdropdown animated flipInX">
                            <div class="title text-success" style="background:#ffcf40;"><b><i class="fa fa-phone"></i> Emergency Contacts</b></div>
                            <li><a style="color:#900">Billing Approval / Customer OTP : 8925537846 </a></li>
                            <li><a style="color:#900">Release Approval (Cash) / Closing Reopen : 8925537846 </a></li>
                            <li><a style="color:#900">Software Issues : 8925537891 </a></li>
                            <li><a style="color:#900">Social Media : 8925537892 </a></li>
                            <li><a style="color:#900">Call Center : 8925536999 </a></li>
                            <li><a style="color:#900">Weighing Scale License Renewal : 9035015936 </a></li>
                            <li><a style="color:#900">Karat Meter : 8925537881 </a></li>
                            <li class="summary"><b><i style="color:#900" class="fa fa-phone"></i> IT / Webmail </b></li>
                            <li><a style="color:#900">KA : 8925537881</a></li>
                            <li><a style="color:#900">TN : 8925537882</a></li>
                            <li><a style="color:#900">AP : 8925537883</a></li>
                            <li class="summary"><b><i style="color:#900" class="fa fa-phone"></i> Release Approval (IMPS) / IMPS </b></li>
                            <li><a style="color:#900">KA : 8884414103</a></li>
                            <li><a style="color:#900">AP/T : 8884414300</a></li>
                            <li><a style="color:#900">TN : 8884410300</a></li>
                            <li class="summary"><b><i style="color:#900" class="fa fa-phone"></i> Zonals </b></li>
                            <li><a style="color:#900">BLR : 8925537861 </a></li>
                            <li><a style="color:#900">KA : 8925537866 </a></li>
                            <li><a style="color:#900">TN1 : 8925537870 , TN2 : 8925537008</a></li>
                            <li><a style="color:#900">TN3 : 8925537871</a></li>
                            <li><a style="color:#900">AP/TS : 8925537875 / 8925537876</a></li>
                            <li><a style="color:#900">8925537870 </a></li>
                            <li><a style="color:#900">8792966489 </a></li>
                            <li><a style="color:#900">8951949322 </a></li>
                            <li><a style="color:#900">8431158526 </a></li>
                            <li><a style="color:#900">8951949277 </a></li>
                            <li><a style="color:#900">8792966486 </a></li>
                            <li><a style="color:#900">8792966487 </a></li>
                            <li class="summary"><b><i style="color:#900" class="fa fa-phone"></i> Legal Department </b></li>
                            <li><a style="color:#900">Attica Legal : 8951949270</a></li>
                            <li><a style="color:#900">Pasha : 8880080900 </a></li>
                            <li class="summary"><b><i style="color:#900" class="fa fa-phone"></i> HR Department </b></li>
                            <li><a style="color:#900">KA : 8925537833 </a></li>
                            <li><a style="color:#900">AP/T : 8925537832 </a></li>
                            <li><a style="color:#900">TN : 8925537831 </a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="logout.php" title="Logout">
                            <b><i style="color:#900" class="pe-7s-upload pe-rotate-90"></i></b>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</body>
</html>


