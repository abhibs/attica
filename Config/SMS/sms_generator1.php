<?php 
	
	class SMS{
		private $json;
		private $mobile = null;
		private $message = null;
		
		function __construct($mobile = null){
			$str = file_get_contents(dirname(__FILE__).'/sms.json');
			$this->json = json_decode($str, true);
			$this->mobile = $mobile;
		}
		
		private function send_sms(){
			$url = "http://api-alerts.solutionsinfini.com/v3/?" .
			"method=sms&" .
			"api_key=".$this->json['API']."&" .
			"to=".$this->mobile."&" .
			"sender=".urlencode($this->json['Sender'])."&" .
			"message=".urlencode($this->message);
			$output = file($url);
		}
		
		private function send_sms_curl(){
			$url = "http://api-alerts.solutionsinfini.com/v3/?" .
			"method=sms&" .
			"api_key=".$this->json['API']."&" .
			"to=".$this->mobile."&" .
			"sender=".urlencode($this->json['Sender'])."&" .
			"message=".urlencode($this->message);
			
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			$response = curl_exec($curl);			
			curl_close($curl);
		}		
		
		function bm_login($bm_name,  $otp){
			$this->message = "Dear $bm_name, use this One Time Password (OTP): $otp to log in to your Attica Gold Company Account.";
			$this->send_sms_curl();
		}
		
		function customer_verification($customer_name, $otp){
			$this->message = "Dear $customer_name, Welcome to Attica Gold Company, your registration code is $otp.";
			$this->send_sms_curl();
		}
		
		function branch_link($url){
			$this->message = "Dear Customer, Thank you for choosing Attica Gold Company, Click the link to find your nearest branch: $url";
			$this->send_sms_curl();
		}
	}
	
?>