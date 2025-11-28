
<style>
    /* Sidebar scroll behaviour */
    #menu {
        overflow-y: scroll;
        overflow-x: hidden;
    }

    /* Small unread badge for ZONAL CHAT in left menu */
    .zimps-menu-badge {
        display: inline-block;
        min-width: 16px;
        padding: 0 4px;
        margin-left: 4px;
        border-radius: 8px;
        background: #ff0000;
        color: #ffffff;
        font-size: 10px;
        font-weight: 600;
        text-align: center;
        line-height: 16px;
        vertical-align: middle;
    }
</style>

<aside id="menu" style="overflow:scroll;overflow-x:hidden">
    <div id="sidebar-collapse">
        <ul class="nav" id="side-menu">
			<li class="active"><a href="subDashboardZonal.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-dashboard"></i> Dashboard</span></a></li>
			<!--<li class="active"><a href="overallReport.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-file-text"></i> COG Report</span></a></li>
			 <li class="active"><a href="bmcog.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-file-text"></i>BM COG</span></a></li>-->
			<li class="active"><a href="GoldInBranch.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-briefcase"></i>Gold In Branch</span></a></li>
			<li class="active"><a href="SilverInBranch.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-bar-chart"></i>Silver In Branch</span></a></li>
			<li class="active"><a href="stateGoldInBranch.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-file-text"></i>State Gold</span></a></li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-user-circle-o"></i> Customer</b></span><b><span class="fa arrow"></span></b></a>
					<ul class="nav nav-second-level">
						 <li><a href="addCustomerZonal.php"> Add Customer</a></li>
						<li><a href="subDisplayQuotation.php"> Waiting</a></li>
						<li><a href="subXmanageCustomer.php"> Blocked </a></li>
					</ul>
			</li>	
			<li><a href="subZonalBillVerification.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-check-circle-o"></i> Verify Bill</span></a></li>
			<li><a href="xapproveRelease.php"><i style="color:#990000;width:20px" class="fa fa-inbox" aria-hidden="true"></i> Release </a></li>
            		<li><a href="subEnquiryWalkinReport.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-users"></i> Walkin</span></a></li>
			<li><a href="viewGoldRate.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-calendar"></i> Today's Rate</span></a></li>
	                <li><a href="branchBalance.php"><span class="nav-label"><i style="color:#990000; width:20px" class="fa fa-money"></i> Available Amt</span></a></li>
					            <!-- NEW: Zonal ↔ IMPS chat menu item with unread badge -->
            <li>
                <a href="chatzonalimps.php">
                    <span class="nav-label">
                        <i style="color:#990000; width:20px;" class="fa fa-comments"></i> IMPS Chat
                        <!-- total unread, from JS; hidden when 0 -->
                        <span id="zimpsMenuBadge" class="zimps-menu-badge" style="display:none;">0</span>
                    </span>
                </a>
            </li>
			<li>
				<a href="chat.php">
					<span class="nav-label">
						<i style="color:#990000; width:20px;" class="fa fa-comment"></i>
						Branch Chat
						<!-- total unread BRANCH messages -->
						<span id="zbranchMenuBadge" class="zimps-menu-badge" style="display:none;">0</span>
					</span>
				</a>
			</li>
			<li><a href="inbox.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-envelope"></i> MailBox</span></a></li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-institution"></i> Branch</b></span><b><span class="fa arrow"></span></b></a>
					<ul class="nav nav-second-level">
						<li><a href="viewBranch.php"> Branch Details</a></li>
						<li><a href="subAssignBranch.php"> Assign Branch</a></li>
						<li><a href="branchEmployeeRating.php"> Branch/BM Rating</a></li>
					</ul>

			</li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-sort-numeric-asc"></i> OTP</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					 <li><a href="viewOtp.php"> Customer OTP</a></li>	
					<li><a href="viewLogin.php"> Login OTP</a></li>
				</ul>
			</li>
			<li><a href="#"><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-edit"></i> Transaction</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="subXviewTransaction.php"> Cash</a></li>
					<li><a href="subXviewTransactionIMPS.php"> IMPS</a></li>
					<li><a href="subXviewTransactionBoth.php"> Cash / IMPS</a></li>
					<li><a href="subXreleaseDataR.php"> Release</a></li>
					<li><a href="pledgeMasterzonal.php">Pledge Bills</a></li>
				</ul>
			</li>
			<!--<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-user"></i> Edit</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="editCustomers.php"> Customer Name</a></li>
					<li><a href="subBranchBillList.php"> Bill Name</a></li>
				</ul>
			</li>-->
			<li><a href="#"><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-rupee"></i> Funds</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="subApproveFund.php">Approve Funds </a></li>
					<li><a href="approveTFund.php">Approve Transfers </a></li>
				</ul>
			</li>
			<li><a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-file-text"></i> Reports</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="subDailyClosingR.php">Branch Report</a></li>
					<li><a href="subViewbillzonal.php">Transaction Report</a></li>
					<li><a href="subEnquiryReportAll.php">Enquiry Report</a></li>
					<li><a href="subSendingReportZonal.php">Send Report</a></li>
					<li><a href="subFundReports.php">Funds Report</a></li>
					<li><a href="subDailyReports.php">Daily Closing</a></li>

					<li><a href="subDailyExpense.php">Daily Expense</a></li>
					<li><a href="subXIMPSreport.php">IMPS Report</a></li>
				</ul>
			</li>	
			<li><a href="#"><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-dashboard"></i> VM Details</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="zmnv.php"> VM's Branch</a></li>
					<li><a href="zviewvm.php"> Update VM's</a></li>
					<li><a href="zvmadd.php">Add VM's</a></li>
				</ul>
			</li>
			<li><a href="fraud.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-user-secret"></i> Fraud</span></a></li>
			<li><a href="logout.php"><span class="nav-label"><i style="color:#990000;width:20px" class="fa fa-sign-out"></i> Logout</span></a></li>
	</ul>
    </div>
