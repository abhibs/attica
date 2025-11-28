<?php
    session_start();
    error_reporting(E_ERROR | E_PARSE);

    $type = $_SESSION['usertype'];
    if ($type == 'Master') {
        include("header.php");
        include("menumaster.php");
    } else if ($type == 'Software') {
        include("header.php");
        include("menuSoftware.php");
    } else {
        include("logout.php");
    }

    include("dbConnection.php"); // must define $con (mysqli)
    date_default_timezone_set("Asia/Kolkata");

    $date = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $current_first_date = date('Y-m-d', strtotime($yesterday . ' -30 day'));

    $branchId = $_GET["branchId"] ?? '';

    // Build WHERE condition for trans alias t
    if ($branchId == "") {
        $condition = " AND t.branchId='AGPL000' ";
    } else {
        // Keep your original intent: restrict to branch + date window + Approved
        $condition = " AND t.branchId='" . mysqli_real_escape_string($con, $branchId) . "' AND t.date BETWEEN '$current_first_date' AND '$date' AND t.status='Approved'";
    }
?>
<style>
    #wrapper{ background:#f5f5f5; }
    #wrapper h3{ text-transform:uppercase; font-weight:600; font-size:20px; color:#123C69; }
    .form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control{ background-color:#fffafa; }
    .text-success{ color:#123C69; text-transform:uppercase; font-weight:bold; font-size:12px; }
    .btn-primary{ background-color:#123C69; }
    .theadRow { text-transform:uppercase; background-color:#123C69!important; color:#f2f2f2; font-size:11px; }
    .dataTables_empty{ text-align:center; font-weight:600; font-size:12px; text-transform:uppercase; }
    .btn-success{
        display:inline-block; padding:0.7em 1.4em; margin:0 0.3em 0.3em 0; border-radius:0.15em; box-sizing:border-box;
        text-decoration:none; font-size:11px; font-family:'Roboto',sans-serif; text-transform:uppercase; color:#fffafa;
        background-color:#123C69; box-shadow:inset 0 -0.6em 0 -0.35em rgba(0,0,0,0.17); text-align:center; position:relative;
    }
    .fa_Icon { color:#ffcf40; }
    fieldset {
        margin-top:1.5rem; margin-bottom:1.5rem; border:none; border:5px solid #fff; border-radius:10px; padding:5px;
        box-shadow: rgb(50 50 93 / 25%) 0px 50px 100px -20px, rgb(0 0 0 / 30%) 0px 30px 60px -30px, rgb(10 37 64 / 35%) 0px -2px 6px 0px inset;
    }
    legend{
        margin-left:8px; width:400px; background-color:#123C69; padding:5px 15px; line-height:30px; font-size:18px; color:white;
        text-shadow:1px 1px 1px rgba(0,0,0,0.5); transform:translateX(-1.1rem);
        box-shadow:-1px 1px 1px rgba(0,0,0,0.8); margin-bottom:0px; letter-spacing:2px; position:relative;
    }
    button { transform:none; box-shadow:none; }
    button:hover { background-color:gray; cursor:pointer; }
    legend:after {
        content:""; height:0; width:0; background-color:transparent;
        border-top:0 solid transparent; border-right:0.35rem solid black; border-bottom:0.45rem solid transparent; border-left:0 solid transparent;
        position:absolute; left:-0.075rem; bottom:-0.45rem;
    }
    .row{ margin-left:0px; margin-right:0px; }
    #search_branchId,#search_date{
        padding:10px; height:50px; font-size:16px; color:grey; box-sizing:border-box; border:2px solid #ccc!important;
    }
    .muted { color:#6b7280; font-size:11px; }
    @media only screen and (max-width: 500px) {
        legend{ width:390px; font-size:12px; }
    }
</style>

<!-- Branch list for autocomplete -->
<datalist id="branchList">
<?php
    $branches = mysqli_query($con, "SELECT branchId,branchName FROM branch WHERE status=1");
    while ($branchList = mysqli_fetch_assoc($branches)) {
        $sel = ($branchId === $branchList['branchId']) ? 'selected="selected"' : '';
        echo '<option value="'.htmlspecialchars($branchList['branchId']).'" '.$sel.' label="'.htmlspecialchars($branchList['branchName']).'"></option>';
    }
?>
</datalist>

<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <fieldset>
                    <legend><i style="padding-top:15px" class="fa_Icon fa fa-edit"></i> GOLD SEND DATA</legend>

                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-9"></div>
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <input list="branchList" class="form-control" id="search_branchId" name="search_branchId" placeholder="SELECT BRANCH" value="<?php echo htmlspecialchars($branchId); ?>">
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary btn-block" style="height:49px;" id="search_walkin" type="button"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-body" style="background:#f5f5f5;">
                        <div class="table-responsive">
                            <table id="example5" class="table table-striped table-bordered">
                                <thead>
                                    <tr class="theadRow">
                                        <th><i class="fa fa-sort-numeric-asc"></i></th>
                                        <th>BRANCH</th>
                                        <th>NAME</th>
                                        <th>PAYMENT TYPE</th>
                                        <th>METAL</th>
                                        <th>GROSS WT</th>
                                        <th>NET WT</th>
                                        <th>DATE</th>
                                        <th>STATUS</th>
                                        <th>STA</th>
                                        <th>STA DATE</th>
                                        <th>CURRENT BRANCH</th> <!-- NEW -->
                                        <th>UPDATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        // Safer explicit JOIN + selected columns (includes CurrentBranch)
                                        $sqlQ = "SELECT t.id, b.branchName, t.branchId, t.name, t.phone, t.paymentType, t.metal,
                                                        t.grossW, t.netW, t.date, t.status, t.sta, t.staDate, t.CurrentBranch
                                                 FROM trans t
                                                 JOIN branch b ON t.branchId = b.branchId
                                                 WHERE 1=1 $condition
                                                 ORDER BY t.id DESC";
                                        $sqlA = mysqli_query($con, $sqlQ);
                                        $i = 1;
                                        while ($rowA = mysqli_fetch_assoc($sqlA)) {
                                            echo "<tr>";
                                            echo "<form method='POST' action='editAjax.php'>";
                                            echo "<input type='hidden' name='trans_id' value='".htmlspecialchars($rowA['id'])."'>";
                                            echo "<input type='hidden' name='branchId' value='".htmlspecialchars($rowA['branchId'])."'>";
                                            echo "<td>". $i ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['branchName']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['name']) ."<br>". htmlspecialchars($rowA['phone']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['paymentType']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['metal']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['grossW']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['netW']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['date']) ."</td>";
                                            echo "<td>". htmlspecialchars($rowA['status']) ."</td>";
                                            echo "<td><input type='text' class='form-control' name='sta' value='".htmlspecialchars($rowA['sta'])."'></td>";
                                            echo "<td><input type='text' class='form-control' name='staDate' value='".htmlspecialchars($rowA['staDate'])."'></td>";

                                            // NEW: Current Branch editor (with datalist for AGPL codes)
                                            echo "<td>
                                                    <input list='branchList' class='form-control' name='CurrentBranch' value='".htmlspecialchars($rowA['CurrentBranch'])."' placeholder='Select or type AGPL code'>
                                                    <small class=\"muted\">Existing: ".htmlspecialchars($rowA['CurrentBranch'])."</small>
                                                  </td>";

                                            echo "<td style='text-align:center'>
                                                    <button onClick=\"javascript: return confirm('Please confirm Again');\" class='btn btn-lg' name='updateGoldSendData' type='submit' style='background-color:transparent'>
                                                        <i class='fa fa-pencil-square-o text-success' style='font-size:16px'></i>
                                                    </button>
                                                  </td>";
                                            echo "</form>";
                                            echo "</tr>";
                                            $i++;
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div style="clear:both"></div>
    <?php include("footer.php"); ?>
</div>

<script>
document.getElementById("search_walkin").addEventListener("click", function(){
    var url = window.location.href.split('?')[0];
    var branch_id = document.getElementById("search_branchId").value;
    if(branch_id === ""){
        alert("Please select a branch");
    } else {
        window.open(url + "?branchId=" + encodeURIComponent(branch_id), "_self");
    }
});
</script>

