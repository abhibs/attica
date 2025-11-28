"use strict";

class sendOTP{
	constructor(){
		this.http = new XMLHttpRequest();
		
		this.name_input = document.getElementById("name");
		this.mobile_input = document.getElementById("mobile");
		
		this.send_otp_button = document.getElementById("send-otp-btn");
		this.otp_timer_display = document.getElementById("otp-timer-display");
		this.otp_timer_count = document.getElementById("otp-timer-count");
		
		this.ten_digit_number_regex = /^\d{10}$/;
		this.counter = 30;
	}
	hide_timer(){
		this.otp_timer_display.hidden = "true";
	}
	show_timer(){
		this.otp_timer_display.removeAttribute("hidden");
	}	
	disable_button(){
		this.send_otp_button.disabled = "true";
	}
	enable_button(){
		this.send_otp_button.removeAttribute("disabled");
	}	
	check_mobile_number(mobile){
		return (this.ten_digit_number_regex.test(mobile));
	}
	add_event(){
		const object = this;
		this.send_otp_button.addEventListener("click", (e)=>{
			const name = this.name_input.value;
			const mobile = this.mobile_input.value;
			if(this.check_mobile_number(mobile) && name != ""){
				object.disable_button();			
				object.http_request(name, mobile);				
			}
			else{
				window.alert("Name & Contact Information is Mandatory");
			}
		});
	}
	http_request(name, mobile){
		const object = this;
		this.http.open("POST", "../ot.php");
		this.http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		this.http.onload = function() {
			alert(`OTP is sent to your Mobile`);
			object.set_interval_x();
		};
		this.http.onerror = function() { 
			alert(`Network Error, Resend OTP`);
			object.enable_button();	
		};
		this.http.send(`name=${name}&data=${mobile}`);
	}
	set_interval_x(){		
		let x = 0;
		this.show_timer();
		const object = this;
		const intervalID = window.setInterval(function () {			
			object.otp_timer_count.textContent = +object.counter - +x;
			if (++x == object.counter) {
				window.clearInterval(intervalID);
				object.hide_timer();
				object.enable_button();
			}
		}, 1000);
	}
}

const obj = new sendOTP();
obj.hide_timer();
obj.add_event();