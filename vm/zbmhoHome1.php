<?php
    session_start();
    error_reporting(E_ERROR | E_PARSE);
    $type = $_SESSION['usertype'];
    if ($type == 'VM-HO') {
        include("headervc.php");
        include("menuvc.php");
    } else {
        include("logout.php");
        exit;
    }

    // Assuming $con is initialized in included header(s)
    $date  = date('Y-m-d');
    $empId = $_SESSION['employeeId'];

    // ---------------- VM-ASSIGNED BRANCHES (for right-side table) ----------------
    $vmBranchListRow = mysqli_fetch_assoc(mysqli_query(
        $con,
        "SELECT agentId, branch FROM vmagent WHERE agentId='" . mysqli_real_escape_string($con, $empId) . "'"
    ));
    $branches = [];
    if (!empty($vmBranchListRow['branch'])) {
        // Expecting comma-separated branchIds in vmagent.branch
        $tmp = array_filter(array_map('trim', explode(',', $vmBranchListRow['branch'])));
        // keep unique, non-empty
        $branches = array_values(array_unique(array_filter($tmp, fn($x) => $x !== '')));
    }

    $branchData = [];
    if (!empty($branches)) {
        // Build IN ('a','b',...) safely
        $quoted = array_map(function($b) use ($con) {
            return "'" . mysqli_real_escape_string($con, $b) . "'";
        }, $branches);
        $inList = implode(',', $quoted);

        $branchSQL = mysqli_query($con, "
            SELECT 
                b.branchId,
                b.branchName,
                b.meet,
                b.ezviz_vc,
                e.name,
                b.openclosestatus
            FROM branch b
            LEFT JOIN employee e ON b.ezviz_vc = e.empId
            WHERE b.branchId IN ($inList)
              AND b.branchId <> ''
        ");

        while ($row = mysqli_fetch_assoc($branchSQL)) {
            $branchData[] = $row;
        }
    }

    // ---------------- ALL ACTIVE BRANCHES (distinct, ordered by branchId ASC) for the dropdown ----------------
    $branchList = [];
    $branchSQL2 = mysqli_query($con, "
        SELECT DISTINCT
            b.branchId,
            b.branchName,
            b.meet,
            b.ezviz_vc,
            e.name,
            b.openclosestatus
        FROM branch b
        LEFT JOIN employee e ON b.ezviz_vc = e.empId
        WHERE b.status = 1
          AND b.branchId <> ''
        ORDER BY b.branchId ASC
    ");
    while ($row = mysqli_fetch_assoc($branchSQL2)) {
        $branchList[] = $row;
    }

    // ---------------- TODAY'S CUSTOMERS FOR THE VM-ASSIGNED BRANCHES ----------------
    $customer = [];
    if (!empty($branches)) {
        $quoted = array_map(function($b) use ($con) {
            return "'" . mysqli_real_escape_string($con, $b) . "'";
        }, $branches);
        $inList = implode(',', $quoted);

        $customerDetails = mysqli_query($con, "
            SELECT branch,
                   customer,
                   CONCAT('XXXXXX', RIGHT(contact, 4)) AS contact 
            FROM everycustomer 
            WHERE branch IN ($inList)
              AND branch <> ''
              AND date = '" . mysqli_real_escape_string($con, $date) . "'
              AND status IN ('0','Blocked')
        ");
        while ($row = mysqli_fetch_assoc($customerDetails)) {
            $customer[] = $row;
        }
    }
?>
<style>
    .list-cust{
        list-style-type: square;
        margin: 0;
        padding-left: 15px;
    }
    .li-cust{
        padding-bottom: 15px;
    }
    form h5{
        color: #123C69;
        text-transform: uppercase;
        font-size: 12px;
    }
    .theadRow th { white-space: nowrap; }
</style>

<!--   MODAL - PASSWORD CHANGE   -->
<div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content ">
            <div class="color-line"></div>
            <span class="fa fa-close modaldesign" data-dismiss="modal"></span>
            <div class="modal-header" style="background-color: #123C69;color: #f0f8ff;">
                <h4>EMP ID : <?php echo htmlspecialchars($empId); ?></h4>
            </div>
            <div class="modal-body">
                <form action="vmSubmit.php" method="POST">
                    <input type="hidden" name="employeeId" value="<?php echo htmlspecialchars($empId); ?>">
                    <div class="form-group" style="margin-top:30px">
                        <label class="col-sm-3 control-label text-success">Enter New Password</label>
                        <div class="col-sm-9">
                            <input style="font-weight:500;" type="text" class="form-control" name="password" value="" placeholder="PASSWORD" required>
                            <span class="help-block m-b-none font-bold">DO NOT USE YOUR PERSONAL INFORMATION</span>
                        </div>
                    </div>
                    <button style="margin-top:20px;" class="btn btn-success" name="vmPasswordChange">CHANGE</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="wrapper"> 
    <div class="row content">
        
        <div class="col-lg-5">
            <div class="hpanel">
                <div class="panel-heading hbuilt" style="margin-bottom:10px">
                    <h3 class="text-success text-center">EMP ID : <span style="color:#990000"><b><?php echo htmlspecialchars($empId); ?></b></span></h3>
                </div>
                <div class="panel-body">
                    <form name="add-new-customer" method="POST" class="form-horizontal" action="vmSubmit.php" onsubmit="vmSubmitNC.disabled = true; return true;">
                        <input type="hidden" name="VMsubmitNCHidden" value="true">
                        
                        <div class="col-sm-12">
                            <select class="form-control m-b" name="branchID" required>
                                <option selected="true" disabled="disabled" value="">Select Branch</option>
                                <?php
                                    foreach ($branchList as $value) {
                                        $bid = $value['branchId'];
                                        $bname = $value['branchName'];
                                        echo '<option value="' . htmlspecialchars($bid) . '" data-branch-name="' . htmlspecialchars($bname) . '">'
                                             . htmlspecialchars($bname)
                                             . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-sm-6">
                            <h5>Name</h5>
                            <input type="text" name="name" id="name" placeholder="Customer Name" class="form-control" autocomplete="off" required>
                        </div>
                        <div class="col-sm-6">
                            <h5>Mobile</h5>
                            <input type="text" name="contact" id="mobile" placeholder="Contact Number" maxlength="10" class="form-control" autocomplete="off" required>
                        </div>
                        
                        <label class="col-sm-12 control-label"><br></label>
                        <div class="col-sm-4">
                            <select class="form-control m-b" name="type" id="billType" required>
                                <option selected="true" disabled="disabled" value="">Type</option>
                                <option value="physical">Physical</option>
                                <option value="release">Release</option>
                            </select>
                        </div>
                        <div id="typeData"></div>
                        
                        <label class="col-sm-12 control-label"></label>
                        <div class="col-sm-4">
                            <h5>No of Ornaments</h5>
                            <input type="text" name="itemCount" placeholder="Count" required class="form-control" autocomplete="off">
                        </div>
                        <div class="col-sm-4">
                            <h5>Gross Weight</h5>
                            <input type="text" name="grossW" placeholder="Gross W" required class="form-control" autocomplete="off">
                        </div>
                        <div class="col-sm-4">
                            <h5>Hallmark</h5>
                            <select class="form-control m-b" name="hallmark" required>
                                <option selected="true" disabled="disabled" value="">Select</option>
                                <option value="yes">Hallmark</option>
                                <option value="no">Non Hallmark</option>
                            </select>
                        </div>
                        <label class="col-sm-12 control-label"></label>
                        <div class="col-sm-12">
                            <textarea name="remarks" placeholder="Remarks" class="form-control" autocomplete="off"></textarea>
                        </div>
                        
                       <!-- <label class="col-sm-12 control-label"><br></label>
                        <div class="col-sm-4">
                            <button type="button" id="send-otp-btn" class="btn btn-success btn-block"><i class="fa_Icon fa fa-paper-plane"></i> OTP</button>
                            <i id="otp-timer-display">Timer : <span id="otp-timer-count"></span></i>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" placeholder="OTP" class="form-control" maxlength="6" required name="otp" id="xotp">
			</div>
			-->
                        <div class="col-sm-4" style="margin-top: 20px;">
                            <button class="btn btn-success btn-block" id="updateCustomer" name="vmSubmitNC" type="submit"><span class="fa_Icon fa fa-save"></span> SUBMIT</button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="hpanel">
                <div class="panel-heading hbuilt text-center">
                    <h3 class="text-success">ASSIGNED BRANCHES</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="border-collapse: separate;border-spacing: 0 15px;">
                            <thead>
                                <tr class="theadRow">
                                    <th>Branch ID</th>
                                    <th>Branch Name</th>
                                    <th>Zonal Name</th>
                                    <th>Close / Open</th>
                                    <th>Customer</th>
                                    <th class='text-center'><a href="https://sfu.mirotalk.com/" target="__BLANK" style="color: #fff">Meet</a></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($branchData as $value) {
                                        $bId   = htmlspecialchars($value['branchId']);
                                        $bName = htmlspecialchars($value['branchName']);
                                        $zName = htmlspecialchars($value['name']);
                                        $meet  = $value['meet']; // may be empty
                                        $ocs   = $value['openclosestatus'];

                                        echo "<tr style='background-color: #ECECEC;'>";
                                        echo "<td>{$bId}</td>";
                                        echo "<td class='text-success'><a title='Access Branch' href='xeveryCustomer1.php?mn=" . base64_encode($value['branchId']) . "'>{$bName}</a></td>";
                                        echo "<td>{$zName}</td>";
                                        echo "<td>
                                                <form method='POST' action='vmSubmit.php' class='form-inline'>
                                                    <input type='hidden' name='branchId' value='{$bId}'>
                                                    <label class='radio-inline' style='margin-right:8px;'>
                                                        <input type='radio' name='status' value='1' " . ($ocs == '1' ? 'checked' : '') . "> Open
                                                    </label>
                                                    <label class='radio-inline' style='margin-right:8px;'>
                                                        <input type='radio' name='status' value='0' " . ($ocs == '0' ? 'checked' : '') . "> Close
                                                    </label>
                                                    <button type='submit' name='openclosesubmit' class='btn btn-primary btn-sm'>Submit</button>
                                                </form>
                                              </td>";

                                        echo "<td><ul class='list-cust'>";
                                        foreach ($customer as $cval) {
                                            if ($value['branchId'] == $cval['branch']) {
                                                echo "<li class='li-cust'><span style='margin-right:30px;'>" . htmlspecialchars($cval['customer']) . "</span>" . htmlspecialchars($cval['contact']) . "</li>";
                                            }
                                        }
                                        echo "</ul></td>";

                                        if (empty($meet)) {
                                            echo "<td></td>";
                                        } else {
                                            echo "<td class='text-center'><a href='" . htmlspecialchars($meet) . "' target='_blank'><span class='fa_Icon fa fa-video-camera'></span></a></td>";
                                        }
                                        echo "</tr>";
                                    }
                                    // free arrays
                                    $branchData = null;
                                    $customer = null;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- <button type="button" class="btn btn-success" style="float:right;margin-bottom:20px" data-toggle="modal" data-target=".bd-example-modal-sm">RESET PASSWORD</button>-->
        </div>
        
        <div style="clear:both"></div>
    </div>
   <!-- <script src="scripts/AGPLotp.js"></script> -->
    <script>
        const type = document.getElementById("billType");
        const typeData = document.getElementById('typeData');
        
        const physicalData = "<div class='col-sm-8 text-center'><div class='radio radio-info radio-inline'><input type='radio' value='with' name='withMetal' checked=''><label> With Gold </label></div><div class='radio radio-info radio-inline'><input type='radio' value='without' name='withMetal'><label> Without Gold </label></div></div><div class='col-sm-8 text-center' style='margin-bottom: 20px; margin-top: 10px;'><div class='radio radio-info radio-inline'><input type='radio' value='no' name='pledge' checked=''><label> Billing </label></div><div class='radio radio-info radio-inline'><input type='radio' value='yes' name='pledge'><label> Pledge </label></div></div>";
        
        const releaseData = "<div class='col-sm-4'><select class='form-control m-b' name='relSlips' required><option selected='true' disabled='disabled' value=''>Rel Slips</option><option value='yes'>With Slips</option><option value='no'>Without Slips</option></select></div><div class='col-sm-4'><input type='text' name='relAmount' placeholder='Rel Amount' class='form-control' autocomplete='off' required></div>";
        
        type.addEventListener('change', (e)=>{
            let billType = type.value;
            if(billType === 'physical'){
                typeData.innerHTML = physicalData;
            }
            else if(billType === 'release'){
                typeData.innerHTML = releaseData;
            } else {
                typeData.innerHTML = "";
            }
        });
    </script>
    <!-- <script>
        $(document).ready(function() {
            // DISABLE / ENABLE SUBMIT BUTTON
            $('#updateCustomer').attr("disabled", true);
            $("#xotp").keyup(function() {
                var data = $('#xotp').val();
                var count = data.toString().length;
                if(count===6){
                    var req = $.ajax({
                        url: "../otpValid.php",
                        type: "POST",
                        data: { data },
                    });
                    req.done(function(msg) {
                        $("#xotp").val(msg);
                        if (msg === "OTP Validated") {
                            $('#xotp').attr('readonly', 'true');
                            $('#updateCustomer').attr("disabled", false);
                        }
                        else if (msg === "Invalid OTP") {
                            alert(msg);
                        }
                    });
                }
            });
        });
    </script>-->
<?php include("footerNew.php"); ?>

