<?php
session_start();
error_reporting(E_ERROR | E_PARSE);

include("dbConnection.php");

$type       = $_SESSION['usertype']       ?? '';
$employeeId = $_SESSION['login_username'] ?? ($_SESSION['employeeId'] ?? '');

// Canonical IMPS employeeId (used in DB as imps_id)
$impsEmployeeId = '';
if ($type === 'Accounts IMPS' || $type === 'AccHead') {   // ← add AccHead here
    $sessLogin = $_SESSION['login_username'] ?? '';
    $sessEmp   = $_SESSION['employeeId'] ?? '';

    if ($sessEmp !== '') {
        $impsEmployeeId = $sessEmp;
    } else {
        $loginEsc = mysqli_real_escape_string($con, $sessLogin);
        $qImps = mysqli_query(
            $con,
            "SELECT employeeId 
             FROM users 
             WHERE username = '$loginEsc' OR employeeId = '$loginEsc'
             LIMIT 1"
        );
        if ($qImps && mysqli_num_rows($qImps) > 0) {
            $rowImps = mysqli_fetch_assoc($qImps);
            $impsEmployeeId = $rowImps['employeeId'] ?? '';
        }
    }
}


/* ----------------- ACCESS + MENUS ----------------- */
if ($type == 'SubZonal') {
    include("header.php");
    include("menuSubZonal.php");
} else if ($type == 'AccHead') {
    include("header.php");
    include("menuaccHeadPage.php");
} else if ($type == 'Accounts') {
    include("header.php");
    include("menuacc.php");
} else if ($type == 'Accounts IMPS') {
    include("header.php");
    include("menuimpsAcc.php");
} else if ($type == 'Master') {
    include("header.php");
    include("menumaster.php");
} else {
    include("logout.php");
    exit;
}

/* ----------------- RESOLVE DISPLAY NAME ----------------- */
$myName = '';
if ($employeeId !== '') {
    if ($type == 'SubZonal') {
        $empEsc = mysqli_real_escape_string($con, $employeeId);
        $resMe  = mysqli_query($con, "SELECT name FROM employee WHERE empId = '$empEsc' LIMIT 1");
        if ($resMe && mysqli_num_rows($resMe) > 0) {
            $rowMe  = mysqli_fetch_assoc($resMe);
            $myName = $rowMe['name'] ?? '';
        }
    } else {
        // Accounts IMPS / Master / Accounts / AccHead – try users.agent first
        $empEsc = mysqli_real_escape_string($con, $employeeId);
        $resMe  = mysqli_query($con, "SELECT agent FROM users WHERE employeeId = '$empEsc' LIMIT 1");
        if ($resMe && mysqli_num_rows($resMe) > 0) {
            $rowMe  = mysqli_fetch_assoc($resMe);
            $myName = $rowMe['agent'] ?? '';
        }
        if ($myName === '') {
            // Fallback to employee.name if present
            $resMe2 = mysqli_query($con, "SELECT name FROM employee WHERE empId = '$empEsc' LIMIT 1");
            if ($resMe2 && mysqli_num_rows($resMe2) > 0) {
                $rowMe2  = mysqli_fetch_assoc($resMe2);
                $myName = $rowMe2['name'] ?? '';
            }
        }
    }
}

/* ----------------- IMPS LIST (for Zonal view) ----------------- */
$impsList = [];
if ($type == 'SubZonal') {
    $sqlI = "
        SELECT 
            u.username,
            u.employeeId,
            u.agent
        FROM users u
        WHERE u.type IN ('Accounts IMPS','AccHead')
        ORDER BY u.agent ASC, u.username ASC
    ";
    $resI = mysqli_query($con, $sqlI);
    while ($rowI = mysqli_fetch_assoc($resI)) {
        $impsList[] = $rowI;
    }
}

/* ----------------- ZONAL LIST (for IMPS + MASTER view) ----------------- */
$zonalList = [];
if ($type == 'Accounts IMPS' || $type == 'Master' || $type == 'AccHead') {
    $sqlZ = "
        SELECT 
            u.employeeId,
            COALESCE(e.name, u.agent, u.username) AS zonal_name
        FROM users u
        LEFT JOIN employee e ON e.empId = u.employeeId
        WHERE u.type = 'SubZonal'
        AND u.employeeId NOT IN ('1005678','1001234')
        ORDER BY zonal_name ASC
    ";
    $resZ = mysqli_query($con, $sqlZ);
    while ($rowZ = mysqli_fetch_assoc($resZ)) {
        $zonalList[] = $rowZ;
    }
}

