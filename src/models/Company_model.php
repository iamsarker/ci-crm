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

	/**
	 * Move a customer from one reseller to another (v2.0.0 req. 9).
	 *
	 * Rewrites companies.parent_company_id, which changes who bills the
	 * customer and — once the wallet lands — whose credit is debited. That is a
	 * money-affecting change with no other trace, so it also writes a
	 * company_transfers audit row.
	 *
	 * Transfer moves FUTURE billing only. Existing order_* rows keep the prices
	 * frozen on them at checkout, and any in-flight wallet debit stays with the
	 * old reseller; nothing is re-priced retroactively.
	 *
	 * @param int $companyId          Customer being moved
	 * @param int $newResellerId      Target reseller's companies.id; 0 = back to platform-direct
	 * @param string $notes
	 * @return array ['success' => bool, 'message' => string]
	 */
	function transferToReseller($companyId, $newResellerId, $notes = '') {
		$companyId     = intval($companyId);
		$newResellerId = intval($newResellerId);

		$company = $this->db->query(
			"SELECT id, name, parent_company_id, is_reseller FROM companies WHERE id = ? AND status = 1 LIMIT 1",
			array($companyId)
		)->row_array();

		if (empty($company)) {
			return array('success' => false, 'message' => 'Customer not found.');
		}
		// Req. 6: no sub-resellers. A reseller cannot be parked under another.
		if (intval($company['is_reseller']) === 1) {
			return array('success' => false, 'message' => 'A reseller cannot be moved under another reseller.');
		}
		if ($newResellerId === $companyId) {
			return array('success' => false, 'message' => 'A customer cannot be its own reseller.');
		}

		if ($newResellerId > 0) {
			$target = $this->db->query(
				"SELECT c.id FROM companies c
				   JOIN reseller_profiles rp ON rp.company_id = c.id
				  WHERE c.id = ? AND c.status = 1 AND c.is_reseller = 1 AND rp.status = 1 LIMIT 1",
				array($newResellerId)
			)->row_array();
			if (empty($target)) {
				return array('success' => false, 'message' => 'Target is not an active reseller.');
			}
		}

		$from = intval($company['parent_company_id']);
		if ($from === $newResellerId) {
			return array('success' => false, 'message' => 'That customer already belongs to this reseller.');
		}

		$this->db->trans_start();

		$this->db->query(
			"UPDATE companies SET parent_company_id = ?, updated_on = ?, updated_by = ? WHERE id = ?",
			array($newResellerId, getDateTime(), getAdminId(), $companyId)
		);

		$this->db->insert('company_transfers', array(
			'company_id'      => $companyId,
			'from_company_id' => $from,
			'to_company_id'   => $newResellerId,
			'notes'           => substr((string) $notes, 0, 255),
			'status'          => 1,
			'inserted_on'     => getDateTime(),
			'inserted_by'     => getAdminId(),
		));

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return array('success' => false, 'message' => 'Transfer failed.');
		}
		return array('success' => true, 'message' => 'Customer transferred successfully.');
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
			// SECURITY: bypasses the $where that ssp_sql_query() scopes. Note the
			// scope column for `companies` is `id` (the reseller row itself plus
			// its sub-customers), not `company_id`.
			$scope = adminScopeSql('id');
			$scope = ($scope !== '') ? " AND {$scope}" : '';
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM ".$this->table." WHERE status=1 {$scope}");
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

	/**
	 * Get company statistics for dashboard cards
	 *
	 * @return array Stats including total, active, this month counts and countries count
	 */
	function getCompanyStats() {
		try {
			// SECURITY: this query has no WHERE at all, so before scoping it
			// counted every company on the platform regardless of tenant.
			$scope = adminScopeSql('id');
			$scope = ($scope !== '') ? " WHERE {$scope}" : '';
			$query = $this->db->query("
				SELECT
					COUNT(*) as total_companies,
					SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_companies,
					SUM(CASE WHEN status = 1 AND YEAR(inserted_on) = YEAR(CURDATE()) AND MONTH(inserted_on) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month_companies,
					COUNT(DISTINCT CASE WHEN status = 1 AND country IS NOT NULL AND country != '' THEN country END) as countries_count
				FROM ".$this->table." {$scope}
			");
			$data = $query->row_array();
			return array(
				'total_companies' => intval($data['total_companies'] ?? 0),
				'active_companies' => intval($data['active_companies'] ?? 0),
				'this_month_companies' => intval($data['this_month_companies'] ?? 0),
				'countries_count' => intval($data['countries_count'] ?? 0)
			);
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getCompanyStats', $this->db->last_query(), $e->getMessage());
			return array(
				'total_companies' => 0,
				'active_companies' => 0,
				'this_month_companies' => 0,
				'countries_count' => 0
			);
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
					os.due_date,
					os.next_renewal_date,
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
