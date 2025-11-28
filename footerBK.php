<style>
    /* Base bubble (normal state) */
    #branchChatFloat {
        position: fixed;
        bottom: 70px;
        right: 20px;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #123C69; /* normal blue */
        color: #fff;
        text-align: center;
        line-height: 45px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        cursor: pointer;
        z-index: 9999;
        font-size: 20px;
        transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
    }

    .chat-messages {
        max-height: 380px;     /* was 320px */
    }

    /* Chat modal: bigger + fixed bottom-right */
    .chat-modal .modal-dialog {
        width: 432px;          /* 360px + 20% */
        max-width: 95%;
        position: fixed;
        right: 0;
        bottom: 0;
        top: auto;
        left: auto;
        margin: 0;             /* no center margin */
    }


    .chat-sender {
        font-weight: 600;
        font-size: 11px;
        margin-bottom: 2px;
        display: block;
    }

    /* Glowing notification state – branch floating bubble */
    #branchChatFloat.chat-notify {
        background-color: #e60023; /* RED */
        box-shadow: 0 0 10px rgba(230,0,35,0.9),
                    0 0 20px rgba(230,0,35,0.7),
                    0 0 30px rgba(230,0,35,0.5);
        animation: chatGlow 1s ease-in-out infinite alternate;
        transform: translateY(-2px);
    }

    @keyframes chatGlow {
        0% {
            box-shadow: 0 0 10px rgba(230,0,35,0.7),
                        0 0 18px rgba(230,0,35,0.5);
        }
        100% {
            box-shadow: 0 0 16px rgba(230,0,35,1),
                        0 0 28px rgba(230,0,35,0.9);
        }
    }

    /* HO side: glowing message button in tables */
    .btn-msg-branch.btn-msg-new {
        background-color: #e60023;
        border-color: #e60023;
        color: #fff;
        box-shadow: 0 0 10px rgba(230,0,35,0.8),
                    0 0 20px rgba(230,0,35,0.6);
        animation: chatGlowBtn 1s ease-in-out infinite alternate;
    }

    @keyframes chatGlowBtn {
        0% {
            box-shadow: 0 0 10px rgba(230,0,35,0.6),
                        0 0 16px rgba(230,0,35,0.4);
        }
        100% {
            box-shadow: 0 0 16px rgba(230,0,35,1),
                        0 0 26px rgba(230,0,35,0.9);
        }
    }

    /* Chat bubbles */
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
    .chat-img-thumb {
        max-width: 120px;
        max-height: 120px;
        border-radius: 4px;
        cursor: pointer;
        display: block;
        margin-top: 3px;
    }

    /* Attach button */
    .chat-image-btn {
        padding: 0;
        width: 36px;
        height: 34px;
        border-radius: 4px;
        border: 1px solid #ccc;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chat-image-btn i {
        font-size: 16px;
    }

    .chat-upload-status {
        font-size: 11px;
        color: #666;
        margin-left: 5px;
        white-space: nowrap;
    }
    .chat-upload-status-error {
        color: #b30000;
        font-weight: 600;
    }

    /* Image editor modal */
    #chatEditCanvas {
        border: 1px solid #ccc;
        max-width: 100%;
        cursor: crosshair;
    }
    .chat-color-swatch {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #ccc;
        padding: 0;
        margin: 0 3px;
    }
    .chat-color-swatch.active {
        box-shadow: 0 0 0 2px #123C69;
    }
    .chat-image-actions {
        margin-top: 4px;
        text-align: right;
    }
    .chat-image-actions .btn-xs {
        padding: 1px 5px;
        font-size: 10px;
    }

    /* Small unread badge on the branch floating bubble */
    #branchChatFloat .chat-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        min-width: 16px;
        height: 16px;
        padding: 0 3px;
        border-radius: 8px;
        background: #ff0000;
        color: #fff;
        font-size: 10px;
        line-height: 16px;
        text-align: center;
        font-weight: 600;
    }
    
    /* Make sure badge is positioned correctly on HO buttons */
    .btn-msg-branch {
        position: relative;
    }

    /* Small date pill above each message */
    .chat-date-pill {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 10px;
        background: #e0e0e0;
        font-size: 10px;
        color: #333;
        margin-bottom: 2px;
    }
    
    /* Unread count badge on HO "Message" buttons */
    .btn-msg-branch .msg-unread-badge {
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

    /* Scroll-to-bottom button inside chat window */
.chat-scroll-bottom-btn {
    position: absolute;
    right: 12px;
    bottom: 52px;      /* sitting just above footer input area */
    z-index: 10;
    display: none;     /* hidden until user scrolls up */
    border-radius: 50%;
    padding: 4px 7px;
    font-size: 12px;
    line-height: 1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.25);
}

    
    /* Date bubble at top of chat box */
.chat-date-badge {
    position: sticky;
    top: 0;
    z-index: 5;
    display: inline-block;
    margin: 0 auto 6px auto;
    padding: 3px 10px;
    border-radius: 12px;
    background: rgba(18, 60, 105, 0.85); /* semi-transparent blue */
    color: #fff;
    font-size: 11px;
    text-transform: uppercase;
    text-align: center;
}

