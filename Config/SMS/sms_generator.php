<?php
class SMS{
    private $json;
    private $mobile = null;
    private $message = null;

    private $current_template_key = null;
    private $last_http_code = 0;
    private $last_response  = '';
    private $last_error     = '';

    function __construct($mobile = null){
        $cfg = dirname(__FILE__).'/sms.json';
        $str = @file_get_contents($cfg);
        if ($str === false) {
            error_log("SMS: Unable to read $cfg");
            $this->json = [];
        } else {
            $this->json = json_decode($str, true) ?: [];
        }
        $this->mobile = $mobile;
    }

    /* Legacy GET name kept for compatibility; now routes to POST sender */
    private function send_sms(){
        $this->send_via_post();
    }

    /* Legacy cURL name kept for compatibility; now routes to POST sender */
    private function send_sms_curl(){
        $this->send_via_post();
    }

    private function build_from_template($tpl, array $vars){
        foreach ($vars as $v) {
            $tpl = preg_replace('/\{\#var\#\}/', (string)$v, $tpl, 1);
        }
        return $tpl;
    }

    private function resolve_template_id_for_message(){
        if ($this->current_template_key && isset($this->json['SMS'][$this->current_template_key]['Template_ID'])) {
            return (string)$this->json['SMS'][$this->current_template_key]['Template_ID'];
        }
        if (!empty($this->json['SMS']) && is_array($this->json['SMS'])) {
            foreach ($this->json['SMS'] as $k => $info) {
                $tpl = (string)($info['Template'] ?? '');
                $tid = (string)($info['Template_ID'] ?? '');
                if ($tpl && $tid) {
                    $needle = trim(preg_replace('/\s*\{\#var\#\}\s*/', ' ', $tpl));
                    $hay    = trim($this->message);
                    if ($needle !== '' && stripos($hay, preg_replace('/\s+/', ' ', $needle)) !== false) {
                        return $tid;
                    }
                }
            }
        }
        return '';
    }

    private function resolve_sender_for_template(){
        // Per-template Sender override if present, else default Sender
        if ($this->current_template_key && isset($this->json['SMS'][$this->current_template_key]['Sender'])) {
            return (string)$this->json['SMS'][$this->current_template_key]['Sender'];
        }
        return (string)($this->json['Sender'] ?? '');
    }

    private function send_via_post(){
        $apiKey   = $this->json['API']    ?? '';
        $baseURL  = rtrim($this->json['BaseURL'] ?? 'https://api-alerts.solutionsinfini.com/v3/', '/').'/';
        $entityId = $this->json['Entity_ID'] ?? '';
        $sender   = $this->resolve_sender_for_template();

        if (!$apiKey || !$sender || !$this->mobile || !$this->message) {
            $miss = [];
            if (!$apiKey)   $miss[] = 'API';
            if (!$sender)   $miss[] = 'Sender';
            if (!$this->mobile) $miss[] = 'mobile';
            if (!$this->message) $miss[] = 'message';
            error_log('SMS: Missing fields: ' . implode(', ', $miss));
            return;
        }

        $templateId = $this->resolve_template_id_for_message();

        $post = [
            'method'  => 'sms',
            'api_key' => $apiKey,
            'to'      => $this->mobile,
            'sender'  => $sender,
            'message' => $this->message
        ];

        if ($templateId !== '') {
            $post['template_id']   = $templateId;   // common
            $post['dlttemplateid'] = $templateId;   // variants
            $post['tempid']        = $templateId;   // variants
        }
        if ($entityId !== '') {
            $post['entity_id'] = $entityId;         // common
            $post['peid']      = $entityId;         // variants
        }

        $ch = curl_init($baseURL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->last_http_code = $code;
        $this->last_response  = (string)$resp;
        $this->last_error     = $err;

        if ($err || $code < 200 || $code >= 300) {
            error_log("SMS: HTTP $code, cURLerr=" . ($err ?: 'none') . ", resp=" . substr((string)$resp, 0, 500));
        } else {
            error_log("SMS: Sent OK, HTTP $code, resp=" . substr((string)$resp, 0, 500));
        }
    }

    /* ---------------- Public methods (unchanged signatures) ---------------- */

    function bm_login($bm_name,  $otp){
        $tpl = $this->json['SMS']['BM_Login']['Template'] ?? '';
        if ($tpl) {
            $this->message = $this->build_from_template($tpl, [$bm_name, $otp]);
            $this->current_template_key = 'BM_Login';
        } else {
            $this->message = "Dear $bm_name, use this One Time Password (OTP): $otp to log in to your Attica Gold Company Account.";
            $this->current_template_key = null;
        }
        $this->send_sms_curl();
    }

    function customer_verification($customer_name, $otp){
        $tpl = $this->json['SMS']['Customer_Verification']['Template'] ?? '';
        if ($tpl) {
            $this->message = $this->build_from_template($tpl, [$customer_name, $otp]);
            $this->current_template_key = 'Customer_Verification';
        } else {
            $this->message = "Dear $customer_name, Welcome to Attica Gold Company, your registration code is $otp.";
            $this->current_template_key = null;
        }
        $this->send_sms_curl();
    }

    function case_notification($lawyer_name, $caseid, $case_date){
        // Uses your approved DLT template if present
        $tpl = $this->json['SMS']['Lawyer_Case_Reminder']['Template'] ?? '';
        if ($tpl) {
            $this->message = $this->build_from_template($tpl, [$lawyer_name, $caseid, $case_date]);
            $this->current_template_key = 'Lawyer_Case_Reminder';
        } else {
            $this->message = "Dear $lawyer_name, Your upcoming date for case $caseid, is nearing your case is on $case_date.";
            $this->current_template_key = null;
        }
        $this->send_sms_curl();
    }

    function branch_link($url){
        $tpl = $this->json['SMS']['Branch_Address']['Template'] ?? '';
        if ($tpl) {
            $this->message = $this->build_from_template($tpl, [$url]);
            $this->current_template_key = 'Branch_Address';
        } else {
            $this->message = "Dear Customer, Thank you for choosing Attica Gold Company, Click the link to find your nearest branch: $url";
            $this->current_template_key = null;
        }
        $this->send_sms_curl();
    }

	function te_cash_move($te_name, $from_branch, $to_branch, $amount_rupees, $date_str){
    $tpl = $this->json['SMS']['TE_Cash_Move']['Template'] ?? '';
    if ($tpl) {
        $this->message = $this->build_from_template($tpl, [$te_name, $from_branch, $to_branch, $amount_rupees, $date_str]);
        $this->current_template_key = 'TE_Cash_Move';
    } else {
        // Fallback text (kept concise & compliant-style)
        $this->message = "Dear $te_name, Cash movement approved from $from_branch to $to_branch of Rs $amount_rupees on $date_str. Please coordinate and confirm receipt. - Attica Gold Company";
        $this->current_template_key = null;
    }
    $this->send_sms_curl();
}

}
