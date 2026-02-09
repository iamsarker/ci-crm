<?php 
class Dashboard_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadSummaryData() {

		$sql = "SELECT COUNT(id) cnt FROM companies WHERE status=1 UNION ALL
				SELECT COUNT(id) cnt FROM orders WHERE status=1 UNION ALL 
				SELECT COUNT(id) cnt FROM tickets WHERE status=1 UNION ALL 
				SELECT COUNT(id) cnt FROM invoices WHERE status=1 ";

		$data = $this->db->query($sql)->result_array();
		
		return $data;
 	}

	 function getServerDnsInfo($id) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate input
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$sql = "SELECT s.name, s.dns1, s.dns2, s.dns3, s.dns4, s.ip_addr primar_ip
			FROM product_service_pricing psp
			JOIN product_services ps on psp.product_service_id=ps.id
			JOIN servers s on ps.server_id=s.id
			WHERE psp.id=? limit 0,1";

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

	/**
	 * Get top domain selling prices
	 * Returns domain extensions with their registration, transfer and renewal prices
	 */
	function getDomainPrices($limit = 10) {
		$sql = "SELECT de.extension, dp.price as reg_price, dp.transfer, dp.renewal,
					   c.code as currency_code, c.symbol as currency_symbol
				FROM dom_pricing dp
				JOIN dom_extensions de ON dp.dom_extension_id = de.id
				JOIN currencies c ON dp.currency_id = c.id
				WHERE dp.status = 1 AND de.status = 1
				ORDER BY de.extension ASC
				LIMIT ?";

		$data = $this->db->query($sql, array(intval($limit)))->result_array();
		return $data;
	}

	/**
	 * Get last 12 months expenses
	 * Returns monthly expense totals for chart display
	 */
	function getLast12MonthsExpenses() {
		$sql = "SELECT
					DATE_FORMAT(expense_date, '%Y-%m') as month_key,
					DATE_FORMAT(expense_date, '%b %Y') as month_label,
					SUM(exp_amount) as total_amount
				FROM expenses
				WHERE status = 1
					AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
				GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
				ORDER BY month_key ASC";

		$data = $this->db->query($sql)->result_array();

		// Fill in missing months with zero values
		$result = array();
		$labels = array();
		$amounts = array();

		// Generate last 12 months
		for ($i = 11; $i >= 0; $i--) {
			$monthKey = date('Y-m', strtotime("-$i months"));
			$monthLabel = date('M Y', strtotime("-$i months"));
			$labels[] = $monthLabel;

			// Find matching data
			$found = false;
			foreach ($data as $row) {
				if ($row['month_key'] == $monthKey) {
					$amounts[] = floatval($row['total_amount']);
					$found = true;
					break;
				}
			}
			if (!$found) {
				$amounts[] = 0;
			}
		}

		return array(
			'labels' => $labels,
			'amounts' => $amounts,
			'total' => array_sum($amounts)
		);
	}
}
?>