/* Container to center the date badge */
.chat-date-row {
    text-align: center;
}

</style>

<footer class="footer">
    <b>
        <span style="color:#990000" class="pull-right">
            ISO 9001:2015 Certified Company
        </span>
        <span style="color:#990000" class="pull-left">
            Attica Gold Pvt Ltd
        </span>
    </b>
</footer>
</div>

<!-- Global Branch Chat Modal (used by HO & Branch) -->
<div class="modal fade chat-modal" id="branchChatModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="padding:8px 15px;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" style="font-size: 20px">
                    <i class="fa fa-comments" style="color:#8B2030;"></i>
                    <span id="chatBranchTitle"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding:10px; position:relative;">
    <div class="chat-messages" id="chatMessages"
         style="max-height:550px;overflow-y:auto;padding:8px;background:#f5f5f5;border-radius:4px;">
    </div>

    <!-- NEW scroll-to-bottom button -->
    <button type="button" id="chatScrollBottomBtn"
            class="btn btn-default btn-xs chat-scroll-bottom-btn"
            title="Scroll to latest">
        <i class="fa fa-arrow-down"></i>
    </button>
</div>

            <div class="modal-footer" style="padding:8px 10px;">
                <form id="chatSendForm" enctype="multipart/form-data"
                      style="width:100%; display:flex; gap:5px; align-items:center;">

                    <input type="hidden" id="chatBranchId" name="branchId">

                    <!-- Hidden file input + custom image button + camera button -->
                    <input type="file" id="chatImageInput" name="image" accept="image/*" style="display:none;">

                    <label for="chatImageInput"
                        class="btn btn-default chat-image-btn"
                        style="margin-bottom:0;">
                        <i class="fa fa-file-image-o"></i>
                    </label>

                    <button type="button"
                            id="chatCameraBtn"
                            class="btn btn-default chat-image-btn"
                            style="margin-bottom:0;">
                        <i class="fa fa-camera"></i>
                    </button>

                    <button type="button"
                            id="chatExcelBtn"
                            class="btn btn-default chat-image-btn"
                            style="margin-bottom:0;"
                            title="Paste Excel as image">
                        <i class="fa fa-table"></i>
                    </button>

                    <input type="text" id="chatMessageInput" class="form-control"
                           placeholder="Type a message..." autocomplete="off">

                    <button type="submit" class="btn btn-success" id="chatSendBtn">Send</button>

                    <!-- upload status -->
                    <span id="chatUploadStatus" class="chat-upload-status"></span>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image EDITOR modal (draw + send reply) -->
<div class="modal fade" id="chatImageEditorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width:420px;max-width:95%;">
        <div class="modal-content">
            <div class="modal-header" style="padding:8px 15px;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-pencil" style="color:#8B2030;"></i>
                    Edit Image &amp; Reply
                </h4>
            </div>
            <div class="modal-body" style="padding:10px;">
    <div class="text-center">
        <canvas id="chatEditCanvas" width="360" height="260"></canvas>
    </div>
    <div class="text-center" style="margin-top:8px;">
        <span style="font-size:11px;margin-right:6px;">Color:</span>
        <button type="button" class="btn btn-xs chat-color-swatch active"
                data-color="#ff0000" style="background:#ff0000;"></button>
        <button type="button" class="btn btn-xs chat-color-swatch"
                data-color="#00a000" style="background:#00a000;"></button>
        <button type="button" class="btn btn-xs chat-color-swatch"
                data-color="#0000ff" style="background:#0000ff;"></button>
        <button type="button" class="btn btn-xs chat-color-swatch"
                data-color="#ffff00" style="background:#ffff00;"></button>
        <button type="button" class="btn btn-xs chat-color-swatch"
                data-color="#000000" style="background:#000000;"></button>
        <button type="button" class="btn btn-xs chat-color-swatch"
                data-color="#ffffff" style="background:#ffffff;"></button>
    </div>

    <!-- NEW: text field inside editor -->
    <div class="form-group" style="margin-top:8px;">
        <label style="font-size:11px;">Message (optional):</label>
        <textarea id="chatEditMessage" class="form-control" rows="2"
                  placeholder="Type your reply text here..."></textarea>
    </div>
</div>
<!-- Camera capture modal -->
<div class="modal fade" id="chatCameraModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width:420px;max-width:95%;">
        <div class="modal-content">
            <div class="modal-header" style="padding:8px 15px;">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-camera" style="color:#8B2030;"></i>
                    Take Photo
                </h4>
            </div>
            <div class="modal-body" style="padding:10px;">
                <div class="text-center">
                    <video id="chatCameraVideo"
                           autoplay
                           playsinline
                           style="max-width:100%;border-radius:4px;background:#000;"></video>
                    <canvas id="chatCameraCanvas" style="display:none;"></canvas>
                </div>
                <p style="font-size:11px;margin-top:5px;">
                    Allow camera access, then click <b>Capture</b> to take a photo.
                </p>
            </div>
            <div class="modal-footer" style="padding:8px 10px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="chatCameraCaptureBtn">Capture</button>
            </div>
        </div>
    </div>