/* ----------------- CHAT ROLE (for JS) ----------------- */
if ($type == 'SubZonal') {
    $CHAT_ROLE_ZIMPS = 'Zonal';
} elseif ($type == 'Accounts IMPS' || $type == 'AccHead') {
    $CHAT_ROLE_ZIMPS = 'IMPS';
} else { // Master, AccHead, Accounts etc. (only Master should come here)
    $CHAT_ROLE_ZIMPS = 'Master';
}

// For JS: zonal room id (for SubZonal view + notifications)
$ZONAL_ROOM_ID = ($type == 'SubZonal') ? $employeeId : '';

// For JS: IMPS employee id (for per-IMPS room)
$IMPS_EMP_ID = ($type == 'Accounts IMPS' || $type == 'AccHead') ? $employeeId : '';
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

    /* Chat bubbles */
    .zimps-bubble {
        padding: 6px 10px;
        margin: 3px 0;
        border-radius: 12px;
        max-width: 80%;
        clear: both;
        font-size: 12px;
    }
    .zimps-bubble-me {
        background: #123C69;
        color: #fff;
        margin-left: auto;
        text-align: right;
    }
    .zimps-bubble-them {
        background: #e4e6eb;
        margin-right: auto;
        text-align: left;
    }

    /* Unread badge on Message buttons (both sides) */
    .btn-zimps-msg,
    .btn-zimps-msg-imps {
        position: relative;
    }
    .btn-zimps-msg.btn-msg-new,
    .btn-zimps-msg-imps.btn-msg-new {
        background-color: #e60023;
        border-color: #e60023;
        color: #fff;
        box-shadow: 0 0 10px rgba(230,0,35,0.9),
                    0 0 18px rgba(230,0,35,0.7);
        animation: chatGlowZimps 1s ease-in-out infinite alternate;
    }
    .zimps-unread-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 9px;
        background: #ff0000;
        color: #fff;
        font-size: 10px;
        line-height: 18px;
        text-align: center;
        font-weight: 600;
        pointer-events: none;
    }
    @keyframes chatGlowZimps {
        0% {
            box-shadow: 0 0 10px rgba(230,0,35,0.7),
                        0 0 18px rgba(230,0,35,0.5);
        }
        100% {
            box-shadow: 0 0 16px rgba(230,0,35,1),
                        0 0 28px rgba(230,0,35,0.9);
        }
    }
    /* Zonal ↔ IMPS chat – fixed bottom-right like footer.php chat */
    .zimps-chat-modal .modal-dialog {
        width: 432px;          /* same as footer chat: 360px + a bit */
        max-width: 95%;
        position: fixed;
        right: 0;
        bottom: 0;
        top: auto;
        left: auto;
        margin: 0;             /* no centering margins */
        z-index: 1060;         /* above page content, below navbar/tooltips */
        height: 80vh;          /* taller dialog */
    }

    /* Let the whole modal use that height */
    .zimps-chat-modal .modal-content {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    /* Body grows to fill remaining space between header & footer */
    .zimps-chat-modal .modal-body {
        flex: 1 1 auto;
        overflow: hidden;
    }

    /* Message area fills the body – no fixed max-height */
    #zimpsChatMessages {
        height: 100%;
        max-height: none;
        overflow-y: auto;
        padding: 8px;
        background: #f5f5f5;
        border-radius: 4px;
    }

</style>

