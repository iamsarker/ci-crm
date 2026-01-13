<?php
class Adminauth_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function doLogin($email, $password) {
		$return = array();

		$sql = "SELECT id, admin_role_id, first_name, last_name, username, password, email, mobile, phone, designation, signature, support_depts, profile_pic FROM admin_users WHERE (username='$email' or email='$email') and status = 1";
		$query = $this->db->query($sql);
		if ($query->num_rows() == 0){
			$return['status_code'] = -1;
			return $return;
		}

		$userdata = $query->row();
		if (password_verify($password, $userdata->password)) {
			$resp = array();
			$resp['id'] = $userdata->id;
			$resp['first_name'] = $userdata->first_name;
			$resp['last_name'] = $userdata->last_name;
			$resp['email'] = $userdata->email;
			$resp['mobile'] = $userdata->mobile;
			$resp['phone'] = $userdata->phone;
			$resp['designation'] = $userdata->designation;
			$resp['signature'] = $userdata->signature;
			$resp['support_depts'] = $userdata->support_depts;
			$resp['profile_pic'] = $userdata->profile_pic;
			$resp['terminal'] = getClientIp();

			$logins = array();
			$logins['admin_id'] = $userdata->id;
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
		if ($this->db->insert('admin_logins', $data)) {
		}
	}

 	function countDbSession($user_id){
		$this->db->select('id');
		$this->db->from('admin_logins');
		$this->db->where(array(
			'admin_id'=>$user_id,
			'active'=>1
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}

	function isEmailExists($email){
		$this->db->select('id');
		$this->db->from('admin_users');
		$this->db->where(array(
			'email'=>$email
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}
}
?>
