<aside id="menu" style="overflow:scroll;overflow-x:hidden">
	<div id="sidebar-collapse">
		<ul class="nav" id="side-menu">
			<li><a href="inbox.php"><i style="color:#990000;width:20px" class="fa fa-envelope"></i> MailBox</a></li>
			<li><a href="dashboardApproval.php"><i style="color:#990000;width:20px" class="fa fa-dashboard"></i> Dashboard</a></li>
<!-- 			<li><a href="zonalApprovalRemarks.php"><i style="color:#990000;width:20px" class="fa fa-bell-o"></i> Notification</a></li> -->
			<li><a href="xmanageCustomer.php"><i style="color:#990000;width:20px" class="fa fa-ban"></i></span> Blocked <sup><?php echo $customerCount; ?></sup></a></li>
			<li><a href="assignBranch.php"><span class="nav-label"><i style="color:#990000; width:20px;" class="fa fa-phone"></i> BM Details</span></a></li>
			<!--<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-navicon"></i> Bills</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="xviewTransaction.php"> Cash</a></li>
					<li><a href="xviewTransactionIMPS.php"> IMPS</a></li>
					<li><a href="xviewTransactionBoth.php"> Cash / IMPS</a></li>
				</ul>
			</li>-->
		<!--	<li><a href="xapproveRelease.php"><i style="color:#990000;width:20px" class="fa fa-inbox" aria-hidden="true"></i> Release </a></li>-->
			 <li><a href="#"><span class="nav-label"><b><i style="color:#990000; width:20px" class="fa fa-edit"></i>
							Bills</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="xviewTransaction.php"> Cash </a></li>
					<li><a href="xviewTransactionIMPS.php"> IMPS </a></li>
					<li><a href="xviewTransactionBoth.php"> Cash / IMPS</a></li>
					
				</ul>
			</li>
			<li><a href="xapproveRelease.php"><i style="color:#990000;width:20px" class="fa fa-inbox" aria-hidden="true"></i> Release </a></li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-edit"></i> Edit</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="editCustomers.php"> Customer Name</a></li>
					<li><a href="branchBillList.php"> Bill Name</a></li>
				</ul>
			</li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-sort-numeric-asc"></i> OTP</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="viewOtp.php"> Customer</a></li>
					<li><a href="viewLogin.php"> Login</a></li>
				</ul>
			</li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-rupee"></i> Funds</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="approveFund.php">Requests</a></li>
					<li><a href="approveTFund.php">Transfers </a></li>
				</ul>
			</li>
			<li>
				<a><span class="nav-label"><b><i style="color:#990000;width:20px" class="fa fa-file-text"></i> Reports</b></span><b><span class="fa arrow"></span></b></a>
				<ul class="nav nav-second-level">
					<li><a href="dailyReports.php">Daily Closing</a></li>
					<!-- <li><a href="zonalBillVerification.php">Verify</a></li> -->
				</ul>
			</li>
			<li><a href="fraud.php"><i style="color:#990000;width:20px" class="fa fa-male"></i> Fraud/Theft</a></li>
			<li><a href="logout.php"><i style="color:#990000;width:20px" class="fa fa-sign-out"></i> Logout</a></li>
		</ul>
	</div>
</aside>