<div id="wrapper">
    <div class="content">
        <div class="row" style="margin-top:10px">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <h3>ZONAL ↔ IMPS CHAT</h3>
                    </div>
                    <div class="panel-body">

                        <?php if ($type == 'SubZonal'): ?>
                            <?php
                            $zonalIdForChat   = $employeeId;
                            $zonalNameForChat = $myName ?: $employeeId;
                            ?>
                            <p>
                                <span class="text-success">
                                    Logged in as Zonal:
                                </span>
                                <?php echo htmlspecialchars($zonalNameForChat); ?>
                                (<?php echo htmlspecialchars($employeeId); ?>)
                            </p>
                            <hr>

                            <?php if (!empty($impsList)): ?>
                                <h5 class="text-success">IMPS TEAM</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>USERNAME</th>
                                                <th>EMPLOYEE ID</th>
                                                <th>AGENT NAME</th>
                                                <th class="text-center">MESSAGE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($impsList as $irow) {
                                            $uNameEsc   = htmlspecialchars($irow['username'],   ENT_QUOTES, 'UTF-8');
                                            $empEsc     = htmlspecialchars($irow['employeeId'], ENT_QUOTES, 'UTF-8');
                                            $agentEsc   = htmlspecialchars($irow['agent'],      ENT_QUOTES, 'UTF-8');
                                            $zonalIdEsc = htmlspecialchars($zonalIdForChat,     ENT_QUOTES, 'UTF-8');
                                            $zonalNameEsc = htmlspecialchars($zonalNameForChat, ENT_QUOTES, 'UTF-8');

                                            echo "<tr>
                                                    <td>{$i}</td>
                                                    <td>{$uNameEsc}</td>
                                                    <td>{$empEsc}</td>
                                                    <td>{$agentEsc}</td>
                                                    <td class=\"text-center\">
                                                        <button type=\"button\"
                                                                class=\"btn btn-xs btn-info btn-zimps-msg-imps\"
                                                                data-zonal-id=\"{$zonalIdEsc}\"
                                                                data-zonal-name=\"{$zonalNameEsc}\"
                                                                data-imps-id=\"{$empEsc}\"
                                                                data-imps-username=\"{$uNameEsc}\"
                                                                data-imps-agent=\"{$agentEsc}\">
                                                            <i class=\"fa fa-comments\"></i>
                                                        </button>
                                                    </td>
                                                </tr>";
                                            $i++;
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>


                        <?php elseif ($type == 'Accounts IMPS' || $type == 'AccHead' || $type == 'Master'): ?>

                            <p>
                                <?php
                                    $roleLabel = 'Master';
                                    if ($type == 'Accounts IMPS') {
                                        $roleLabel = 'IMPS User';
                                    } elseif ($type == 'AccHead') {
                                        $roleLabel = 'AccHead';
                                    }
                                    ?>
                                    <span class="text-success">
                                        Logged in as <?php echo $roleLabel; ?>:
                                    </span>

                                <?php echo htmlspecialchars($myName ?: $employeeId); ?>
                                (<?php echo htmlspecialchars($employeeId); ?>)
                            </p>
                            <hr>
                            <div class="alert alert-info">
                                Select a Zonal (SubZonal user) below and click <strong>Message</strong> to open the shared chat.
                            </div>

                            <div class="table-responsive">
                                <table id="zimpsTable" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Employee ID</th>
                                            <th>Zonal Name</th>
                                            <th class="text-center">Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($zonalList as $z) {
                                        $empIdEsc  = htmlspecialchars($z['employeeId'], ENT_QUOTES, 'UTF-8');
                                        $nameEsc   = htmlspecialchars($z['zonal_name'], ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $empIdEsc; ?></td>
                                            <td><?php echo $nameEsc; ?></td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-xs btn-info btn-zimps-msg"
                                                    data-zonal-id="<?php echo $empIdEsc; ?>"
                                                    data-zonal-name="<?php echo $nameEsc; ?>"
                                                >
                                                    <i class="fa fa-comments"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                    if ($i === 1) {
                                        echo '<tr><td colspan="4" class="text-center">No SubZonal users found.</td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>
                            <div class="alert alert-warning">
                                You do not have access to Zonal ↔ IMPS chat.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.php"); ?>
</div>

<!-- Zonal ↔ IMPS Chat Modal -->
<div class="modal fade zimps-chat-modal" id="zimpsChatModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" >
            <div class="modal-header" style="padding:8px 15px;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h6 class="modal-title" style="font-size:20px">
                    <i class="fa fa-comments" style="color:#8B2030;"></i>
                    <span id="zimpsChatTitle"></span>
                </h6>
            </div>
            <div class="modal-body" style="padding:10px;position:relative;">
                <div id="zimpsChatMessages"
                     style="overflow-y:auto;padding:8px;background:#f5f5f5;border-radius:4px;">
                </div>
            </div>
            <div class="modal-footer" style="padding:8px 10px; margin-top:0px">
                <form id="zimpsChatSendForm" enctype="multipart/form-data"
                    style="width:100%;display:flex;gap:5px;align-items:center;">
                    <input type="hidden" id="zimpsZonalId" name="zonalId" value="">
                    <input type="hidden" id="zimpsImpsId"  name="impsId"  value="">

                    <input type="file" id="zimpsImageInput" name="image" accept="image/*" style="display:none;">
                    <label for="zimpsImageInput"
                        class="btn btn-default"
                        style="margin-bottom:0;">
                        <i class="fa fa-file-image-o"></i>
                    </label>

                    <button type="button"
                            id="zimpsCameraBtn"
                            class="btn btn-default"
                            style="margin-bottom:0;">
                        <i class="fa fa-camera"></i>
                    </button>
                    
                        <button type="button"
                            id="zimpsExcelBtn"
                            class="btn btn-default"
                            style="margin-bottom:0;"
                            title="Paste Excel as image">
                        <i class="fa fa-table"></i>
                    </button>

                    <input type="text" id="zimpsMessageInput" class="form-control"
                        placeholder="Type a message..." autocomplete="off">
                    <button type="submit" class="btn btn-success" id="zimpsSendBtn">Send</button>
                    <span id="zimpsUploadStatus" style="font-size:11px;color:#666;white-space:nowrap;"></span>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Camera overlay inside the chat modal -->
<div id="zimpsCameraOverlay" style="
    display:none;
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.7);
    z-index:9999;
    align-items:center;
    justify-content:center;
">
    <div style="background:#fff;border-radius:6px;padding:10px;max-width:420px;width:95%;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
            <strong>Camera</strong>
            <button type="button" id="zimpsCameraClose" class="btn btn-xs btn-default">X</button>
        </div>
        <video id="zimpsCameraVideo" autoplay playsinline style="width:100%;border-radius:4px;background:#000;"></video>
        <canvas id="zimpsCameraCanvas" style="display:none;"></canvas>
        <div style="margin-top:8px;text-align:right;">
            <button type="button" id="zimpsCameraCapture" class="btn btn-success btn-sm">Capture</button>
        </div>
    </div>
</div>


<!-- html2canvas for converting pasted Excel HTML into image -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Hidden container where pasted Excel HTML is rendered for capture -->
<div id="zimpsExcelPastePreview"
     style="position:fixed; left:-99999px; top:-99999px; opacity:0; pointer-events:none;"></div>


<script type="text/javascript">
(function($) {
    var CHAT_ROLE_ZIMPS  = '<?php echo $CHAT_ROLE_ZIMPS; ?>';  // 'Zonal' | 'IMPS' | 'Master'
    var ZONAL_ROOM_ID    = '<?php echo htmlspecialchars($ZONAL_ROOM_ID, ENT_QUOTES, "UTF-8"); ?>';
    var IMPS_EMPLOYEE_ID = '<?php echo htmlspecialchars($impsEmployeeId, ENT_QUOTES, "UTF-8"); ?>'; // NEW

var CURRENT_ZONAL_ID   = null;
var CURRENT_IMPS_ID    = null;
var zimpsRefreshTimer  = null;
var zimpsLastSeenId    = 0;
// Holds an image that was pasted or captured via camera
var zimpsPastedImageFile = null;
// Camera stream object
var zimpsCameraStream = null;
var zimpsExcelPasteMode = false;
// NEW: prevent double-send
var zimpsIsSending = false;
// Only scroll once when popup is freshly opened
var forceScrollToBottomOnce = false;
// Holds an image that was pasted from clipboard
var zimpsPastedImageFile = null;
    // Only scroll once when popup is freshly opened
    var forceScrollToBottomOnce = false;

    /* ---------- RENDER MESSAGES (NO continuous autoscroll) ---------- */
    function renderZimpsMessages(messages) {
        var $wrap = $('#zimpsChatMessages');
        if (!$wrap.length) return;

        $wrap.empty();
        var lastDay = null;

        messages.forEach(function(msg) {
            if (msg.day && msg.day !== lastDay) {
                lastDay = msg.day;
                var $dateRow = $('<div>').css({
                    textAlign: 'center',
                    marginBottom: '4px',
                    marginTop: '4px'
                });
                var $badge = $('<span>')
                    .css({
                        display: 'inline-block',
                        padding: '2px 8px',
                        borderRadius: '10px',
                        background: '#d0d0d0',
                        fontSize: '10px'
                    })
                    .text(msg.day);
                $dateRow.append($badge);
                $wrap.append($dateRow);
            }

            var isMine      = false;
            var senderLabel = msg.sender_label || msg.sender_id || '';

            if (CHAT_ROLE_ZIMPS == 'Zonal' && msg.sender_type == 'Zonal') {
                isMine = true;
            } else if (CHAT_ROLE_ZIMPS == 'IMPS' && msg.sender_type == 'IMPS') {
                isMine = true;
            } else if (CHAT_ROLE_ZIMPS == 'Master' && msg.sender_type == 'Master') {
                isMine = true;
            }

            var bubbleClass = isMine ? 'zimps-bubble-me' : 'zimps-bubble-them';

            var $bubble = $('<div>').addClass('zimps-bubble ' + bubbleClass);

            var $nameLine = $('<div>')
                .css({ fontWeight: 'bold', fontSize: '11px', marginBottom: '2px' })
                .text(senderLabel);

            var $time = $('<span>')
                .css({ display: 'block', fontSize: '10px', opacity: 0.7, marginTop: '2px' })
                .text(msg.time);

            $bubble.append($nameLine);

            if (msg.message_type == 'image' && msg.image_url) {
                var $link = $('<a>')
                    .attr('href', msg.image_url)
                    .attr('target', '_blank');

                var $img = $('<img>')
                    .attr('src', msg.image_url)
                    .attr('alt', 'Chat Image')
                    .css({
                        maxWidth: '140px',
                        maxHeight: '140px',
                        borderRadius: '4px',
                        display: 'block',
                        marginTop: '3px'
                    });

                $link.append($img);
                $bubble.append($link);

                if (msg.message) {
                    $bubble.append($('<div>').text(msg.message));
                }
            } else {
                $bubble.append($('<div>').text(msg.message));
            }

            $bubble.append($time);
            $wrap.append($bubble);
        });

        // Only scroll once when popup is first opened
        if (forceScrollToBottomOnce && $wrap[0]) {
            $wrap.scrollTop($wrap[0].scrollHeight);
            forceScrollToBottomOnce = false;
        }
    }

    /* ---------- LOAD MESSAGES ---------- */
    function loadZimpsMessages(markSeen) {
        if (!CURRENT_ZONAL_ID || !CURRENT_IMPS_ID) return;
        markSeen = (typeof markSeen == 'undefined') ? false : markSeen;

        $.getJSON('zonal_imps_chat_fetch.php', {
            zonalId: CURRENT_ZONAL_ID,
            imps_id: CURRENT_IMPS_ID
        }, function(res) {
            if (res && res.success) {
                var msgs = res.messages || [];
                renderZimpsMessages(msgs);

                if (markSeen && msgs.length) {
                    var lastMsg = msgs[msgs.length - 1];
                    zimpsLastSeenId = lastMsg.id;

                    $.post('zonal_imps_chat_mark_seen.php', {
                        zonalId: CURRENT_ZONAL_ID,
                        role: CHAT_ROLE_ZIMPS,
                        last_id: zimpsLastSeenId,
                        imps_id: CURRENT_IMPS_ID
                    });
                }
            }
        });
    }

    function startZimpsRefresh() {
        stopZimpsRefresh();
        zimpsRefreshTimer = setInterval(function() {
            loadZimpsMessages(true);
        }, 3000);
    }

    function stopZimpsRefresh() {
        if (zimpsRefreshTimer) {
            clearInterval(zimpsRefreshTimer);
            zimpsRefreshTimer = null;
        }
    }


   /* ---------- PASTE: IMAGE or EXCEL TABLE AS IMAGE (ONLY WHEN EXCEL MODE ON) ---------- */
$('#zimpsMessageInput').on('paste', function (e) {
    var oe = e.originalEvent || e;
    if (!oe || !oe.clipboardData) return;

    var clipboardData = oe.clipboardData;
    var $status = $('#zimpsUploadStatus');

    // 1) Check if clipboard contains an HTML table (Excel / Sheets)
    var html     = clipboardData.getData('text/html') || '';
    var hasTable = html && html.toLowerCase().indexOf('<table') !== -1;

    // CASE A: Excel table present, but Excel mode is OFF
    // → let browser paste as normal text (no preventDefault)
    if (hasTable && !zimpsExcelPasteMode) {
        return;
    }

    // CASE B: Excel table present AND Excel mode ON → convert to image
    if (hasTable && zimpsExcelPasteMode && window.html2canvas) {
        e.preventDefault(); // block raw HTML/text paste

        var $preview = $('#zimpsExcelPastePreview');
        $preview.empty().html(html);

        var tableEl = $preview.find('table').first()[0];
        if (!tableEl) {
            zimpsExcelPasteMode = false;
            $('#zimpsExcelBtn').removeClass('active');
            return;
        }

        $status.css({ color: '#666' }).text('Converting Excel selection to image...');

        html2canvas(tableEl).then(function (canvas) {
            canvas.toBlob(function (blob) {
                if (!blob) {
                    $status.css({ color: '#b30000' }).text('Failed to capture Excel as image');
                    setTimeout(function () { $status.text(''); }, 3000);

                    zimpsExcelPasteMode = false;
                    $('#zimpsExcelBtn').removeClass('active');
                    return;
                }

                blob.name = 'excel_selection_' + Date.now() + '.png';

                zimpsPastedImageFile = blob;
                $('#zimpsImageInput').val('');

                $status.css({ color: '#666' }).text('Excel selection attached as image');
                setTimeout(function () { $status.text(''); }, 3000);

                // one-shot mode: turn OFF excel mode after conversion
                zimpsExcelPasteMode = false;
                $('#zimpsExcelBtn').removeClass('active');
            }, 'image/png', 0.95);
        }).catch(function (err) {
            console.error('html2canvas error:', err);
            $status.css({ color: '#b30000' }).text('Error capturing Excel as image');
            setTimeout(function () { $status.text(''); }, 3000);

            zimpsExcelPasteMode = false;
            $('#zimpsExcelBtn').removeClass('active');
        });

        return;
    }

    // CASE C: No table (or table already handled) → check for direct image (screenshots)
    var items = clipboardData.items || [];
    var foundImage = null;

    for (var i = 0; i < items.length; i++) {
        var it = items[i];
        if (!it.type) continue;
        if (it.type.indexOf('image') === 0) {
            foundImage = it.getAsFile && it.getAsFile();
            if (foundImage) break;
        }
    }

    if (!foundImage && clipboardData.files && clipboardData.files.length) {
        var f = clipboardData.files[0];
        if (f && f.type && f.type.indexOf('image') === 0) {
            foundImage = f;
        }
    }

    if (foundImage) {
        e.preventDefault();

        zimpsPastedImageFile = foundImage;
        $('#zimpsImageInput').val('');  // clear manual file selection

        $status.css({ color: '#666' }).text('Image pasted (will be sent)');
        setTimeout(function () {
            if ($status.text().indexOf('Image pasted') === 0) {
                $status.text('');
            }
        }, 3000);

        // Do NOT modify #zimpsMessageInput value
        return;
    }

    // CASE D: No table, no image → normal text paste
    // (do nothing special)
});

// Excel button: enable Excel → image mode for next paste
$(document).on('click', '#zimpsExcelBtn', function () {
    zimpsExcelPasteMode = true;

    $('#zimpsExcelBtn').addClass('active');

    $('#zimpsUploadStatus')
        .css({ color: '#666' })
        .text('Excel mode ON');

    $('#zimpsMessageInput').focus();
});





    /* ---------- Zonal / IMPS open-popup handlers ---------- */
    // IMPS side button – 1 Zonal ↔ this IMPS
    $(document).on('click', '.btn-zimps-msg', function() {
        var zonalId   = $(this).data('zonal-id');
        var zonalName = $(this).data('zonal-name') || zonalId;

        CURRENT_ZONAL_ID = zonalId;
        CURRENT_IMPS_ID  = IMPS_EMPLOYEE_ID;  // logged-in IMPS

        $('#zimpsZonalId').val(zonalId);
        $('#zimpsImpsId').val(CURRENT_IMPS_ID);

        $('#zimpsChatTitle').text('Zonal: ' + zonalName + ' (' + zonalId + ')');

        forceScrollToBottomOnce = true;
        $('#zimpsChatModal').modal('show');
        loadZimpsMessages(true);
        startZimpsRefresh();

        clearZimpsNotifyBadges();
    });

    // Zonal side button – 1 Zonal ↔ selected IMPS
    $(document).on('click', '.btn-zimps-msg-imps', function () {
        var zonalId   = $(this).data('zonal-id');
        var zonalName = $(this).data('zonal-name') || zonalId;
        var impsId    = $(this).data('imps-id');

        CURRENT_ZONAL_ID = zonalId;
        CURRENT_IMPS_ID  = impsId;

        $('#zimpsZonalId').val(zonalId);
        $('#zimpsImpsId').val(impsId);

        $('#zimpsChatTitle').text('IMPS: ' + $(this).data('imps-agent') + ' (' + impsId + ') ↔ ' + zonalName);

        forceScrollToBottomOnce = true;
        $('#zimpsChatModal').modal('show');
        loadZimpsMessages(true);
        startZimpsRefresh();

        clearZimpsNotifyBadges();
    });

    /* ---------- Image input helper ---------- */
function handleImageSelected() {
    // Explicit file selection overrides any pasted/camera image
    zimpsPastedImageFile = null;

    var curr = $('#zimpsMessageInput').val().trim();
    if (!curr) {
        $('#zimpsMessageInput').val('Image attached');
    }

    $('#zimpsUploadStatus').css({color:'#666'}).text('Image selected');
    setTimeout(function () { $('#zimpsUploadStatus').text(''); }, 1500);
}

$(document).on('change', '#zimpsImageInput', function () {
    if (this.files && this.files.length > 0) {
        handleImageSelected();
    }
});



    /* ---------- SEND HANDLER ---------- */
/* ---------- SEND HANDLER ---------- */
$(document)
  .off('submit', '#zimpsChatSendForm')   // ensure only one handler
  .on('submit', '#zimpsChatSendForm', function(e) {
    e.preventDefault();

    // If already sending, ignore (prevents double insert)
    if (zimpsIsSending) return;

    var msg      = $('#zimpsMessageInput').val().trim();
    var zonalId  = $('#zimpsZonalId').val();
    var impsId   = $('#zimpsImpsId').val();

    // Priority: explicit file picker > camera/pasted blob
    var fileObj = ($('#zimpsImageInput')[0].files[0] || null) || (zimpsPastedImageFile || null);

    if (!zonalId || !impsId || (!msg && !fileObj)) return;

    var fd = new FormData();
    fd.append('zonalId', zonalId);
    fd.append('impsId',  impsId);
    fd.append('message', msg);
    if (fileObj) {
        var fname = fileObj.name || 'chat_image.png';
        fd.append('image', fileObj, fname);
    }

    var $btn    = $('#zimpsSendBtn');
    var $status = $('#zimpsUploadStatus');
    var origTxt = $btn.data('orig-text') || $btn.text();

    zimpsIsSending = true;  // LOCK

    $btn.data('orig-text', origTxt)
        .prop('disabled', true)
        .text('Sending...');

    $status.css({ color: '#666' }).text(fileObj ? 'Uploading...' : 'Sending...');

    $.ajax({
        url: 'zonal_imps_chat_send.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        timeout: 120000,
        success: function(res) {
            if (res && res.success) {
                $('#zimpsMessageInput').val('');
                $('#zimpsImageInput').val('');
                zimpsPastedImageFile = null;
                $status.text('Sent');
                setTimeout(function() { $status.text(''); }, 1500);
                loadZimpsMessages(true);
            } else {
                var err = (res && res.error) ? res.error : 'Unable to send';
                $status.css({ color: '#b30000' }).text('Failed: ' + err);
            }
        },
        error: function() {
            $status.css({ color: '#b30000' }).text('Error sending message');
        },
        complete: function() {
            zimpsIsSending = false;              // UNLOCK
            $btn.prop('disabled', false).text(origTxt);
        }
    });
});


    /* ---------- MODAL CLOSE ---------- */
    $(document).on('hidden.bs.modal', '#zimpsChatModal', function () {
        stopZimpsRefresh();
        CURRENT_ZONAL_ID = null;
        CURRENT_IMPS_ID  = null;
        $('#zimpsChatMessages').empty();
        $('#zimpsZonalId').val('');
        $('#zimpsImpsId').val('');
    });

    /* ---------- UNREAD BADGES ON TABLE BUTTONS ---------- */

    // Set / clear unread badge on a specific button
    function setUnreadBadge($btn, unreadCount) {
        if (!$btn.length) return;

        var $badge = $btn.find('.zimps-unread-badge');

        if (unreadCount && unreadCount > 0) {
            $btn.addClass('btn-msg-new');
            if (!$badge.length) {
                $badge = $('<span class="zimps-unread-badge"></span>').appendTo($btn);
            }
            $badge.text(unreadCount > 99 ? '99+' : unreadCount);
        } else {
            $btn.removeClass('btn-msg-new');
            if ($badge.length) {
                $badge.remove();
            }
        }
    }

    function stopZimpsCamera() {
    if (zimpsCameraStream) {
        zimpsCameraStream.getTracks().forEach(function (t) { t.stop(); });
        zimpsCameraStream = null;
    }
}

function openZimpsCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Camera not supported in this browser');
        return;
    }

    var $overlay = $('#zimpsCameraOverlay');
    var video = document.getElementById('zimpsCameraVideo');

    $overlay.css('display','flex');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function (stream) {
            zimpsCameraStream = stream;
            video.srcObject = stream;
            video.play();
        })
        .catch(function (err) {
            console.error(err);
            alert('Unable to access camera');
            $overlay.hide();
        });
}

// Open camera on button click
$(document).on('click', '#zimpsCameraBtn', function () {
    openZimpsCamera();
});

// Close camera overlay
$(document).on('click', '#zimpsCameraClose', function () {
    stopZimpsCamera();
    $('#zimpsCameraOverlay').hide();
});

// Capture frame -> convert to blob and store as image
$(document).on('click', '#zimpsCameraCapture', function () {
    var video  = document.getElementById('zimpsCameraVideo');
    var canvas = document.getElementById('zimpsCameraCanvas');
    if (!video || !canvas) return;

    var w = video.videoWidth || 640;
    var h = video.videoHeight || 480;
    canvas.width  = w;
    canvas.height = h;

    var ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, w, h);

    canvas.toBlob(function (blob) {
        if (!blob) return;

        // Give the blob a name so PHP sees something like a real file
        blob.name = 'camera_' + Date.now() + '.png';

        zimpsPastedImageFile = blob;  // reuse same variable for sending
        $('#zimpsImageInput').val(''); // clear any file selection

        stopZimpsCamera();
        $('#zimpsCameraOverlay').hide();

        var $status = $('#zimpsUploadStatus');
        $status.css({color:'#666'}).text('Photo captured (will be sent with next message)');
        setTimeout(function () {
            if ($status.text().indexOf('Photo captured') === 0) {
                $status.text('');
            }
        }, 3000);

        if (!$('#zimpsMessageInput').val().trim()) {
            $('#zimpsMessageInput').val('Image attached');
        }
    }, 'image/png');
});


    // IMPS / Master side: one badge per Zonal row
    function updateZonalButtonUnread(zonalId, unreadCount) {
        var $btn = $('.btn-zimps-msg[data-zonal-id="' + zonalId + '"]');
        setUnreadBadge($btn, unreadCount);
    }

    // Clear all badges
    function clearZimpsNotifyBadges() {
        $('.btn-zimps-msg-imps, .btn-zimps-msg').each(function () {
            setUnreadBadge($(this), 0);
        });
    }

    // Polling for Zonal side (SubZonal) – per IMPS agent
    function startZonalUnreadChecker() {
        if (CHAT_ROLE_ZIMPS !== 'Zonal' || !ZONAL_ROOM_ID) return;

        setInterval(function () {
            $('.btn-zimps-msg-imps').each(function () {
                var $btn   = $(this);
                var impsId = $btn.data('imps-id');   // employeeId of IMPS user
                if (!impsId) return;

                $.getJSON('zonal_imps_chat_latest.php', {
                    zonalId: ZONAL_ROOM_ID,
                    role: 'Zonal',
                    imps_id: impsId
                }, function (res) {
                    if (res && res.success) {
                        var unread = parseInt(res.unread_count || 0, 10);
                        if (isNaN(unread)) unread = 0;
                        setUnreadBadge($btn, unread);
                    }
                });
            });
        }, 5000);
    }

    // Polling for IMPS / Master side
    function startImpsUnreadChecker() {
        if (CHAT_ROLE_ZIMPS !== 'IMPS' && CHAT_ROLE_ZIMPS !== 'Master') return;

        var zonalIds = [];
        $('.btn-zimps-msg').each(function () {
            var id = $(this).data('zonal-id');
            if (id) zonalIds.push(id);
        });
        if (!zonalIds.length) return;

        setInterval(function () {
            zonalIds.forEach(function (zId) {
                $.getJSON('zonal_imps_chat_latest.php', {
                    zonalId: zId,
                    role: CHAT_ROLE_ZIMPS
                    // imps_id not needed; server will derive from session for IMPS
                }, function (res) {
                    if (res && res.success) {
                        var unread = parseInt(res.unread_count || 0, 10);
                        if (isNaN(unread)) unread = 0;
                        updateZonalButtonUnread(zId, unread);
                    }
                });
            });
        }, 5000);
    }

    $(function () {
        startZonalUnreadChecker();
        startImpsUnreadChecker();
    });

})(jQuery);
</script>

