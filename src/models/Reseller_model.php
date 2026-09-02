<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reseller_model
 * -------------------------------------------------------------------------
 * Backs the admin "Reseller Management" section. A reseller is a `companies`
 * row flagged is_reseller=1 that owns a single `reseller_profiles` row
 * (discount / credit / API flag) plus zero or more sub-customer companies
 * (companies.parent_company_id = the reseller's company id).
 *
 * The list DataTable reads `reseller_view` (profiles + company + live counts).
 *
 * @see reseller_api_migration.sql, crm_db_views.sql (reseller_view)
 */
class Reseller_model extends CI_Model {

	var $table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "reseller_profiles";
	}

	// ─── CRUD ────────────────────────────────────────────────

	function getDetail($id) {
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}
		$sql = "SELECT rp.*, c.name AS company_name, c.email AS company_email
				FROM {$this->table} rp
				JOIN companies c ON c.id = rp.company_id
				WHERE rp.id = ? AND rp.status = 1";
		$data = $this->db->query($sql, array(intval($id)))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Profile for a company, INCLUDING soft-deleted ones (status = 0).
	 *
	 * ⚠️ Deliberately not filtered on status. delete_records() soft-deletes the
	 * row but keeps it, and `uniq_reseller_company` is a real UNIQUE index — so
	 * a status-filtered lookup finds nothing, manage() takes the INSERT branch,
	 * and re-adding a previously removed reseller dies on a duplicate key.
	 * Callers that need "is this an ACTIVE reseller" must check status
	 * themselves; manage() reactivates instead of inserting.
	 */
	function getByCompany($companyId) {
		$sql = "SELECT * FROM {$this->table} WHERE company_id = ?";
		$data = $this->db->query($sql, array(intval($companyId)))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return = array('id' => 0);
		if (!empty($data['id']) && $data['id'] > 0) {
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
	}

	function getLastId() {
		$sql = "SELECT id FROM {$this->table} ORDER BY id DESC LIMIT 1";
		$data = $this->db->query($sql)->result_array();
		return !empty($data) ? $data[0]['id'] : 0;
	}

	// ─── Reseller flag + hierarchy ──────────────────────────

	/** Mark / unmark a company as a reseller. */
	function setResellerFlag($companyId, $flag) {
		$this->db->where('id', intval($companyId));
		$this->db->update('companies', array('is_reseller' => $flag ? 1 : 0));
	}

	/** Companies currently assigned under this reseller. */
	function getSubCustomers($resellerCompanyId) {
		$sql = "SELECT id, name, email, status FROM companies
				WHERE parent_company_id = ? AND status = 1
				ORDER BY name";
		return $this->db->query($sql, array(intval($resellerCompanyId)))->result_array();
	}

	/**
	 * Companies eligible to be assigned as sub-customers of a reseller:
	 * active, not a reseller itself, not the reseller company, and either
	 * unassigned (parent = 0) or already assigned to THIS reseller.
	 */
	function getAssignableCompanies($resellerCompanyId) {
		$rid = intval($resellerCompanyId);
		$sql = "SELECT id, name, email FROM companies
				WHERE status = 1
				  AND is_reseller = 0
				  AND id <> ?
				  AND (parent_company_id = 0 OR parent_company_id = ?)
				ORDER BY name";
		return $this->db->query($sql, array($rid, $rid))->result_array();
	}

	/**
	 * Set the sub-customers of a reseller. Assigns parent_company_id for the
	 * selected companies and detaches any previously-assigned company that is
	 * no longer selected. No-op for a reseller company id of 0.
	 */
	function assignSubCustomers($resellerCompanyId, $companyIds) {
		$rid = intval($resellerCompanyId);
		if ($rid <= 0) return;

		$companyIds = array_filter(array_map('intval', (array) $companyIds));

		// Detach previously-linked companies that are no longer selected.
		$this->db->where('parent_company_id', $rid);
		if (!empty($companyIds)) {
			$this->db->where_not_in('id', $companyIds);
		}
		$this->db->update('companies', array('parent_company_id' => 0));

		// Attach the selected companies (guard: never nest a reseller/self).
		if (!empty($companyIds)) {
			$this->db->where_in('id', $companyIds);
			$this->db->where('id <>', $rid);
			$this->db->where('is_reseller', 0);
			$this->db->update('companies', array('parent_company_id' => $rid));
		}
	}

	// ─── DataTable ───────────────────────────────────────────

	function getDataTableRecords($sqlQuery, $bindings) {
		$results = $this->db->query($sqlQuery, $bindings)->result_array();
		foreach ($results as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}
		return $results;
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("SELECT COUNT(id) as cnt FROM reseller_view WHERE status = 1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("SELECT COUNT(id) as cnt FROM reseller_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	// ─── Stats ───────────────────────────────────────────────

	function getStats() {
		$stats = array('total' => 0, 'active' => 0, 'sub_customers' => 0, 'api_keys' => 0);

		$row = $this->db->query("SELECT COUNT(*) AS total,
					SUM(CASE WHEN allow_api = 1 THEN 1 ELSE 0 END) AS api_enabled
				FROM {$this->table} WHERE status = 1")->row_array();
		if (!empty($row)) {
			$stats['total']  = intval($row['total']);
			$stats['active'] = intval($row['total']);
		}

		$sub = $this->db->query("SELECT COUNT(*) AS cnt FROM companies
				WHERE parent_company_id > 0 AND status = 1")->row_array();
		$stats['sub_customers'] = !empty($sub) ? intval($sub['cnt']) : 0;

		$keys = $this->db->query("SELECT COUNT(*) AS cnt FROM api_keys WHERE status = 1")->row_array();
		$stats['api_keys'] = !empty($keys) ? intval($keys['cnt']) : 0;

		return $stats;
	}

	// ─── Dropdown Helpers ────────────────────────────────────

	/**
	 * Companies selectable as the reseller account. When editing, the current
	 * company is always included; otherwise only non-reseller companies show.
	 */
	function getSelectableCompanies($includeCompanyId = 0) {
		$includeCompanyId = intval($includeCompanyId);
		$sql = "SELECT id, name AS company_name, email FROM companies
				WHERE status = 1 AND (is_reseller = 0 OR id = ?)
				ORDER BY name";
		return $this->db->query($sql, array($includeCompanyId))->result_array();
	}

	function getCurrencies() {
		$sql = "SELECT id, code AS currency_code, symbol AS currency_name FROM currencies WHERE status = 1 ORDER BY code";
		return $this->db->query($sql)->result_array();
	}

	// ─── Reseller admin login (v2.0.0) ───────────────────────
	//
	// A reseller signs in through the ADMIN login page, so it needs an
	// `admin_users` row bound to its company via admin_type=1 + company_id.
	// That binding is what every tenant scope check reads.

	/** The reseller admin row for a company, whatever its status. */
	function getAdminUser($companyId) {
		$sql = "SELECT * FROM admin_users WHERE admin_type = 1 AND company_id = ? ORDER BY id ASC LIMIT 1";
		$data = $this->db->query($sql, array(intval($companyId)))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Is this username/email already taken by a DIFFERENT admin?
	 * admin_users.username and .email are TEXT columns with no unique index, so
	 * uniqueness has to be enforced here or two admins can share a login and
	 * doLogin()'s `(username = ? or email = ?)` picks whichever sorts first.
	 */
	function adminLoginExists($username, $email, $excludeId = 0) {
		$sql = "SELECT id FROM admin_users
				WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1";
		$data = $this->db->query($sql, array($username, $email, intval($excludeId)))->result_array();
		return !empty($data);
	}

	/** Insert or update a reseller's admin login. Returns the admin_users.id. */
	function saveAdminUser($data) {
		if (!empty($data['id']) && intval($data['id']) > 0) {
			$id = intval($data['id']);
			unset($data['id']);
			$this->db->where('id', $id);
			$this->db->update('admin_users', $data);
			return $id;
		}
		$this->db->insert('admin_users', $data);
		return $this->db->insert_id();
	}

	/**
	 * Activate / deactivate every admin login belonging to a reseller.
	 * Paired with WHMAZADMIN_Controller::isLogin(), which re-checks liveness on
	 * each request, this is what makes "deactivate reseller" take effect
	 * immediately rather than whenever the session happens to expire.
	 */
	function setAdminUsersStatus($companyId, $status) {
		$this->db->query(
			"UPDATE admin_users SET status = ?, updated_on = ? WHERE admin_type = 1 AND company_id = ?",
			array(intval($status), getDateTime(), intval($companyId))
		);
	}
}
