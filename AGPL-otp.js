	$(document).ready(function()
	{
		document.getElementById("submit").disabled = true;
		$("#employeeid").change(function()
		{
			alert("OTP Sent to Your Mobile Number");
			var username=$('#username').val();
			var empid=$('#employeeid').val();
			var req1=$.ajax
			({
				url:"loginOTP.php",
				type: "POST",
				data:{username:username,empid:empid},
			});
			req1.done(function(msg)
			{
				window.location.href("addCustomers.php");
			});
		});
		
		$("#loginotp").change(function()
		{
			var data=$('#loginotp').val();
			var req=$.ajax(
			{
				url:"otpValids.php",
				type: "POST",
				data:{data},
			}); 
			req.done(function(msg)
			{
				$("#loginotp").val(msg);
				if(msg=="OTP Validated")
				{
					$('#loginotp').attr('readonly','true');
					document.getElementById("submit").disabled = false;
				}
				else if(msg=="Invalid OTP")
				{
					alert("Branch and Employee Does Not Match!, Call Zonal Manager for Assistance!.");
				}
			});
		});
	});