</div>

            <div class="modal-footer" style="padding:8px 10px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="chatEditSendBtn">Send Reply</button>
            </div>
        </div>
    </div>
</div>

<!-- Vendor scripts (unchanged) -->
<script src="vendor/jquery/dist/jquery.min.js"></script>
<script src="vendor/jquery-ui/jquery-ui.min.js"></script>
<script src="vendor/slimScroll/jquery.slimscroll.min.js"></script>
<script src="vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="vendor/metisMenu/dist/metisMenu.min.js"></script>
<script src="vendor/iCheck/icheck.min.js"></script>
<script src="vendor/sparkline/index.js"></script>
<script src="vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="vendor/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="vendor/pdfmake/build/pdfmake.min.js"></script>
<script src="vendor/pdfmake/build/vfs_fonts.js"></script>
<script src="vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="vendor/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="vendor/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
<script src="vendor/bootstrap-datepicker-master/dist/js/bootstrap-datepicker.min.js"></script>
<script src="scripts/homer.js"></script>
<script src="vendor/summernote/dist/summernote.min.js"></script>
<script src="vendor/inputmask/js/jquery.inputmask.bundle.js"></script>
<script src="vendor/select2-3.5.2/select2.min.js"></script>
<script src="AGPL-script.js"></script>
<script src="scripts/attic.js" type="text/javascript"></script>
<script src="scripts/demo.js" type="text/javascript"></script>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$type         = $_SESSION['usertype']      ?? '';
$BRANCH_CODE  = $_SESSION['branchCode']    ?? '';   // branchId stored here
$username     = $_SESSION['login_username']?? '';

// Chat role: Branch vs Center
$CHAT_ROLE = ($type === 'Branch') ? 'Branch' : 'Center';

// Sender label
$CHAT_SENDER_LABEL = '';
if ($type === 'Branch') {
    $CHAT_SENDER_LABEL = $BRANCH_CODE ?: 'Branch';
} elseif ($type === 'Master') {
    $CHAT_SENDER_LABEL = 'Master';
} else {
    $CHAT_SENDER_LABEL = $username ?: 'Center';

    if (!empty($username) && isset($con)) {
        $uEsc = mysqli_real_escape_string($con, $username);
        $res  = mysqli_query($con, "SELECT name FROM employee WHERE empId='$uEsc' LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if (!empty($row['name'])) {
                $CHAT_SENDER_LABEL = $row['name'];
            }
        }
    }
}
?>

<!-- html2canvas to capture pasted Excel table as image -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Hidden container to render pasted Excel HTML for capture -->
<div id="chatExcelPastePreview"
     style="position:fixed; left:-99999px; top:-99999px; opacity:0; pointer-events:none;"></div>

