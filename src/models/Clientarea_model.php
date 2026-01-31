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

	function changePassword($userId, $currentPassword, $newPassword)
	{
		$sql = "SELECT password, first_name, email FROM users WHERE id = ? AND status = 1";
		$query = $this->db->query($sql, array($userId));

		if ($query->num_rows() == 0) {
			return ['success' => false, 'msg' => 'User not found.'];
		}

		$user = $query->row();

		if (!password_verify($currentPassword, $user->password)) {
			return ['success' => false, 'msg' => 'Current password is incorrect.'];
		}

		$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
		$sql = "UPDATE users SET password = ? WHERE id = ?";
		$this->db->query($sql, array($hashedPassword, $userId));

		return ['success' => true, 'email' => $user->email, 'first_name' => $user->first_name];
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