</aside>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
    // jQuery is loaded in footer; make sure it exists
    if (typeof jQuery === 'undefined') {
        return;
    }

    (function ($) {

        // --- IMPS Chat total (zonal_imps_chat_unread_total.php) ---
        function refreshZimpsMenuBadge() {
            var $badge = $('#zimpsMenuBadge');
            if (!$badge.length) return; // safety

            $.getJSON('zonal_imps_chat_unread_total.php', function (res) {
                if (!res || res.success !== true) {
                    $badge.hide();
                    return;
                }

                var count = parseInt(res.total_unread || 0, 10);
                if (isNaN(count) || count <= 0) {
                    $badge.hide();
                } else {
                    $badge.text(count > 99 ? '99+' : count);
                    $badge.show();
                }
            }).fail(function () {
                $badge.hide();
            });
        }

        // --- Branch Chat total (branch_chat_unread_total.php) ---
        function refreshZbranchMenuBadge() {
            var $badge = $('#zbranchMenuBadge');
            if (!$badge.length) return; // safety

            $.getJSON('branch_chat_unread_total.php', function (res) {
                if (!res || res.success !== true) {
                    $badge.hide();
                    return;
                }

                var count = parseInt(res.total_unread || 0, 10);
                if (isNaN(count) || count <= 0) {
                    $badge.hide();
                } else {
                    $badge.text(count > 99 ? '99+' : count);
                    $badge.show();
                }
            }).fail(function () {
                $badge.hide();
            });
        }

        // Run once + poll
        refreshZimpsMenuBadge();
        refreshZbranchMenuBadge();
        setInterval(refreshZimpsMenuBadge,   5000);
        setInterval(refreshZbranchMenuBadge, 5000);

    })(jQuery);
});
</script>


