<?php
	
	define ('CALLCENTERSERVER','http://14.97.3.234/Alpha_Attica/Api/');
	
	/* 
		BILLING INFO API - @ xmessage.php	
	*/
	$billingInfoURL = CALLCENTERSERVER.'billing_info';
	
	/* 
		CALL CENTER DASHBOARD - @ dashboardcc.php
		MULTI CURL INVOCATION
	*/
	$agentStatusURL = CALLCENTERSERVER.'realtime_agent_status';
	$pendingCallCountURL = CALLCENTERSERVER.'pending_call_count';       
	$inboundOutboundURL = CALLCENTERSERVER.'total_call_count';
	$callsInQueueURL = CALLCENTERSERVER.'total_queue_count';
	
	/* 
		CALL CENTER DASHBOARD - @ dashboardcc.php
		SINGLE CURL INVOCATION WITH POST DATA
	*/
	$soldoutCountURL = CALLCENTERSERVER.'sold_out_count';	
	
	/* 
		CALL CENTER DASHBOARD - @ dashboardcc.php
		GOLD RATE UPDATE
	*/
	$goldRateUpdateURL = CALLCENTERSERVER.'gold_rate_update';	
	
	/* 
		CALL CENTER DASHBOARD - @ dashboardcc.php
		ANNOUNCEMENT UPDATE
	*/
	$announceUpdateURL = CALLCENTERSERVER.'announcement_update';