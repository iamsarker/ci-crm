<?php

	function successAlert($msg){
		return '<div class="alert alert-success text-center" style="margin-bottom:0;font-weight:bold;font-size:18px;">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</div>';
	}
	function primaryAlert($msg){
		return '<div class="alert alert-info text-center" style="margin-bottom:0;font-weight:bold;font-size:18px;">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</div>';
	}
	function errorAlert($msg){
		return '<div class="alert alert-danger text-center" style="margin-bottom:0;font-weight:bold;font-size:18px;">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</div>';
	}

	function xss_cleaner($str, $is_image = FALSE) {
	    if (is_array($str)) {
	    	foreach($str as $key => $val) {
	    		$str[$key] = xss_cleaner($val);
	    	}
	        return $str;
	    }

	    $str = strip_tags($str);
	    $str = str_replace('<!--', '', $str);
	    $str = trim($str);
	    //$str = addslashes($str);
	    $str = htmlspecialchars($str);
	    $str = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	    $filtered_words = array('alert', 'readfile', 'prompt', 'confirm', 'cmd', 'passthru', 'eval', 'exec', 'expression', 'system', 'fopen', 'fsockopen', 'file_get_contents', 'file', 'unlink', 'javascript');
	    $str = str_replace($filtered_words, '', $str);
	    return $str;
	}

	function xssCleaner($str, $is_image = FALSE) {
		return xss_cleaner($str, $is_image);
	}

	function convertImageToBase65($imagePath) {
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$type = $finfo->file($imagePath);
		return 'data:' . $type . ';base64,' . base64_encode(file_get_contents($imagePath));
	}

	function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    function getCustomerFirstName(){
    	$ci = & get_instance();
    	$user = $ci->session->userdata("CUSTOMER");
    	return $user['first_name'];
    }

    function getCustomerLastName(){
    	$ci = & get_instance();
    	$user = $ci->session->userdata("CUSTOMER");
    	return $user['last_name'];
    }

	function getCustomerFullName(){
		$ci = & get_instance();
		$user = $ci->session->userdata("CUSTOMER");
		return $user['first_name'].' '.$user['last_name'];
	}

	function getUserData(){
		$ci = & get_instance();
		$user = $ci->session->userdata("CUSTOMER");
		return $user;
	}

    function getCustomerId(){
    	$ci = & get_instance();
    	$user = $ci->session->userdata("CUSTOMER");
    	return !empty($user['id']) ? $user['id'] : 0;
    }

	function getCompanyId(){
		$ci = & get_instance();
		$user = $ci->session->userdata("CUSTOMER");
		return !empty($user['company_id']) ? $user['company_id'] : 0;
	}

    function isLoggedin(){
    	$ci = & get_instance();
    	$user = $ci->session->userdata("CUSTOMER");
    	return ( !empty($user['id']) && $user['id']>0 ) ? true : false;
    }

	function isAdminLoggedIn(){
		$ci = & get_instance();
		$admin = $ci->session->userdata("ADMIN");
		return ( !empty($admin['id']) && $admin['id']>0 ) ? true : false;
	}

	function getAdminData(){
		$ci = & get_instance();
		$admin = $ci->session->userdata("ADMIN");
		return $admin;
	}

	function getAdminId(){
		$ci = & get_instance();
		$admin = $ci->session->userdata("ADMIN");
		return !empty($admin['id']) ? $admin['id'] : 0;
	}

	function getAdminFullName(){
		$ci = & get_instance();
		$admin = $ci->session->userdata("ADMIN");
		return $admin['first_name'].' '.$admin['last_name'];
	}

	function getCustomerSessionId(){
		$ci = & get_instance();
		if( empty($ci->session->customer_session_id) ){
			$ci->session->customer_session_id = time().rand(100,999);
		}

		return $ci->session->customer_session_id;
	}

	function getAdminSessionId(){
		$ci = & get_instance();
		if( empty($ci->session->admin_session_id) ){
			$ci->session->admin_session_id = time().rand(100,999);
		}

		return $ci->session->admin_session_id;
	}

    function getCurrencyId(){
		$ci = & get_instance();
		if( !empty($ci->session->currency_id) ){
			return $ci->session->currency_id;
		}
		return 1;
	}

	function getCurrencyCode(){

		$ci = & get_instance();
		if( !empty($ci->session->currency_code) ){
			return $ci->session->currency_code;
		}
		return "USD";
	}

	function changeCurrency($id, $code){
		$ci = & get_instance();
		$ci->session->currency_code = $code;
		$ci->session->currency_id = $id;

		echo $ci->session->currency_code;
		echo $ci->session->currency_id;
		die();
	}

	function getMenuItems(){
		$ci = & get_instance();
		$ci->load->model('Cart_model');
		$arr = $ci->Cart_model->getServiceGroups();
		return $arr;
	}

	function pricingDropdown(){
		$arr = array();
		$arr['price'] = "Monthly";
		$arr['price_six_month'] = "Semi-Annually";
		$arr['price_annually'] = "Annually";
		$arr['price_2yrs'] = "2-Years";
		$arr['price_3yrs'] = "3-Years";
		return $arr;
	}

	function pricingLabel($idx){
		$arr = pricingDropdown();
		return $arr[$idx];
	}

	function getServiceStatus($status){
		// 0=pending, 1=active, 2=expired, 3=suspended, 4=terminated
		switch ($status){
			case "0":
				return '<span class="badge rounded-pill bg-secondary">Pending</span>';
			case "1":
				return '<span class="badge rounded-pill bg-success">Active</span>';
			case "2":
				return '<span class="badge rounded-pill bg-dark">Expired</span>';
			case "3":
				return '<span class="badge rounded-pill bg-warning">Suspended</span>';
			case "4":
				return '<span class="badge rounded-pill bg-danger">Terminated</span>';
		}
	}

	function getDomainStatus($status){
		// 0=pending reg, 1=active, 2=expired, 3=grace, 4=cancelled, 5=pending transfer, 6=deleted
		switch ($status){
			case "0":
				return '<span class="badge rounded-pill bg-secondary">Reg. Pending</span>';
			case "1":
				return '<span class="badge rounded-pill bg-success">Active</span>';
			case "2":
				return '<span class="badge rounded-pill bg-dark">Expired</span>';
			case "3":
				return '<span class="badge rounded-pill bg-warning">Grace</span>';
			case "4":
				return '<span class="badge rounded-pill bg-danger">Cancelled</span>';
			case "5":
				return '<span class="badge rounded-pill bg-secondary">Trans. Pending</span>';
			case "6":
				return '<span class="badge rounded-pill bg-danger">Deleted</span>';
		}
	}

	function getDomainRegistrationYears(){
		$data[''] = '-- Select --';
		$data["1"] = "1 year";
		$data["2"] = "2 years";
		$data["3"] = "3 years";
		$data["4"] = "4 years";
		$data["5"] = "5 years";
		$data["6"] = "6 years";
		$data["7"] = "7 years";
		$data["8"] = "8 years";
		$data["9"] = "9 years";
		$data["10"] = "10 years";

		return $data;
	}

	function getTicketStatus($status){
		// 1=Opened, 2=Answered, 3=Customer reply, 4=Closed
		switch ($status){
			case "1":
				return '<span class="badge rounded-pill bg-success">Opened</span>';
			case "2":
				return '<span class="badge rounded-pill bg-info">Answered</span>';
			case "3":
				return '<span class="badge rounded-pill bg-warning">Customer reply</span>';
			case "4":
				return '<span class="badge rounded-pill bg-secondary">Closed</span>';
		}
	}

	function getRowStatus($status){
		// 0=No, 1=Yes
		switch ($status){
			case "1":
				return '<span class="badge rounded-pill bg-success">Yes</span>';
			case "0":
				return '<span class="badge rounded-pill bg-danger">No</span>';
		}
	}

	function format($number, $places=2){
		return number_format((float)$number, $places, '.', ',');
	}

	function strStartWith( $str, $match ) {
		$length = strlen( $match );
		return substr( $str, 0, $length ) === $match;
	}
	function strEndWith( $str, $match ) {
		$length = strlen( $match );
		if( !$length ) {
			return true;
		}
		return substr( $str, -$match ) === $match;
	}

	function getDateOnly(){
		return date('Y-m-d');
	}

	function getDateTime(){
		return date('Y-m-d H:i:s');
	}

	function commonDateFormat($date){
		return date("D, d M Y", strtotime($date));
	}

	function getDateAddDay($day){
		$date =  date_create(date('Y-m-d'));
		date_modify($date, '+'.$day.' day');
		return date_format($date, 'Y-m-d');
	}

	function getDateAddMonth($month){
		$date = date_create(date('Y-m-d'));
		date_modify($date, '+'.$month.' month');
		return date_format($date, 'Y-m-d');
	}

	function getDateAddYear($year){
		$date = date_create(date('Y-m-d'));
		date_modify($date, '+'.$year.' year');
		return date_format($date, 'Y-m-d');
	}

	function safe_encode($str){
		return urlencode(base64_encode($str));
	}

	function safe_decode($str){
		return base64_decode(urldecode($str));
	}

	function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	function getAppSettings(){
		$ci = & get_instance();
		$ci->load->database();
		$query = $ci->db->query("SELECT * FROM app_settings LIMIT 1");
		$data = $query->result();
		return !empty($data) ? $data[0] : null;
	}

	function buildSuccessResponse($data, $msg){
		$resp["code"] = 200;
		$resp["msg"] = $msg;
		$resp["data"] = $data;
		return $resp;
	}

	function buildFailedResponse($msg){
		$resp["code"] = 500;
		$resp["msg"] = $msg;
		$resp["data"] = null;
		return $resp;
	}

	function pr($arr){
		echo "<pre>";
		print_r($arr);
		echo "<pre>";
	}

	if ( ! function_exists('admin_url'))
	{
		function admin_url($uri = '', $protocol = NULL)
		{
			return base_url();
		}
	}

	/**
	 * CSRF Field - Outputs hidden input field with CSRF token
	 * Used for forms to include CSRF protection
	 * Usage: <?=csrf_field()?>
	 */
	if ( ! function_exists('csrf_field'))
	{
		function csrf_field()
		{
			$ci = & get_instance();
			$csrf = array(
				'name' => $ci->security->get_csrf_token_name(),
				'hash' => $ci->security->get_csrf_hash()
			);
			return '<input type="hidden" name="'.$csrf['name'].'" value="'.$csrf['hash'].'" />';
		}
	}

	/**
	 * CSRF Meta - Outputs meta tags with CSRF token for AJAX requests
	 * Usage in header: <?=csrf_meta()?>
	 * Usage in JS: var csrfName = $('meta[name="csrf-token-name"]').attr('content');
	 *              var csrfHash = $('meta[name="csrf-token-hash"]').attr('content');
	 */
	if ( ! function_exists('csrf_meta'))
	{
		function csrf_meta()
		{
			$ci = & get_instance();
			$csrf = array(
				'name' => $ci->security->get_csrf_token_name(),
				'hash' => $ci->security->get_csrf_hash()
			);
			return '<meta name="csrf-token-name" content="'.$csrf['name'].'" />
<meta name="csrf-token-hash" content="'.$csrf['hash'].'" />';
		}
	}

	/**
	 * Send HTML email via raw SMTP socket connection.
	 * Bypasses CI3 email library to avoid line-length wrapping issues
	 * that break long URLs (e.g., password reset links).
	 *
	 * @param string $to        Recipient email
	 * @param string $subject   Email subject
	 * @param string $htmlBody  HTML email body
	 * @param string|null $fromEmail  Sender email (defaults to app_settings)
	 * @param string|null $fromName   Sender name (defaults to app_settings)
	 * @return bool True on success, false on failure
	 */
	if ( ! function_exists('sendHtmlEmail'))
	{
		function sendHtmlEmail($to, $subject, $htmlBody, $fromEmail = null, $fromName = null)
		{
			$appSettings = getAppSettings();

			$smtpHost   = $appSettings->smtp_host;
			$smtpPort   = (int) $appSettings->smtp_port;
			$smtpUser   = $appSettings->smtp_username;
			$smtpPass   = $appSettings->smtp_authkey;
			$smtpCrypto = !empty($appSettings->smtp_crypto) ? $appSettings->smtp_crypto : 'tls';

			$fromEmail = $fromEmail ?: $appSettings->email;
			$fromName  = $fromName  ?: $appSettings->company_name;

			// Build MIME message
			$boundary = md5(uniqid(time()));
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "From: " . mb_encode_mimeheader($fromName, 'UTF-8') . " <{$fromEmail}>\r\n";
			$headers .= "To: <{$to}>\r\n";
			$headers .= "Subject: " . mb_encode_mimeheader($subject, 'UTF-8') . "\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$headers .= "Content-Transfer-Encoding: base64\r\n";

			// Base64 encode the body to avoid any line-length issues
			$encodedBody = chunk_split(base64_encode($htmlBody), 76, "\r\n");

			$message = $headers . "\r\n" . $encodedBody;

			// Determine connection prefix
			$prefix = '';
			if ($smtpCrypto === 'ssl') {
				$prefix = 'ssl://';
			}

			$errno  = 0;
			$errstr = '';
			$socket = @fsockopen($prefix . $smtpHost, $smtpPort, $errno, $errstr, 30);

			if (!$socket) {
				log_message('error', "sendHtmlEmail: Could not connect to {$smtpHost}:{$smtpPort} - {$errstr}");
				return false;
			}

			// Helper to read SMTP response
			$getResponse = function() use ($socket) {
				$response = '';
				while ($line = fgets($socket, 512)) {
					$response .= $line;
					// If 4th char is a space, it's the last line of the response
					if (isset($line[3]) && $line[3] === ' ') break;
				}
				return $response;
			};

			// Helper to send command and check response
			$sendCmd = function($cmd, $expectedCode) use ($socket, $getResponse) {
				fwrite($socket, $cmd . "\r\n");
				$resp = $getResponse();
				if ((int)substr($resp, 0, 3) !== $expectedCode) {
					log_message('error', "sendHtmlEmail: SMTP error on '{$cmd}' - Response: " . trim($resp));
					return false;
				}
				return $resp;
			};

			// Read server greeting
			$getResponse();

			// EHLO
			if ($sendCmd('EHLO ' . gethostname(), 250) === false) {
				fclose($socket);
				return false;
			}

			// STARTTLS if needed
			if ($smtpCrypto === 'tls') {
				if ($sendCmd('STARTTLS', 220) === false) {
					fclose($socket);
					return false;
				}
				if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
					log_message('error', 'sendHtmlEmail: STARTTLS crypto negotiation failed');
					fclose($socket);
					return false;
				}
				// Re-EHLO after STARTTLS
				if ($sendCmd('EHLO ' . gethostname(), 250) === false) {
					fclose($socket);
					return false;
				}
			}

			// AUTH LOGIN
			if ($sendCmd('AUTH LOGIN', 334) === false) {
				fclose($socket);
				return false;
			}
			if ($sendCmd(base64_encode($smtpUser), 334) === false) {
				fclose($socket);
				return false;
			}
			if ($sendCmd(base64_encode($smtpPass), 235) === false) {
				fclose($socket);
				return false;
			}

			// MAIL FROM
			if ($sendCmd('MAIL FROM:<' . $fromEmail . '>', 250) === false) {
				fclose($socket);
				return false;
			}

			// RCPT TO
			if ($sendCmd('RCPT TO:<' . $to . '>', 250) === false) {
				fclose($socket);
				return false;
			}

			// DATA
			if ($sendCmd('DATA', 354) === false) {
				fclose($socket);
				return false;
			}

			// Send message body (dot-stuffing: lines starting with . get an extra .)
			$lines = explode("\r\n", $message);
			foreach ($lines as $line) {
				if (isset($line[0]) && $line[0] === '.') {
					$line = '.' . $line;
				}
				fwrite($socket, $line . "\r\n");
			}

			// End DATA with <CRLF>.<CRLF>
			if ($sendCmd('.', 250) === false) {
				fclose($socket);
				return false;
			}

			// QUIT
			$sendCmd('QUIT', 221);
			fclose($socket);

			return true;
		}
	}

	/**
	 * SECURITY: Sanitize HTML content for safe display
	 *
	 * Allows safe HTML tags (formatting, links, lists) while removing
	 * dangerous elements (scripts, event handlers, javascript: URLs).
	 * Use this for rich text content from editors like Quill.
	 *
	 * @param string $html The HTML content to sanitize
	 * @return string Sanitized HTML safe for display
	 *
	 * Usage:
	 *   <?= sanitize_html($ticket['message']) ?>
	 */
	if ( ! function_exists('sanitize_html'))
	{
		function sanitize_html($html)
		{
			if (empty($html)) {
				return '';
			}

			// Escape dangerous tags so they display as text instead of being removed
			// This allows users to see what was written without executing it
			$dangerous_tags = array('script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'style', 'link', 'meta', 'base');
			foreach ($dangerous_tags as $tag) {
				// Escape opening tags
				$html = preg_replace('/<\s*' . $tag . '(\s|>)/i', '&lt;' . $tag . '$1', $html);
				// Escape closing tags
				$html = preg_replace('/<\s*\/\s*' . $tag . '\s*>/i', '&lt;/' . $tag . '&gt;', $html);
			}

			// Allowed tags (Quill editor output + common formatting)
			$allowed_tags = '<p><br><strong><b><em><i><u><s><strike><a><blockquote><pre><code><ul><ol><li><h1><h2><h3><h4><h5><h6><sub><sup><span><div>';

			// Strip all non-allowed tags (dangerous ones are already escaped above)
			$html = strip_tags($html, $allowed_tags);

			// Remove dangerous attributes using regex
			// Remove event handlers (onclick, onerror, onload, etc.)
			$html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
			$html = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $html);

			// Remove javascript: and data: URLs in href/src attributes
			$html = preg_replace('/href\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', 'href="#"', $html);
			$html = preg_replace('/href\s*=\s*["\']?\s*data:[^"\'>\s]*/i', 'href="#"', $html);
			$html = preg_replace('/src\s*=\s*["\']?\s*javascript:[^"\'>\s]*/i', 'src=""', $html);
			$html = preg_replace('/src\s*=\s*["\']?\s*data:[^"\'>\s]*/i', 'src=""', $html);

			// Remove style attributes with expression/javascript
			$html = preg_replace('/style\s*=\s*["\'][^"\']*expression\s*\([^"\']*["\']/i', '', $html);
			$html = preg_replace('/style\s*=\s*["\'][^"\']*javascript:[^"\']*["\']/i', '', $html);

			return $html;
		}
	}

	/**
	 * SECURITY: Generate secure random password
	 *
	 * Generates a cryptographically secure random password with configurable length
	 * and character set options.
	 *
	 * @param int $length Length of the password (default: 16)
	 * @param bool $include_special Include special characters (default: true)
	 * @return string Secure random password
	 *
	 * Usage:
	 *   $password = generate_secure_password();           // 16 chars with special chars
	 *   $password = generate_secure_password(12);         // 12 chars with special chars
	 *   $password = generate_secure_password(12, false);  // 12 chars without special chars
	 */
	if ( ! function_exists('generate_secure_password'))
	{
		function generate_secure_password($length = 16, $include_special = true)
		{
			// Use cryptographically secure random bytes
			$random_bytes = random_bytes($length);

			// Define character sets
			$uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';  // Removed I, O for clarity
			$lowercase = 'abcdefghjkmnpqrstuvwxyz';   // Removed i, l, o for clarity
			$numbers = '23456789';                     // Removed 0, 1 for clarity
			$special = '!@#$%^&*()-_=+';

			// Build character pool
			$chars = $uppercase . $lowercase . $numbers;
			if ($include_special) {
				$chars .= $special;
			}

			$chars_length = strlen($chars);
			$password = '';

			// Generate password ensuring at least one character from each set
			// First, add one from each required set
			$password .= $uppercase[ord($random_bytes[0]) % strlen($uppercase)];
			$password .= $lowercase[ord($random_bytes[1]) % strlen($lowercase)];
			$password .= $numbers[ord($random_bytes[2]) % strlen($numbers)];

			if ($include_special) {
				$password .= $special[ord($random_bytes[3]) % strlen($special)];
				$start_pos = 4;
			} else {
				$start_pos = 3;
			}

			// Fill the rest with random characters
			for ($i = $start_pos; $i < $length; $i++) {
				$password .= $chars[ord($random_bytes[$i]) % $chars_length];
			}

			// Shuffle the password to randomize character positions
			$password = str_shuffle($password);

			return $password;
		}
	}

?>
