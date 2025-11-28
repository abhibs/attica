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

<aside id="menu">
    <div id="sidebar-collapse">
        <ul class="nav" id="side-menu">

            <!-- Mailbox -->
            <li>
                <a href="inbox.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-envelope"></i> MailBox
                    </span>
                </a>
            </li>

            <!-- NEW: Zonal ↔ IMPS Chat menu item with unread badge -->
            <li>
                <a href="chatzonalimps.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-comments"></i> Zonal Chat
                        <!-- unread count from JS; hidden by default -->
                        <span id="zimpsMenuBadge" class="zimps-menu-badge" style="display:none;">0</span>
                    </span>
                </a>
            </li>

            <!-- Branch -->
            <li>
                <a href="viewBranch.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-institution"></i> Branch
                    </span>
                </a>
            </li>

            <!-- Rate -->
            <li>
                <a href="viewGoldRate.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-money"></i> Rate
                    </span>
                </a>
            </li>

            <!-- BM Details -->
            <li>
                <a href="assignBranch.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-phone"></i> BM Details
                    </span>
                </a>
            </li>

            <!-- Transactions -->
            <li>
                <a>
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-file-text"></i> Transactions
                    </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <!--li><a href="xapprovePhysicalCash.php"> Cash </a></li-->
                    <li><a href="xapprovePhysicalIMPS.php"> IMPS </a></li>
                    <li><a href="xapproveRelease.php"> Release </a></li>
                </ul>
            </li>

            <!-- Today's Bills -->
            <li>
                <a href="viewbill.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-pencil-square"></i> Today's Bills
                    </span>
                </a>
            </li>

            <!-- Funds -->
            <li>
                <a href="#">
                    <span class="nav-label">
                        <b><i style="color:#990000" class="fa fa-rupee"></i> Funds</b>
                    </span>
                    <b><span class="fa arrow"></span></b>
                </a>
                <ul class="nav nav-second-level">
                    <li><a href="approveFund.php">Approve Funds</a></li>
                    <li><a href="approveTFund.php">Approve Transfers</a></li>
                </ul>
            </li>

            <!-- Reports -->
            <li>
                <a>
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-file-text"></i> Reports
                    </span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    <li><a href="dailyClosingR.php">Branch Report</a></li>
                    <li><a href="transactionReports.php">Transaction Report</a></li>
                    <li><a href="ReleaseReports.php">Release Reports</a></li>
                    <li><a href="SendingReport.php">Gold/Silver Report</a></li>
                    <li><a href="fundReports.php">Funds Report</a></li>
                    <li><a href="xIMPSreport.php">IMPS Report</a></li>
                    <li><a href="fundTReports.php">Fund Transfer</a></li>
                    <li><a href="dailyReports.php">Daily Closing</a></li>
                    <li><a href="dailyExpense.php">Daily Expense</a></li>
                </ul>
            </li>

            <!-- Logout -->
            <li>
                <a href="logout.php">
                    <span class="nav-label">
                        <i style="color:#990000" class="fa fa-sign-out"></i> Logout
                    </span>
                </a>
            </li>

        </ul>
    </div>
</aside>
