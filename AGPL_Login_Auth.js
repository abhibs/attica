$(document).ready(function(){
	
	const branchId = $('#username');
	const employeeId = $('#employeeid');
	const loginOtp = $("#loginotp");
	const submitButton = $('#submit');
	
	loginOtp.attr('readonly', 'true');
	submitButton.attr('disabled', 'true');
	
	employeeId.change(function(){
		let username = branchId.val();
		let empid = employeeId.val();
		if(username !== '' && empid !== ''){
			const request1 = $.ajax({
				url: "loginOTP.php",
				type: "POST",
				data: {username: username, empid: empid},
				dataType: 'JSON'
			});
			request1.done(function(res){
				if(res.status == "ok"){
					loginOtp.removeAttr('readonly');
					alert(res.msg);
				}
				else if(res.status == "Invalid"){
					alert(res.msg);
				}
				else{
					alert("Oops, Something went wrong");
				}
			});
		}
		else{
			alert('Please Fill Both BranchId And EmployeeId');
		}
	});
	
	loginOtp.change(function(){
		let data = loginOtp.val();
		const request2 = $.ajax({
			url:"otpValids.php",
			type: "POST",
			data:{data},
		});
		request2.done(function(msg){
			loginOtp.attr('readonly','true');
			if(msg == "OTP Validated"){
				loginOtp.val('OTP Validated');
				submitButton.removeAttr('disabled');
			}
			else{
				loginOtp.val('Invalid OTP');
			}
		});
	});
	
});