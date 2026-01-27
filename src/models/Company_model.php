<?php 
class Company_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "companies";
	}

	function loadAllData() {
		try {
			$sql = "SELECT * FROM $this->table WHERE status=1 ";
			$data = $this->db->query($sql)->result_array();

			return $data;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('loadAllData', $this->db->last_query(), $e->getMessage());
			return array();
		}
 	}

	function getDetail($id) {
		// SECURITY FIX: Validate ID and use query builder to prevent SQL injection
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}

		try {
			$this->db->select('*');
			$this->db->from($this->table);
			$this->db->where('id', intval($id));
			$this->db->where('status', 1);
			$data = $this->db->get();

			if ($data && $data->num_rows() > 0) {
				return $data->row_array();
			} else {
				return array();
			}
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('getDetail', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function saveData($data) {
		$return['id'] = 0;

		try {
			if( !empty($data['id']) && $data['id'] > 0){
				$this->db->where('id', $data['id']);
				if ($this->db->update($this->table, $data)) {
					$return['id'] = $data['id'];
				}
			} else {
				if ($this->db->insert($this->table, $data)) {
					$return['id'] = $this->db->insert_id();
				}
			}

			return $return;
		} catch (Exception $e) {
			// SECURITY: Log database error with operation details
			$operation = (!empty($data['id']) && $data['id'] > 0) ? 'UPDATE' : 'INSERT';
			ErrorHandler::log_database_error('saveData - ' . $operation, $this->db->last_query(), $e->getMessage());
			return array('id' => 0, 'error' => true, 'message' => 'Database operation failed');
		}
 	}

	function getDataTableRecords($sqlQuery, $bindings) {
		try {
			$data = $this->db->query($sqlQuery, $bindings);
			$results = $data->result_array();

			return $results;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('getDataTableRecords', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function countDataTableTotalRecords() {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM ".$this->table." WHERE status=1");
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('countDataTableTotalRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function countDataTableFilterRecords($where, $bindings) {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM ".$this->table." $where", $bindings);
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('countDataTableFilterRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	// ============================================
	// Services DataTable Methods
	// ============================================

	function getServicesDataTableRecords($sqlQuery, $bindings) {
		try {
			$data = $this->db->query($sqlQuery, $bindings);
			return $data->result_array();
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getServicesDataTableRecords', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function countServicesDataTableTotalRecords($companyId) {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM order_services WHERE company_id = ?", array(intval($companyId)));
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countServicesDataTableTotalRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function countServicesDataTableFilterRecords($where, $bindings) {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM order_services $where", $bindings);
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countServicesDataTableFilterRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	// ============================================
	// Domains DataTable Methods
	// ============================================

	function getDomainsDataTableRecords($sqlQuery, $bindings) {
		try {
			$data = $this->db->query($sqlQuery, $bindings);
			return $data->result_array();
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getDomainsDataTableRecords', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function countDomainsDataTableTotalRecords($companyId) {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM order_domains WHERE company_id = ?", array(intval($companyId)));
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countDomainsDataTableTotalRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function countDomainsDataTableFilterRecords($where, $bindings) {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM order_domains $where", $bindings);
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countDomainsDataTableFilterRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	// ============================================
	// Service Management Methods (cPanel Integration)
	// ============================================

	/**
	 * Get service detail for management modal
	 * Includes product info, service type, and cp_package
	 * @param int $serviceId Service ID
	 * @param int $companyId Company ID for security validation
	 * @return array Service details
	 */
	function getServiceDetail($serviceId, $companyId) {
		if (!is_numeric($serviceId) || !is_numeric($companyId) || $serviceId <= 0 || $companyId <= 0) {
			return array();
		}

		try {
			$sql = "SELECT
					os.id,
					os.order_id,
					os.company_id,
					os.hosting_domain,
					os.cp_username,
					os.product_service_type_key,
					os.is_synced,
					os.status,
					os.first_pay_amount,
					os.recurring_amount,
					os.reg_date,
					os.next_due_date,
					ps.product_name as product_name,
					ps.cp_package,
					pst.key_name as product_type_key,
					pst.servce_type_name as product_type_name
				FROM order_services os
				LEFT JOIN product_service_pricing psp ON os.product_service_pricing_id = psp.id
				LEFT JOIN product_services ps ON psp.product_service_id = ps.id
				LEFT JOIN product_service_types pst ON ps.product_service_type_id = pst.id
				WHERE os.id = ? AND os.company_id = ?";

			$result = $this->db->query($sql, array(intval($serviceId), intval($companyId)))->row_array();

			return !empty($result) ? $result : array();
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getServiceDetail', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	/**
	 * Get service detail for cPanel operations (without company validation)
	 * Used by controller after admin authentication
	 * @param int $serviceId Service ID
	 * @return array Service details with server info
	 */
	function getServiceDetailForCpanel($serviceId) {
		if (!is_numeric($serviceId) || $serviceId <= 0) {
			return array();
		}

		try {
			$sql = "SELECT
					os.id,
					os.order_id,
					os.company_id,
					os.hosting_domain,
					os.cp_username,
					os.product_service_type_key,
					os.is_synced,
					os.status,
					ps.cp_package,
					ps.server_id
				FROM order_services os
				LEFT JOIN product_service_pricing psp ON os.product_service_pricing_id = psp.id
				LEFT JOIN product_services ps ON psp.product_service_id = ps.id
				WHERE os.id = ?";

			$result = $this->db->query($sql, array(intval($serviceId)))->row_array();

			return !empty($result) ? $result : array();
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getServiceDetailForCpanel', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	/**
	 * Update service record
	 * @param int $serviceId Service ID
	 * @param array $data Data to update
	 * @return bool Success status
	 */
	function updateService($serviceId, $data) {
		if (!is_numeric($serviceId) || $serviceId <= 0 || empty($data)) {
			return false;
		}

		try {
			$this->db->where('id', intval($serviceId));
			return $this->db->update('order_services', $data);
		} catch (Exception $e) {
			ErrorHandler::log_database_error('updateService', $this->db->last_query(), $e->getMessage());
			return false;
		}
	}
}
?>
