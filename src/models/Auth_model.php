<?php
class Auth_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function doLogin($email, $password) {
		$return = array();
		$sql = "SELECT * FROM `users` WHERE users.email='$email'";
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0){
			$return['status_code'] = -2;
			return $return;
		}

		$sql = "SELECT u.*, c.name company, c.address, c.city, c.state, c.zip_code, c.country FROM users u join companies c on u.company_id=c.id WHERE u.email='$email' and u.status = 1 and c.status=1";
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0){
			$return['status_code'] = -1;
			return $return;
		}

		$userdata = $query->row();
		if (password_verify($password,$userdata->password)) {
			$resp = array();
			$resp['id'] = $userdata->id;
			$resp['first_name'] = $userdata->first_name;
			$resp['last_name'] = $userdata->last_name;
			$resp['email'] = $userdata->email;
			$resp['mobile'] = $userdata->mobile;
			$resp['phone'] = $userdata->phone;
			$resp['address'] = $userdata->address;
			$resp['zip_code'] = $userdata->zip_code;
			$resp['city'] = $userdata->city;
			$resp['state'] = $userdata->state;
			$resp['country'] = $userdata->country;
			$resp['company'] = $userdata->company;
			$resp['designation'] = $userdata->designation;
			$resp['user_type'] = $userdata->user_type;
			$resp['company_id'] = $userdata->company_id;
			$resp['profile_pic'] = $userdata->profile_pic;
			$resp['terminal'] = getClientIp();

			$logins = array();
			$logins['user_id'] = $userdata->id;
			$logins['login_time'] = getDateTime();
			$logins['session_val'] = 0;
			$logins['terminal'] = $resp['terminal'];
			$this->saveUserLogins($logins);
			$return['status_code'] = 1;
			$return['data'] = $resp;
			return $return;
		}
		$return['status_code'] = 0;
		return $return;
 	}

 	function saveUserLogins($data){
 		$data['active'] = 1;
 		if ($this->db->insert('user_logins', $data)) {
		}
 	}

	function newRegistration($data) {
		$return = array();

		$data['status'] = "2";
		$data['inserted_on'] = getDateTime();
		//$data['terminal'] = getClientIp();

		$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);


		$uniqueVarificationCode = uniqid().$data['email'].uniqid().time();
		$verification_code = hash('sha256', 'TB'.$uniqueVarificationCode);
		$data['verify_hash'] = $verification_code;

		if ($this->db->insert('users', $data)) {
			$return['success'] = 1;
			$return['email'] = $data['email'];
			$return['verification_code'] = $verification_code;
		} else {
			$return['success'] = 0;
		};
		return $return;
 	}


	public function forgetpaswrd($username)
	{
		$this->db->select('email');
		$this->db->from('users');
		$this->db->where('email', $username);
		$query=$this->db->get();
		if ($query->num_rows()){
			$this->sendpassword($username);

//			var_dump($query->num_rows());
//			exit();
		}


		return 0;


	}


	 function sendpassword($email) {
		 $return = array();


		$query1 = $this->db->query("SELECT *  from users where email = '" . $email . "'");
		$row = $query1->result();


		if ($query1->num_rows() > 0) {
//			$passwordplain = rand(999999999, 9999999999);
			$newpass = password_hash(1, PASSWORD_DEFAULT);

			$sql = "UPDATE `users` SET `password` = '".$newpass."' WHERE `users`.`email` = '".$email."'";

			$query = $this->db->query($sql);
			if ($query){
				$this->sendEditPaidInvoiceEmail($row,"yeasinalicse@gmail.com");

//				if (!$mail->send()) {
//					$return['status_code'] = 0;
//					$return['msg'] = "Failed to send password, please try again!";
//				} else {
//					echo $this->email->print_debugger();
//					$return['status_code'] = 1;
//					$return['msg'] = "Password sent to your email!";
//				}
			}else {
				$return['status_code'] = -1;
				$return['msg'] = "Unable to update the password";
			}
//



			return $return;

		}
	}

	function sendEditPaidInvoiceEmail($info, $emailTo){
		$appSettings = $this->db->query("SELECT *  from app_settings");
		$rowData = $appSettings->result();
		$this->load->library('email');
		$this->email->clear();

		$config = Array(
			'wordwrap' => TRUE,
			'smtp_host' => $rowData[0]->smtp_host,
			'smtp_port' => $rowData[0]->smtp_port,
			'smtp_user' => $rowData[0]->smtp_username,
			'smtp_pass' => $rowData[0]->smtp_authkey,
			'mailtype'  => 'html'
		);
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->from($rowData[0]->email, 'Tong Bari');

		$emailList = array(
			$emailTo,
			$rowData[0]->email,
		);

		$this->email->to($emailList);
		$this->email->subject("Update your password - Tong Bari");
		$passwordplain = 123;
//		$body = $this->load->view('email/email_demo', $info, TRUE);
		$body='Dear ,'. "\r\n";
		$body.='Thanks for contacting us regarding forgot password,<br> Your <b>Password</b> is <b>'.$passwordplain.'</b>'."\r\n";
		$body.='<br>Please Update your password.';
		$body.='<br>Thanks & Regards';
		$body.='<br>Tong Bari';

		$this->email->message($body);

		if( $this->email->send() ){
			//$this->session->set_flashdata('alert', successAlert('Invoice has been created successfully'));
		} else{
			echo "0";
		}
	}

 	function verifyUser($verificationCode){
 		$return = 0;

		$this->db->select('id');
		$this->db->from('users');
		$this->db->where(array(
			'verify_hash'=>$verificationCode,
			'status'=>'2'
		));
		$num_results = $this->db->count_all_results();
		if ($num_results == 1) {
			$data['status'] = '1';
			$this->db->where('verify_hash', $verificationCode);
			if($this->db->update('users', $data)) {
				$return = 1;
			}
		}
		return $return;
 	}

 	function countDbSession($user_id){
		$this->db->select('id');
		$this->db->from('user_logins');
		$this->db->where(array(
			'user_id'=>$user_id,
			'active'=>1
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}

	function isEmailExists($email){
		$this->db->select('id');
		$this->db->from('users');
		$this->db->where(array(
			'email'=>$email
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}
}
?>
