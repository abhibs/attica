<?php
session_start();

$type = $_SESSION['usertype'] ?? '';

/* ---------------- MENUS / HEADER ---------------- */
if ($type == 'Zonal') {
    include("header.php");
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
} elseif ($type == 'Software') {
    include("header.php");
    include("menuSoftware.php");
} elseif ($type == 'SubZonal') {
    include("header.php");
    include("menuSubZonal.php");
} else {
    include("logout.php");
    exit;
}

include("dbConnection.php");
$date = date('Y-m-d');

/*
 * Branch list:
 *  - Only status = 1
 *  - users.username = branchId
 *  - employee.empId = users.employeeId
 *  - If usertype is Zonal/SubZonal -> filter by branch.ezviz_vc = $_SESSION['username']
 *  - For SubZonal: also compute unread_count and order by it (unread on top)
 */

$username = $_SESSION['username'] ?? ($_SESSION['employeeId'] ?? ''); // used for Zonal/SubZonal mapping

if ($type === 'SubZonal') {

    // SubZonal: only their branches, plus unread_count, ordered with unread on top
    $usernameEsc = mysqli_real_escape_string($con, $username);

    $sqlBranches = "
        SELECT 
            b.branchId,
            b.branchName,
            e.name    AS managerName,
            e.contact AS managerContact,
            (
                SELECT COUNT(*)
                FROM branch_chat bc
                WHERE bc.branchId    = b.branchId
                  AND bc.sender_type = 'Branch'
                  AND bc.seen_center = 0
            ) AS unread_count
        FROM branch b
        LEFT JOIN users    u ON u.username = b.branchId
        LEFT JOIN employee e ON e.empId    = u.employeeId
        WHERE b.status   = 1
          AND b.ezviz_vc = '$usernameEsc'
        ORDER BY unread_count DESC, b.branchName ASC
    ";

} elseif ($type === 'Zonal') {

    // Zonal: only their branches (no unread sorting requested)
    $usernameEsc = mysqli_real_escape_string($con, $username);

    $sqlBranches = "
        SELECT 
            b.branchId,
            b.branchName,
            e.name    AS managerName,
            e.contact AS managerContact
        FROM branch b
        LEFT JOIN users    u ON u.username = b.branchId
        LEFT JOIN employee e ON e.empId    = u.employeeId
        WHERE b.status   = 1
          AND b.ezviz_vc = '$usernameEsc'
        ORDER BY b.branchName ASC
    ";

} else {

    // Master / others: all branches
    $sqlBranches = "
        SELECT 
            b.branchId,
            b.branchName,
            e.name    AS managerName,
            e.contact AS managerContact
        FROM branch b
        LEFT JOIN users    u ON u.username = b.branchId
        LEFT JOIN employee e ON e.empId    = u.employeeId
        WHERE b.status = 1
        ORDER BY b.branchName ASC
    ";
}

$branchListRes = mysqli_query($con, $sqlBranches);
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

    /* Chat modal styles (kept minimal – full modal & JS in footer) */
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

<div id="wrapper">
    <div class="content">
        <!-- BRANCH-WISE LIST WITH CHAT BUTTON -->
        <div class="row" style="margin-top:10px">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body">
                        <table id="example5" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Branch</th>
                                    <th>Branch ID</th>
                                    <th>Branch Manager</th>
                                    <th>Contact</th>
                                    <th class="text-center">Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($branchListRes)) {
                                    $branchId   = $row['branchId'];
                                    $branchName = $row['branchName'];
                                    $mgrName    = $row['managerName'];
                                    $mgrContact = $row['managerContact'];
                                    $unreadCnt  = (int)($row['unread_count'] ?? 0); // only set for SubZonal

                                    $branchIdEsc   = htmlspecialchars($branchId,   ENT_QUOTES, 'UTF-8');
                                    $branchNameEsc = htmlspecialchars($branchName, ENT_QUOTES, 'UTF-8');
                                    $mgrNameEsc    = htmlspecialchars($mgrName,    ENT_QUOTES, 'UTF-8');
                                    $mgrContactEsc = htmlspecialchars($mgrContact, ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $branchNameEsc; ?></td>
                                        <td><?php echo $branchIdEsc; ?></td>
                                        <td><?php echo $mgrNameEsc ?: '-'; ?></td>
                                        <td><?php echo $mgrContactEsc ?: '-'; ?></td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-xs btn-info btn-msg-branch"
                                                    data-branch-id="<?php echo $branchIdEsc; ?>"
                                                    data-branch-name="<?php echo $branchNameEsc; ?>"
                                                    data-unread="<?php echo $unreadCnt; ?>">
                                                <i class="fa fa-comments"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                }
                                if ($i === 1) {
                                    echo '<tr><td colspan="6" class="text-center">No active branches found.</td></tr>';
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
</div>



<?php if ($type === 'SubZonal') { ?>
<script type="text/javascript">
jQuery(function ($) {
    if (!$.fn.DataTable) return;

    var table = $('#example5').DataTable();

    // Destroy previous initialization (pagination was applied)
    table.destroy();

    // Reinitialize WITHOUT pagination
    $('#example5').DataTable({
        pageLength: -1,         // show ALL rows
        paging: false,          // disable pagination UI
        searching: true,        // keep search
        ordering: false,        // SQL already sorted by unread count
        info: false,            // hide "Showing X of Y entries"
        lengthChange: false     // hide "Show entries" dropdown
    });
});
</script>
<?php } else { ?>
<!-- Other roles: normal paging -->
<script type="text/javascript">
jQuery(function ($) {
    if (!$.fn.dataTable) return;

    if (!$.fn.dataTable.isDataTable('#example5')) {
        $('#example5').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            order: [[1, 'asc']]
        });
    }
});
</script>
<?php } ?>

