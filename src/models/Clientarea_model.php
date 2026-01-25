<?php
class Clientarea_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadSummaryData($id) {
		// SECURITY: Validate input to prevent SQL injection
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$id = intval($id);

		$sql = "SELECT COUNT(id) cnt FROM order_services WHERE company_id = ? UNION ALL
				SELECT COUNT(id) cnt FROM order_domains WHERE company_id = ? UNION ALL
				SELECT COUNT(id) cnt FROM tickets WHERE company_id = ? UNION ALL
				SELECT COUNT(id) cnt FROM invoices WHERE company_id = ?";

		$data = $this->db->query($sql, array($id, $id, $id, $id))->result_array();

		return $data;
	}

	function getServerDnsInfo($id) {
		// SECURITY: Validate input to prevent SQL injection
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$sql = "SELECT s.name, s.dns1, s.dns2, s.dns3, s.dns4, s.ip_addr primar_ip
			FROM product_service_pricing psp
			JOIN product_services ps ON psp.product_service_id = ps.id
			JOIN servers s ON ps.server_id = s.id
			WHERE psp.id = ? LIMIT 1";

		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return $data;
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
