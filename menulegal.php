<aside id="menu" style="overflow:scroll;overflow-x:hidden">
    <div id="sidebar-collapse">
        <ul class="nav" id="side-menu">
			<li><a href="assignBranch.php"><i style="color:#990000" class="fa pe-7s-users"></i> BMs LIST</a></li>
			<li><a href="xBillList.php"><i style="color:#990000" class="fa fa-list"></i> BILLS</a></li>
			<li><a href="fraud.php"><i style="color:#990000" class="fa fa-bug"></i> ADD FRAUD</a></li>
			<?php if($type=='Legal' && $_SESSION["login_username"]=='admin_legal'){ ?>
			<li><a href="searchbillsdata.php"><i style="color:#990000" class="fa fa-search"></i> BILL SEARCH</a></li>
			<!--<li><a href="customerAddAccess.php"><i style="color:#990000;" class="fa fa-universal-access"></i> ACCESS </a></li>-->
			<?php } ?>

			<li><a href="case.php"><i style="color:#990000" class="fa fa-sign-out"></i> Case</a></li>
			<li><a href="logout.php"><i style="color:#990000" class="fa fa-sign-out"></i> LOGOUT</a></li>
		</ul>
	</div>
</aside>

