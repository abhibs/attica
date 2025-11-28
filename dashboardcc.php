<?php
    error_reporting(E_ERROR | E_PARSE);
    session_start();
    $type=$_SESSION['usertype'];
	if($type == 'Call Centre'){
	    include("header.php");
		include("menuCall.php");
	}
	else if($type == 'Master'){
	    include("header.php");
		include("menumaster.php");
	}
    else{
        include("logout.php");
	}
	date_default_timezone_set("Asia/Kolkata");
	include("dbConnection.php");
	require 'Config/CallCenter.php';
	$date=date('Y-m-d');
    $time=date("h:i:s");
	
	if(isset($_GET['date']) && $_GET['date']!='' && $_GET['date']!=$date ){
		$callcenterData = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM callcenter WHERE date='$_GET[date]'"));
		// Agent status
		$agentLogged = $callcenterData['loggedIn'];
		$agentpaused = $callcenterData['onBreakAgents'];
		$agentidle = $callcenterData['waiting'];
		$agentincall = $callcenterData['onCall'];
		$agentdispoupdate = $callcenterData['Dispo'];
		$agentdead = $callcenterData['Dead'];
		$activeAgents = $callcenterData['activeAgents'];
		
		// Pending call count
		$pendingMissedcallCount = $callcenterData['missedCallPending'];
		$pendingSchedulecallCount = $callcenterData['scheduledPending'];
		$totalMissedcallCount = $callcenterData['missedCallTotal'];
		$totalschedulecallCount = $callcenterData['scheduledTotal'];
		
		// Total Inbound and Outbound Calls
		$totalInbound= $callcenterData['incomingCalls'];
		$totalOutbound = $callcenterData['outgoingCalls'];
		
		// CALLS IN QUEUE
		$callInQueue = $callcenterData['callsOnQueue'];
		
		$soldout = $callcenterData['soldOut'];
		
		$todaygold=$callcenterData['goldrateCash'];
		$todaygoldonline=$callcenterData['goldrateImps'];		
		$todaysilver=$callcenterData['silverRareCash'];
		$todaysilverOnline=$callcenterData['silverRateImps'];
	}
	else{
		/*** MULTI CURL INVOCATION ***/		
		$multiCurl = array();
		$result = array();
		$url = array($agentStatusURL,$pendingCallCountURL,$inboundOutboundURL,$callsInQueueURL);
		$mh = curl_multi_init();
		foreach ($url as $i => $urls) {
			$multiCurl[$i] = curl_init();
			curl_setopt($multiCurl[$i], CURLOPT_URL,$urls);
			curl_setopt($multiCurl[$i], CURLOPT_HEADER,0);
			curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
			curl_multi_add_handle($mh, $multiCurl[$i]);
		}
		$index = null;  
		do {
			curl_multi_exec($mh,$index);
		} while($index > 0);  
		foreach($multiCurl as $k => $ch) {
			$result[$k] = curl_multi_getcontent($ch);
			curl_multi_remove_handle($mh, $ch);
		}
		curl_multi_close($mh);
		
		// Agent status
		$result0 = json_decode($result[0], true);
		$agentLogged = $result0[0]['Total'];
		$agentpaused = $result0[0]['PAUSED'];
		$agentidle = $result0[0]['IDLE'];
		$agentincall = $result0[0]['INCALL'];
		$agentdispoupdate = $result0[0]['DISPO'];
		$agentdead = $result0[0]['DEAD'];
		$activeAgents = $result0[0]['IDLE']+$result0[0]['INCALL']+$result0[0]['DISPO']+$result0[0]['DEAD'];
		
		// Pending call count
		$result1 = json_decode($result[1], true);
		$pendingMissedcallCount = $result1['Pending_Missed_call_Count'];
		$pendingSchedulecallCount = $result1['Pending_Schedule_call_Count'];
		$totalMissedcallCount = $result1['Total_Missed_call_Count'];
		$totalschedulecallCount = $result1['Total_Schedule_call_Count'];
		
		// Total Inbound and Outbound Calls
		$result2 = json_decode($result[2], true);
		$totalInbound = $result2['Inbound'];
		$totalOutbound = $result2['Outbound'];
		
		// CALLS IN QUEUE
		$result3 = json_decode($result[3], true);
		$callInQueue = $result3['Call_In_Queue'];
		
		
		/***  SINGLE CURL INVOCATION WITH POST DATA  ***/
		
		$rowTSC = array();
		$rowTSC['From_Billing_Date'] = $date;
		$rowTSC['To_Billing_Date'] = $date;
		$chs = curl_init($soldoutCountURL);
		$a = json_encode($rowTSC);
		curl_setopt($chs, CURLOPT_POSTFIELDS,$a);
		curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($chs);
		curl_close($chs); 
		$result4 = json_decode($res, true);	
		$soldout = $result4['TOTAL_SOLD_OUT'];
		
		//TODAY'S RATE
		$result8 = mysqli_query($con,"SELECT cash,transferRate FROM gold WHERE date='$date' and type='Gold' and city = 'Bangalore' order by id DESC");
		$row8= mysqli_fetch_assoc($result8);
		$todaygold=$row8['cash'];
		$todaygoldonline=$row8['transferRate'];
		
		$result88 = mysqli_query($con,"SELECT cash,transferRate FROM gold WHERE date='$date' and type='Silver' and city = 'Bangalore' order by id DESC");
		$row88= mysqli_fetch_assoc($result88);
		$todaysilver=$row88['cash'];
		$todaysilverOnline=$row88['transferRate'];
	}
   	
	//UPDATE CALL CENTER DATA
    if(isset($_POST['UpdateccData'])){
		$insconn = mysqli_query($con,"INSERT INTO callcenter (	loggedIn,activeAgents,onBreakAgents,onCall,waiting,Dispo,Dead,missedCallPending,scheduledPending,missedCallTotal,scheduledTotal,incomingCalls,outgoingCalls,callsOnQueue,soldOut,goldrateCash, goldrateImps, silverRareCash, silverRateImps,date,time) VALUES
		('$agentLogged','$activeAgents','$agentpaused','$agentincall','$agentidle','$agentdispoupdate','$agentdead','$pendingMissedcallCount','$pendingSchedulecallCount','$totalMissedcallCount','$totalschedulecallCount','$totalInbound','$totalOutbound','$callInQueue','$soldout','$todaygold','$todaygoldonline','$todaysilver','$todaysilverOnline','$date','$time')");
		if($insconn)
		{
			echo "<script type='text/javascript'>alert('Data Update Successfull')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		}
		else
		{
			echo "<script type='text/javascript'>alert('Error Inserting Details!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		} 	
	}
	
	// GOLD RATE UPDATE
	if(isset($_POST['updateGoldRate'])){
		$goldRate = $_POST['goldRate'];
		$rowTSC = array();
		$rowTSC['RATE'] = $goldRate;
		$chs = curl_init($goldRateUpdateURL);
		$a = json_encode($rowTSC);
		curl_setopt($chs, CURLOPT_POSTFIELDS,$a);
		curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($chs);
		curl_close($chs);
		$result = json_decode($res,true);
		if($result['Status'] == "Success"){
			echo "<script type='text/javascript'>alert('Gold Rate Updated')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		}
		else{
			echo "<script type='text/javascript'>alert('Error Updating Gold Rate!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		} 
	}
	
	//ANNOUNCEMENT UPDATE
	if(isset($_POST['announceUpdate'])){
		$announce = $_POST['ann'];
		$rowTSC = array();
		$rowTSC['TEXT'] = $announce;
		$chs = curl_init($announceUpdateURL);
		$a = json_encode($rowTSC);
		curl_setopt($chs, CURLOPT_POSTFIELDS,$a);
		curl_setopt($chs, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($chs);
		curl_close($chs); 
		$result = json_decode($res,true);
    	if($result['Status'] == "Success"){
    		$insconn = "INSERT INTO announcement_update(thoughtoftheday,date,time) VALUES ('$announce','$date','$time')";
			if(mysqli_query($con,$insconn)){
				echo "<script type='text/javascript'>alert('Announcement Updated')</script>";
    			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
			}
			else{
			    echo "<script type='text/javascript'>alert('Error Inserting Data!!!')</script>";
			    echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
			} 
		}
	    else{
			echo "<script type='text/javascript'>alert('Error Updating Announcement!!!')</script>";
			echo "<script>setTimeout(\"location.href = 'dashboardcc.php';\",150);</script>";
		} 	
	}
	
?>
<style>
	modal{
		display: block !important;
	}
	.modal-dialog{
		overflow-y: initial !important
	}
	.modal-body{
		height: 350px;
		overflow-y: auto;
	}

	#wrapper{
		background-color:#e6e6fa;
	}
	.box{
		padding:10px;
		transition:.2s all; 
	}
	.box-wrap:hover .box{
		transform: scale(.98);
		box-shadow:none;
	}
	.box-wrap:hover .box:hover{
	    filter:blur(0px);
		transform: scale(1.05);
		box-shadow:0 8px 20px 0px #b8860b;
	}
	.hpanel{
		margin-bottom:5px;
	}
	.text-success{
		color:#123C69;
		text-transform:uppercase;
		font-size: 18px;
	}
	.stats-label{
		text-transform:uppercase;
		font-size: 10px;
	}
	.list-item-container h3{
		font-size: 14px;
	}
	.theadRow {
		text-transform:uppercase;
		background-color:#123C69!important;
		color: #f2f2f2;
		font-size:11px;
	}	
	.dataTables_empty{
		text-align:center;
		font-weight:600;
		font-size:12px;
		text-transform:uppercase;
	}
</style>

<!-- MODAL - SOLD OUT DETAILS -->
<div class="modal fade" id="soldOutModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header">
				<h3 class="text-success"><b>Agent Soldout Details</b></h3>
			</div>
			<div class="modal-body">
				<table class="table table-striped table-bordered">
					<thead>
						<tr class="theadRow">
							<th>Name</th>
							<th>ID</th>
							<th>Sold</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ($result4 as $key => $value) {
							?>
							<?php
								
								//$result3 = array("8009" => 'Anandhi',"8006" => 'Shamini', "8032" => 'Angel', "8010" => 'Anuja', "8027" => 'Blessy', "8037" => 'Ganga', "8017" => 'Geetha', "8094" => 'Hemavathi R', "8003" => 'Indhumathi', "8005" => 'Indra', "8040" => 'Varalakshmi', "8035" => 'Azeez', "8025" => 'Jyothika', "8002" => 'Kiran', "8001" => 'Mamatha Y', "8029" => 'Mamatha.s', "8023" => 'Meghana CSR', "8031" => 'Monisha', "8030" => 'Padmapriya', "8012" => 'Shwetha', "8019" => 'Priya.N', "8016" => 'Ramya N', "8034" => 'Ramya T', "8039" => 'Shamala', "8007" => 'RD Chowdhary', "8033" => 'Shodini', "8024" => 'Sneha', "8036" => 'Soundarya', "8018" => 'Akshaya', "8008" => 'Vijayalakshmi', "8036" => 'Vydevi', "8013" => 'Sanjana',"8103" => 'Rajarajeshwari',"8076" => 'Geetha C',"8101" => 'Manigandan',"8004" => 'Gangothri',"8014" => 'Anitha',"8028" => 'Shilpa');
								
								$result3 = array(
								"8001"=>"Anitha",
								"8002"=>"Angel",
								"8003"=>"Jeevitha",
								"8004"=>"Deepa",
								"8005"=>"Blessy",
								"8006"=>"Gangothri",
								"8007"=>"Geetha.C",
								"8008"=>"Indra",
								"8009"=>"Shodini",
								"8010"=>"Jyothika",
								"8011"=>"Mamatha Y",
								"8012"=>"Jyothi",
								"8013"=>"Monisha",
								"8014"=>"Padmapriya",
								"8016"=>"Gracy",
								"8017"=>"Ramya.T ",
								"8018"=>"Shashikala",
								"8019"=>"Shwetha",
								"8020"=>"Nikhitha",
								"8022"=>"Vijayalakshmi V",
								"8024"=>"Vinitha",
								"8025"=>"Vasanthikumari",
								"8027"=>"Jaykumar",
								"8030"=>"Chowdhary RD"
								);
								
								echo "<tr>";
								echo "<td><h5>" . $result3[$key] . "</h5></td>";
								echo "<td><h5>" . $key . "</h5></td>";
								echo "<td><h5>" . $value . "</h5></td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>

<!-- MODAL - ANNOUNCEMENT LIST -->
<div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="color-line"></div>
			<span class="fa fa-close modaldesign" data-dismiss="modal"></span>
			<div class="modal-header">
				<h3 class="text-success"><b>Thought Of The Day</b></h3>
			</div>
			<div class="modal-body">
				<table class="table table-striped table-bordered">
					<thead>
						<tr class="theadrow">
							<th><i class="fa fa-sort-numeric-asc"></i></th>
							<th>Thought Of The Day</th>
							<th>Date</th>
							<th>Time</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$query = mysqli_query($con,"SELECT * from announcement_update ORDER BY id DESC");
							$i=1;
							while($row = mysqli_fetch_assoc($query))
							{
								echo "<tr>";
								echo "<td><h5>" . $i ."</h5></td>";
								echo "<td><h5>" . $row['thoughtoftheday'] ."</h5></td>"; 
								echo "<td><h5>" . $row['date'] ."</h5></td>"; 
								echo "<td><h5>" . $row['time'] ."</h5></td>"; 
								echo "</tr>";
								$i++;
							}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>
</div>

<!--<meta http-equiv="refresh" content="50">-->
<div id="wrapper" style="top:50px">
    <div class="content animate-panel">
	    <div class="row m-t-md">
	        
			<!--   AGENT TOTAL   -->   
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Agents (<?php echo $agentLogged; ?>)</h3>
						</div>
						<span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Active Agents:</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $activeAgents; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label">On Break Agents:</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $agentpaused; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
            
            <!--  AGENT STATUS  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Agent Status</h3>
						</div>
						<span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-4">
                                    <small class="stats-label"> On Call</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $agentincall; ?></h4>
								</div>
								<div class="col-xs-4">
                                    <small class="stats-label"> Waiting</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $agentidle;?></h4>
								</div>
                                <div class="col-xs-4">
                                    <small class="stats-label"> Dispo / Dead </small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo " ".$agentdispoupdate."/".$agentdead; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
            
            <!--  CALL STATUS  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Call Status (<?php $totalS = $totalOutbound+$totalInbound; echo $totalS; ?>)</h3>
						</div>                       
						<span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">outgoing</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $totalOutbound; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Incoming</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $totalInbound; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
			<!--  CALLS ON QUEUE  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> Calls On Queue </h3>
						</div>
						<span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Calls On queue</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $callInQueue; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
			<!--  MISSED CALLS  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> Missed Calls </h3>
						</div>
						<span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Pending</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $pendingMissedcallCount; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Total</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $totalMissedcallCount; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>

			<!--  SCHEDULED CALLS  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Scheduled Calls </h3>
						</div>
						<span class="font-bold no-margins">
						</span>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Pending</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $pendingSchedulecallCount; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> Total</small>
                                    <h4><i style="color:#990000" class="fa fa-user"></i> <?php echo $totalschedulecallCount; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
			<!--  TOTAL SOLD OUT   --> 
            <div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success">Today's Sold Out</h3>
						</div>
						<div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"><b>Total</b></small>
                                    <h4><i style="color:#990000" class="fa fa-users"></i> <?php echo $soldout; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b>&nbsp; <i class="fa fa-angle-double-right"></i>
						<a data-toggle="modal" data-target="#soldOutModal">VIEW DETAILS</a>
					</div>	
				</div>
			</div>
            
            <!--  TODAY GOLD RATE  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> Today's Gold Rate</h3>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Cash:</small>
                                    <h4><i style="color:#990000" class="fa fa-money"></i> <?php echo $todaygold; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> IMPS/RTGS:</small>
									<h4><i style="color:#990000" class="fa fa-money"></i> <?php echo $todaygoldonline; ?></h4>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center">
						<b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
			<!--  SILVER RATE  -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> Silver Rate</h3>
						</div>
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label"> Cash:</small>
                                    <h4><i style="color:#990000" class="fa fa-money"></i> <?php echo $todaysilver; ?></h4>
								</div>
                                <div class="col-xs-6">
                                    <small class="stats-label"> IMPS/RTGS:</small>
									<h4><i style="color:#990000" class="fa fa-money"></i> <?php echo $todaysilverOnline; ?></h4>
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
			<!--  GOLD RATE UPDATE -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body" style="height:130px;">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> Update Gold Rate</h3>
						</div> 
                        <div class="m-t-xl">
                            <div class="row">
								<form method="POST" action="">
									<div class="col-xs-9">
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#900" class="fa fa-rupee"></span></span>
											<input type="text" name="goldRate" placeholder="Gold Rate" class="form-control">
										</div>
									</div>
									<div class="col-xs-2">
										<button class="btn btn-success" type="submit" name="updateGoldRate"><i style="color:#ffcf40" class="fa fa-pencil"></i></button>
									</div>
								</form>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
			<!--  ANNOUNCEMENT  -->
			<div class="col-lg-6">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body" style="height:130px;">
						<div class="stats-title pull-left">
							<h3 class="font-extra-bold no-margins text-success"> Announcement</h3>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<form method="POST" action="">
									<div class="col-xs-10">
										<div class="input-group">
											<span class="input-group-addon"><span style="color:#900" class="fa fa-commenting"></span></span>
											<textarea type="text" name="ann" placeholder="Thought of the Day" class="form-control" style="width:400px;"></textarea>
										</div>
									</div>
									<div class="col-xs-2">
										<button class="btn btn-success" type="submit" name="announceUpdate"><i style="color:#ffcf40" class="fa fa-bullhorn"></i></button>
									</div>
								</form>
							</div>
						</div>
					</div>
				    <div style="color:#990000" class="panel-footer" align="center"> 
                        <b>Attica Gold Pvt Ltd</b>&nbsp; <i class="fa fa-angle-double-right"></i>
						<a data-toggle="modal" data-target="#announcementModal">VIEW DETAILS</a>
					</div>	
				</div>
			</div>
			
			<!-- Date Selector -->
			<div class="col-lg-3">
                <div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
                    <div class="panel-body">
                        <div class="stats-title pull-left">
                            <h3 class="font-extra-bold no-margins text-success"> Date Selector </h3>
						</div>                      
                        <div class="m-t-xl">
                            <div class="row">
                                <div class="col-xs-9">
                                    <input type="date" class="form-control" name="brname" placeholder="Branch" id="dateValue"/>
								</div>
                                <div class="col-xs-3">  
                                    <a class="btn btn-success" id="dateButton"><i class="fa fa-search"></i></a> 
								</div>
							</div>
						</div>
					</div>
                    <div style="color:#990000" class="panel-footer" align="center">
                        <b>Attica Gold Pvt Ltd</b> &nbsp; 
					</div>
				</div>
			</div>
                      
			<div class="col-lg-3">
				<div class="hpanel stats" style="box-shadow:5px 5px 5px #999;">
					<div class="panel-body">
						<div class="stats-title pull-left">
							<h3 class="font-extra-bold no-margins text-success"> Update Details</h3>
						</div>
						<div class="m-t-xl">
							<div class="row">
								<div style="text-align: center">
									<form  method="POST" action="">
										<button class="btn btn-success" type="submit" name="UpdateccData"> UPDATE </button>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div style="color:#990000" class="panel-footer" align="center"> 
						<b>Attica Gold Pvt Ltd</b> &nbsp;
					</div>
				</div>
			</div>
			
		</div>
	</div>	
	
	<script type="text/javascript">
		$(document).ready(function(){
			$("#dateButton").click(function(){
				var date =  $('#dateValue').val();
				location.href = 'dashboardcc.php?date='+date;
			});
		});
	</script>
<?php include("footer.php"); ?>