<script type="text/javascript">
(function ($) {
    // From PHP
    var CHAT_ROLE         = '<?php echo $CHAT_ROLE; ?>';             // 'Center' or 'Branch'
    var DEFAULT_BRANCH_ID = '<?php echo $BRANCH_CODE; ?>';           // BranchId from $_SESSION['branchCode']
    var CHAT_SENDER_LABEL = '<?php echo addslashes($CHAT_SENDER_LABEL); ?>';
    // NEW: excel paste mode flag
    var excelPasteMode = false;

    var chatBranchId      = null;
    var chatRefreshTimer  = null;

    // Branch floating bubble timer (Branch login)
    var branchBubbleTimer = null;

    // Branch side: last seen id of any message
    var lastSeenId        = 0;

    // HO side: last seen id per branch (for table "Message" buttons)
    var branchLastSeen    = {};

    // For edited-image reply
        // For edited-image reply
    var editedImageBlob   = null;
    var replyContext      = null;   // {senderLabel: '...', messageId: n}

    // NEW: image sources
    var pastedImageBlob   = null;   // image from clipboard paste
    var cameraImageBlob   = null;   // image from camera capture

    // NEW: camera stream handle
    var cameraStream      = null;

    // Autoscroll control
    var forceScrollToBottomOnce = false;
    var userIsNearBottom        = true;

    /* ---------- helpers: update badges ---------- */

    // Branch floating bubble badge + glow
    function updateBranchBubbleUnread(unreadCount) {
        var $float = $('#branchChatFloat');
        if (!$float.length) return;

        if (unreadCount && unreadCount > 0) {
            $float.addClass('chat-notify');
            var $badge = $float.find('.chat-badge');
            if (!$badge.length) {
                $badge = $('<span class="chat-badge"></span>').appendTo($float);
            }
            $badge.text(unreadCount > 99 ? '99+' : unreadCount);
        } else {
            $float.removeClass('chat-notify');
            $float.find('.chat-badge').remove();
        }
    }

    // HO table: badge + glow on "Message" button
    function updateButtonUnread(branchId, unreadCount) {
        var $btn = $('.btn-msg-branch[data-branch-id="' + branchId + '"]');
        if (!$btn.length) return;

        var $badge = $btn.find('.msg-unread-badge');

        if (unreadCount && unreadCount > 0) {
            $btn.addClass('btn-msg-new');
            if (!$badge.length) {
                $badge = $('<span class="msg-unread-badge"></span>').appendTo($btn);
            }
            $badge.text(unreadCount > 99 ? '99+' : unreadCount);
        } else {
            $btn.removeClass('btn-msg-new');
            if ($badge.length) {
                $badge.remove();
            }
        }
    }

    /* ---------- RENDER MESSAGES ---------- */
       function renderChatMessages(messages) {
    var $wrap = $('#chatMessages');
    if (!$wrap.length) return;

    $wrap.empty();

    var lastDay = null;

    messages.forEach(function (msg) {
        // Insert date bubble when day changes
        if (msg.day && msg.day !== lastDay) {
            lastDay = msg.day;

            var $dateRow = $('<div>').addClass('chat-date-row');
            var $dateBadge = $('<span>')
                .addClass('chat-date-badge')
                .text(msg.day);

            $dateRow.append($dateBadge);
            $wrap.append($dateRow);
        }

        var isMine      = (msg.sender_type === CHAT_ROLE);
        var bubbleClass = isMine ? 'chat-bubble-me' : 'chat-bubble-them';

        var senderLabel = msg.sender_label || msg.sender_id || '';
        if (CHAT_ROLE === 'Branch' && !isMine) {
            senderLabel = 'ATTICA-HO';
        }

        var $bubble = $('<div>').addClass('chat-bubble ' + bubbleClass);

        var $nameLine = $('<div>')
            .css({ 'font-weight': 'bold', 'font-size': '11px', 'margin-bottom': '2px' })
            .text(senderLabel);

        var $time = $('<span>').addClass('chat-time').text(msg.time);

        $bubble.append($nameLine);

        if (msg.message_type === 'image' && msg.image_url) {
            var $link = $('<a>')
                .attr('href', msg.image_url)
                .attr('target', '_blank');

            var $img = $('<img>')
                .addClass('chat-img-thumb')
                .attr('src', msg.image_url)
                .attr('alt', 'Chat Image');

            $link.append($img);
            $bubble.append($link);

            if (msg.message) {
                $bubble.append($('<div>').text(msg.message));
            }

            var $actions = $('<div>').addClass('chat-image-actions');
            var $btnReply = $('<button type="button" class="btn btn-xs btn-default chat-reply-btn">')
                .text('Reply')
                .attr('data-sender-label', senderLabel)
                .attr('data-message-id', msg.id)
                .attr('data-image-url', msg.image_url);

            var $btnEdit = $('<button type="button" class="btn btn-xs btn-primary chat-edit-btn" style="margin-left:4px;">')
                .text('Edit')
                .attr('data-sender-label', senderLabel)
                .attr('data-message-id', msg.id)
                .attr('data-image-url', msg.image_url);

            $actions.append($btnReply).append($btnEdit);
            $bubble.append($actions);
        } else {
            $bubble.append($('<div>').text(msg.message));
        }

        $bubble.append($time);
        $wrap.append($bubble);
    });

    // --- ONLY scroll once, when popup is freshly opened ---
    if (forceScrollToBottomOnce) {
        $wrap.scrollTop($wrap[0].scrollHeight);
        forceScrollToBottomOnce = false;
    }

    // Update bottom button visibility
    updateScrollBottomButton();
}


    function clearNotifyBubble() {
        updateBranchBubbleUnread(0);
    }

    function updateScrollBottomButton() {
    var $wrap = $('#chatMessages');
    var $btn  = $('#chatScrollBottomBtn');
    if (!$wrap.length || !$btn.length) return;

    var scrollTop    = $wrap.scrollTop();
    var visible      = $wrap.innerHeight();
    var scrollHeight = $wrap[0].scrollHeight;

    // if near bottom, hide; if user scrolled up, show
    if (scrollHeight - (scrollTop + visible) < 40) {
        $btn.hide();
    } else {
        $btn.show();
    }
}

    /* track user scroll position to disable auto-scroll when they scroll up */
$(document).on('scroll', '#chatMessages', function () {
    updateScrollBottomButton();
});

$(document).on('click', '#chatScrollBottomBtn', function () {
    var $wrap = $('#chatMessages');
    if (!$wrap.length) return;

    $wrap.scrollTop($wrap[0].scrollHeight);
    updateScrollBottomButton();
});

    /* ---------- LOAD MESSAGES (modal) ---------- */
    function loadChatMessages(markSeen) {
        if (!chatBranchId) return;
        markSeen = (typeof markSeen === 'undefined') ? false : markSeen;

        $.getJSON('branch_chat_fetch.php', {branchId: chatBranchId}, function (res) {
            if (res && res.success) {
                var msgs = res.messages || [];
                renderChatMessages(msgs);

                if (markSeen && msgs.length) {
                    var lastMsg = msgs[msgs.length - 1];
                    lastSeenId  = lastMsg.id;

                    // Tell server that up to lastSeenId is seen for this role
                    $.post('branch_chat_mark_seen.php', {
                        branchId: chatBranchId,
                        role: CHAT_ROLE,
                        last_id: lastSeenId
                    });

                    // HO UI: clear glow/badge for this branch
                    if (CHAT_ROLE === 'Center' && chatBranchId) {
                        branchLastSeen[chatBranchId] = lastMsg.id; // optional now
                        updateButtonUnread(chatBranchId, 0);
                    }

                    clearNotifyBubble();
                }

            }
        });
    }

    function startChatRefresh() {
        stopChatRefresh();
        chatRefreshTimer = setInterval(function () {
            loadChatMessages(true);  // while modal open, mark as seen
        }, 3000);
    }

    function stopChatRefresh() {
        if (chatRefreshTimer) {
            clearInterval(chatRefreshTimer);
            chatRefreshTimer = null;
        }
    }

    /* ---------- BACKGROUND CHECKER FOR BRANCH FLOAT BUBBLE (with unread count) ---------- */
    function startBranchBubbleChecker() {
        if (!DEFAULT_BRANCH_ID) return; // Branch side only

        stopBranchBubbleChecker();

        branchBubbleTimer = setInterval(function () {
    $.getJSON('branch_chat_latest.php', {
        branchId: DEFAULT_BRANCH_ID,
        role: 'Branch'
    }, function (res) {
        if (res && res.success) {
            var unread = parseInt(res.unread_count || 0, 10);
            if (isNaN(unread)) unread = 0;

            var modalOpen = $('#branchChatModal').hasClass('in');
            if (!modalOpen) {
                updateBranchBubbleUnread(unread);
            } else if (unread === 0) {
                updateBranchBubbleUnread(0);
            }
        }
    });
}, 5000);

    }

    function stopBranchBubbleChecker() {
        if (branchBubbleTimer) {
            clearInterval(branchBubbleTimer);
            branchBubbleTimer = null;
        }
    }

    /* ---------- OPEN CHAT FROM TABLE (HO side) ---------- */
    $(document).on('click', '.btn-msg-branch', function () {
        var $btn       = $(this);
        var branchId   = $btn.data('branch-id');
        var branchName = $btn.data('branch-name') || branchId;

        if (!$('#branchChatModal').length) return;

        chatBranchId = branchId;
        $('#chatBranchId').val(branchId);
        $('#chatBranchTitle').text(branchName + ' (' + branchId + ')');

        forceScrollToBottomOnce = true;
        $('#branchChatModal').modal('show');
        loadChatMessages(true);
        startChatRefresh();
    });

        /* ---------- FILE SELECTED → set "Image attached" text ---------- */
    $(document).on('change', '#chatImageInput', function () {
        if (this.files && this.files.length > 0) {
            // Clear other image sources so we don't send multiple
            pastedImageBlob = null;
            cameraImageBlob = null;
            editedImageBlob = null;

            var curr = $('#chatMessageInput').val().trim();
            if (!curr) {
                $('#chatMessageInput').val('Image attached');
            }

            $('#chatUploadStatus')
                .removeClass('chat-upload-status-error')
                .text('Image selected');
        }
    });

        /* ---------- PASTE: IMAGE or EXCEL TABLE AS IMAGE (ONLY WHEN EXCEL MODE ON) ---------- */
    $(document).on('paste', '#chatMessageInput', function (e) {
        var ev = e.originalEvent || e;
        if (!ev || !ev.clipboardData) return;

        var clipboardData = ev.clipboardData;
        var $status = $('#chatUploadStatus');

        // 1) Check if clipboard contains an HTML table (Excel / Sheets)
        var html     = clipboardData.getData('text/html') || '';
        var hasTable = html && html.toLowerCase().indexOf('<table') !== -1;

        // 👉 CASE A: Excel table present, but Excel mode is OFF
        //    → Do NOTHING. Let the browser paste as plain text in the input.
        if (hasTable && !excelPasteMode) {
            return; // no preventDefault → normal paste as text
        }

        // 👉 CASE B: Excel table present AND Excel mode ON
        //    → Convert table to image using html2canvas
        if (hasTable && excelPasteMode && window.html2canvas) {
            e.preventDefault(); // stop raw HTML/text paste

            var $preview = $('#chatExcelPastePreview');
            $preview.empty().html(html);

            var tableEl = $preview.find('table').first()[0];
            if (!tableEl) {
                // Nothing valid, just fall back and reset mode
                excelPasteMode = false;
                $('#chatExcelBtn').removeClass('active');
                return;
            }

            $status
                .removeClass('chat-upload-status-error')
                .css({ color: '#666' })
                .text('Converting Excel selection to image...');

            html2canvas(tableEl).then(function (canvas) {
                canvas.toBlob(function (blob) {
                    if (!blob) {
                        $status
                            .addClass('chat-upload-status-error')
                            .css({ color: '#b30000' })
                            .text('Failed to capture Excel as image');
                        setTimeout(function () { $status.text(''); }, 3000);

                        excelPasteMode = false;
                        $('#chatExcelBtn').removeClass('active');
                        return;
                    }

                    blob.name = 'excel_selection_' + Date.now() + '.png';

                    pastedImageBlob = blob;
                    cameraImageBlob = null;
                    editedImageBlob = null;
                    $('#chatImageInput').val('');

                    $status
                        .removeClass('chat-upload-status-error')
                        .css({ color: '#666' })
                        .text('Excel selection attached as image');
                    setTimeout(function () { $status.text(''); }, 3000);

                    // do NOT touch #chatMessageInput value → can send as image-only
                    excelPasteMode = false;
                    $('#chatExcelBtn').removeClass('active');
                }, 'image/png', 0.95);
            }).catch(function (err) {
                console.error('html2canvas error:', err);
                $status
                    .addClass('chat-upload-status-error')
                    .css({ color: '#b30000' })
                    .text('Error capturing Excel as image');
                setTimeout(function () { $status.text(''); }, 3000);

                excelPasteMode = false;
                $('#chatExcelBtn').removeClass('active');
            });

            return;
        }

        // 👉 CASE C: No table case → handle direct images (screenshots, etc.)
        var items = clipboardData.items || [];
        var foundImage = null;

        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (!item.type) continue;
            if (item.kind === 'file' && item.type.indexOf('image/') === 0) {
                foundImage = item.getAsFile && item.getAsFile();
                if (foundImage) break;
            }
        }

        if (!foundImage && clipboardData.files && clipboardData.files.length) {
            var f = clipboardData.files[0];
            if (f && f.type && f.type.indexOf('image/') === 0) {
                foundImage = f;
            }
        }

        if (foundImage) {
            e.preventDefault();

            pastedImageBlob = foundImage;
            cameraImageBlob = null;
            editedImageBlob = null;
            $('#chatImageInput').val('');

            $status
                .removeClass('chat-upload-status-error')
                .css({ color: '#666' })
                .text('Image pasted (will be sent)');
            setTimeout(function () {
                if ($status.text().indexOf('Image pasted') === 0) {
                    $status.text('');
                }
            }, 3000);

            // Do NOT modify #chatMessageInput value
            return;
        }

        // 👉 CASE D: No table, no image → normal text paste
        // Do nothing special; browser will paste text into input.
    });


$(document).on('click', '#chatExcelBtn', function () {
    excelPasteMode = true;

    $('#chatExcelBtn').addClass('active');

    $('#chatUploadStatus')
        .removeClass('chat-upload-status-error')
        .css({ color: '#666' })
        .text('Excel mode ON: copy cells in Excel and press Ctrl+V here');

    $('#chatMessageInput').focus();
});


    /* ---------- CENTRAL SEND HANDLER (HO / Branch, text + image OR edited image) ---------- */
    $(document).on('submit', '#chatSendForm', function (e) {
        e.preventDefault();

        var msg      = $('#chatMessageInput').val().trim();
        var branchId = $('#chatBranchId').val();

        var rawInputFile = $('#chatImageInput')[0].files[0] || null;
        var fileObj = null;

        // Priority: edited > camera > pasted > file input
        if (editedImageBlob) {
            fileObj = editedImageBlob;
        } else if (cameraImageBlob) {
            fileObj = cameraImageBlob;
        } else if (pastedImageBlob) {
            fileObj = pastedImageBlob;
        } else if (rawInputFile) {
            fileObj = rawInputFile;
        }


        if (!branchId || (!msg && !fileObj)) return;

        var fd = new FormData();
        fd.append('branchId', branchId);
        fd.append('message', msg);

        if (fileObj) {
            var fname = (fileObj.name && /\S/.test(fileObj.name)) ? fileObj.name : 'chat_image.png';
            if (!/\.(jpe?g|png|gif|webp)$/i.test(fname)) {
                fname += '.png';
            }
            fd.append('image', fileObj, fname);
        }

        var $sendBtn  = $('#chatSendBtn');
        var $statusEl = $('#chatUploadStatus');

        var originalText = $sendBtn.data('orig-text') || $sendBtn.text();
        $sendBtn
            .data('orig-text', originalText)
            .prop('disabled', true)
            .text('Uploading...');

        $statusEl
            .removeClass('chat-upload-status-error')
            .text(fileObj ? 'Uploading image 0%' : 'Sending...');

        $.ajax({
            url: 'branch_chat_send.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 120000,

            xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload && fileObj) {
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            var percent = Math.round((e.loaded / e.total) * 100);
                            $statusEl.text('Uploading image ' + percent + '%');
                        }
                    }, false);
                }
                return xhr;
            },

            success: function (res) {
                if (res && res.success) {
                    $('#chatMessageInput').val('');
                    $('#chatImageInput').val('');

                    // Clear all in-memory image sources
                    editedImageBlob = null;
                    pastedImageBlob = null;
                    cameraImageBlob = null;

                    $statusEl.text('Sent').removeClass('chat-upload-status-error');
                    setTimeout(function () { $statusEl.text(''); }, 2000);

                    loadChatMessages(true);

                } else {
                    var err = (res && res.error) ? res.error : 'Unable to send message';
                    $statusEl
                        .addClass('chat-upload-status-error')
                        .text('Failed: ' + err);
                }
            },

            error: function (xhr, textStatus) {
                var msg = 'Upload failed';
                if (textStatus === 'timeout') {
                    msg = 'Upload timeout, please try again';
                }
                $statusEl
                    .addClass('chat-upload-status-error')
                    .text(msg);
            },

            complete: function () {
                var orig = $sendBtn.data('orig-text') || 'Send';
                $sendBtn.prop('disabled', false).text(orig);
            }
        });
    });

    /* ---------- Reply button: image + optional text inside editor ---------- */
    $(document).on('click', '.chat-reply-btn', function () {
        var senderLabel = $(this).data('sender-label') || '';
        var imageUrl    = $(this).data('image-url') || '';
        var msgId       = $(this).data('message-id') || null;

        replyContext = {
            senderLabel: senderLabel,
            messageId:   msgId
        };

        if (imageUrl) {
            // For image reply, open editor and prefill the editor text box
            openImageEditor(imageUrl, replyContext);
        } else {
            // For pure text messages, keep old behaviour
            var baseText = 'Reply to ' + senderLabel + ': ';
            var curr = $('#chatMessageInput').val();
            if (!curr || curr.indexOf('Reply to ') === 0) {
                $('#chatMessageInput').val(baseText);
            }
            $('#chatMessageInput').focus();
        }
    });

        /* ---------- CAMERA CAPTURE HELPERS ---------- */
    function stopCameraStream() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (t) { t.stop(); });
            cameraStream = null;
        }
    }

    // Open camera when clicking the camera button
    $(document).on('click', '#chatCameraBtn', function () {
        var video = document.getElementById('chatCameraVideo');
        if (!video) return;

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Camera is not supported in this browser.');
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function (stream) {
                cameraStream = stream;
                video.srcObject = stream;
                $('#chatCameraModal').modal('show');
            })
            .catch(function (err) {
                console.error('getUserMedia error:', err);
                alert('Unable to access camera. Please check permissions.');
            });
    });

    // Capture frame from video and attach as image
    $(document).on('click', '#chatCameraCaptureBtn', function () {
        var video  = document.getElementById('chatCameraVideo');
        var canvas = document.getElementById('chatCameraCanvas');
        if (!video || !canvas) return;

        var w = video.videoWidth || 640;
        var h = video.videoHeight || 480;
        if (!w || !h) {
            alert('Camera is not ready yet. Please try again.');
            return;
        }

        canvas.width  = w;
        canvas.height = h;

        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, w, h);

        canvas.toBlob(function (blob) {
            if (!blob) return;

            cameraImageBlob = blob;
            cameraImageBlob.name = 'camera_photo.png';

            // Clear other image sources
            pastedImageBlob = null;
            editedImageBlob = null;
            $('#chatImageInput').val('');

            var curr = $('#chatMessageInput').val().trim();
            if (!curr) {
                $('#chatMessageInput').val('Photo captured');
            }

            $('#chatUploadStatus')
                .removeClass('chat-upload-status-error')
                .text('Camera photo ready to send');

            $('#chatCameraModal').modal('hide');
        }, 'image/png', 0.92);
    });

    // When camera modal closes, stop stream
    $(document).on('hidden.bs.modal', '#chatCameraModal', function () {
        stopCameraStream();
    });


    /* ---------- Image EDITOR (draw over received image) ---------- */
    var editCanvas   = null;
    var editCtx      = null;
    var isDrawing    = false;
    var currentColor = '#ff0000';
    var baseImage    = null;

    function getCanvasPos(e) {
        var rect = editCanvas.getBoundingClientRect();
        var clientX, clientY;
        if (e.touches && e.touches.length) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDraw(e) {
        if (!editCanvas || !editCtx) return;
        isDrawing = true;
        var pos = getCanvasPos(e);
        editCtx.beginPath();
        editCtx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!isDrawing || !editCanvas || !editCtx) return;
        var pos = getCanvasPos(e);
        editCtx.lineTo(pos.x, pos.y);
        editCtx.strokeStyle = currentColor;
        editCtx.lineWidth = 3;
        editCtx.lineCap = 'round';
        editCtx.stroke();
        e.preventDefault();
    }

    function endDraw(e) {
        if (!editCanvas || !editCtx) return;
        isDrawing = false;
        editCtx.closePath();
        if (e) e.preventDefault();
    }

    function initEditorCanvas() {
        if (editCanvas) return; // already init
        editCanvas = document.getElementById('chatEditCanvas');
        if (!editCanvas) return;
        editCtx = editCanvas.getContext('2d');

        editCanvas.addEventListener('mousedown', startDraw);
        editCanvas.addEventListener('mousemove', draw);
        editCanvas.addEventListener('mouseup', endDraw);
        editCanvas.addEventListener('mouseleave', endDraw);

        editCanvas.addEventListener('touchstart', startDraw, {passive:false});
        editCanvas.addEventListener('touchmove', draw, {passive:false});
        editCanvas.addEventListener('touchend', endDraw, {passive:false});
        editCanvas.addEventListener('touchcancel', endDraw, {passive:false});
    }

        function openImageEditor(imageUrl, context) {
        initEditorCanvas();
        if (!editCanvas || !editCtx) return;

        replyContext    = context || null;
        editedImageBlob = null;

        // Reset & prefill the editor message box
        var $editMsg = $('#chatEditMessage');
        if ($editMsg.length) {
            $editMsg.val('');
            if (replyContext && replyContext.senderLabel) {
                $editMsg.val('Reply to ' + replyContext.senderLabel + ': ');
            }
        }

        baseImage = new Image();
        baseImage.crossOrigin = 'anonymous';
        baseImage.onload = function () {
            var maxW = editCanvas.width;
            var maxH = editCanvas.height;
            var iw = baseImage.width;
            var ih = baseImage.height;
            var scale = Math.min(maxW / iw, maxH / ih, 1);
            var drawW = iw * scale;
            var drawH = ih * scale;
            var dx = (maxW - drawW) / 2;
            var dy = (maxH - drawH) / 2;

            editCtx.clearRect(0, 0, editCanvas.width, editCanvas.height);
            editCtx.fillStyle = '#ffffff';
            editCtx.fillRect(0, 0, editCanvas.width, editCanvas.height);
            editCtx.drawImage(baseImage, dx, dy, drawW, drawH);
        };
        baseImage.src = imageUrl;

        $('#chatImageEditorModal').modal('show');
    }


    // Click on Edit button under image
    $(document).on('click', '.chat-edit-btn', function () {
        var imageUrl    = $(this).data('image-url');
        var senderLabel = $(this).data('sender-label') || '';
        var msgId       = $(this).data('message-id') || null;

        var ctx = {
            senderLabel: senderLabel,
            messageId:   msgId
        };
        openImageEditor(imageUrl, ctx);
    });

    // Color palette clicks
    $(document).on('click', '.chat-color-swatch', function () {
        $('.chat-color-swatch').removeClass('active');
        $(this).addClass('active');
        currentColor = $(this).data('color') || '#ff0000';
    });

    // Send edited image as reply
    $(document).on('click', '#chatEditSendBtn', function () {
        if (!editCanvas) return;

        // 1) Convert canvas to Blob (image)
        var dataUrl = editCanvas.toDataURL('image/png');
        var arr  = dataUrl.split(',');
        var mime = arr[0].match(/:(.*?);/)[1];
        var bstr = atob(arr[1]);
        var n    = bstr.length;
        var u8   = new Uint8Array(n);
        while (n--) {
            u8[n] = bstr.charCodeAt(n);
        }
        editedImageBlob = new Blob([u8], {type: mime});
        editedImageBlob.name = 'edited_chat_image.png';

        // 2) Get text from editor’s message box
        var msgFromEditor = $('#chatEditMessage').val() || '';

        // Put this text into the main input so central send logic uses it
        $('#chatMessageInput').val(msgFromEditor);

        // 3) Close editor and use normal send flow (which will send editedImageBlob + text)
        $('#chatImageEditorModal').modal('hide');
        $('#chatSendForm').trigger('submit');
    });

    /* ---------- MODAL CLOSE ---------- */
    $(document).on('hidden.bs.modal', '#branchChatModal', function () {
        chatBranchId = null;
        $('#chatMessages').empty();
        stopChatRefresh();
    });

    /* ---------- BRANCH FLOATING BUBBLE + HO TABLE BUTTON POLLING ---------- */
    $(function () {
        /* Branch side: floating chat bubble */
        if (CHAT_ROLE === 'Branch') {
            var branchIdForChat = DEFAULT_BRANCH_ID || $('#chatBranchId').val() || '';

            if (!branchIdForChat) {
                console.log('Branch chat disabled: no branch id in session.');
            } else {
                $('#chatBranchId').val(branchIdForChat);

                if (!$('#branchChatFloat').length) {
                    var $btn = $('<div id="branchChatFloat"></div>')
                        .html('<i class="fa fa-comments"></i>');
                    $('body').append($btn);
                }

                $('#branchChatFloat').on('click', function () {
                    if (!$('#branchChatModal').length) return;

                    chatBranchId = branchIdForChat;
                    $('#chatBranchTitle').text('Branch (' + branchIdForChat + ')');

                    forceScrollToBottomOnce = true;
                    $('#branchChatModal').modal('show');
                    loadChatMessages(true);
                    startChatRefresh();
                    clearNotifyBubble();
                });

                DEFAULT_BRANCH_ID = branchIdForChat;
                startBranchBubbleChecker();
            }
        }

        /* HO / Center side: make table Message buttons glow + show unread count */
        if (CHAT_ROLE === 'Center') {
            // Read initial lastChatId from buttons rendered in tables
            $('.btn-msg-branch').each(function () {
                var $btn   = $(this);
                var bid    = $btn.data('branch-id');
                var lastId = parseInt($btn.data('last-chat-id') || 0, 10);
                if (bid) {
                    if (!isNaN(lastId)) {
                        branchLastSeen[bid] = lastId;
                    } else if (typeof branchLastSeen[bid] === 'undefined') {
                        branchLastSeen[bid] = 0;
                    }
                }
            });

            function checkNewMessagesForButtons() {
                $.each(branchLastSeen, function (branchId, lastId) {
                    $.getJSON('branch_chat_latest.php', {
                        branchId: branchId,
                        role: 'Center'
                    }, function (res) {
                        if (res && res.success) {
                            var unread = parseInt(res.unread_count || 0, 10);
                            if (isNaN(unread)) unread = 0;
                            updateButtonUnread(branchId, unread);
                        }
                    });
                });
            }


            // poll every 6 seconds
            setInterval(checkNewMessagesForButtons, 6000);
        }
    });

})(jQuery);
</script>
</body>
</html>

