<?php	
	session_start();
	error_reporting(E_ERROR | E_PARSE);
	$type=$_SESSION['usertype'];
	if($type=='Branch'){
		include("header.php");
		include("menu.php");
	}
	else if($type=='Master'){
		include("header.php");
		include("menumaster.php");
	}
	else if($type=='Zonal'){
		include("header.php");
	    include("menuZonal.php");
	}
	else if($type=='SubZonal'){
		include("header.php");
		include("menuSubZonal.php");
	}
	else if ($type == 'Software') {
		include("header.php");
		include("menuSoftware.php");
	}
	else if ($type == 'ZonalMaster') {
        	include("header.php");
        	include("menuzonalMaster.php");
	}
	else if ($type == 'SundayUser') {
        	include("header.php");
        	include("menuSundayUser.php");
	}
	else if($type=='Issuecall'){
	    include("header.php");
		include("menuissues.php");
	}
	else if ($type == 'IssueHead') {
		include("header.php");
		include("menuIssueHead.php");
	}
	else if($type=='Call Centre'){
		include("header.php");
	    include("menuCall.php");
	}
	else if($type=='AccHead'){
	    include("header.php");
		include("menuaccHeadPage.php");
	}
	else if($type=='HR'){
		include("header.php");
	    include("menuhr.php");
	}
	else if($type=='ApprovalTeam'){
		include("header.php");
	    include("menuapproval.php");
	}
	else if($type=='Assets'){
		include("header.php");
        include("menuassets.php");
	}
	else{
		include("logout.php");
	}
?>
<div id="wrapper">
    <div class="row content">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body" style="box-shadow:10px 15px 15px #999;">
					<iframe src="CUSTOMER HANDLING GUIDELINES.pdf" height="600px" width="100%" title="Standard Operations Procedure"></iframe>
				</div>
			</div>
		</div>
		<div style="clear:both"></div>
	</div>
<?php include("footer.php"); ?>	
