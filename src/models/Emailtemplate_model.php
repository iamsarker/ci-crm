<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emailtemplate_model extends CI_Model {
	var $table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "email_templates";
	}

	/**
	 * Load all email templates
	 */
	function loadAllData() {
		$sql = "SELECT * FROM {$this->table} WHERE deleted_on IS NULL ORDER BY category ASC, template_name ASC";
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Load templates by category
	 */
	function loadByCategory($category) {
		$sql = "SELECT id, template_key, template_name FROM {$this->table} WHERE category = ? AND status = 1 AND deleted_on IS NULL ORDER BY template_name ASC";
		return $this->db->query($sql, array($category))->result_array();
	}

	/**
	 * Get single template by ID
	 */
	function getDetail($id) {
		if (!is_numeric($id) || $id < 0) {
			return array();
		}
		$sql = "SELECT * FROM {$this->table} WHERE id = ? AND deleted_on IS NULL";
		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Get template by key
	 */
	function getByKey($template_key) {
		$sql = "SELECT * FROM {$this->table} WHERE template_key = ? AND status = 1 AND deleted_on IS NULL";
		$data = $this->db->query($sql, array($template_key))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Check if template_key already exists (excluding given ID for updates)
	 */
	function isKeyExists($template_key, $exclude_id = 0) {
		$sql = "SELECT id FROM {$this->table} WHERE template_key = ? AND deleted_on IS NULL";
		$params = array($template_key);

		if ($exclude_id > 0) {
			$sql .= " AND id != ?";
			$params[] = intval($exclude_id);
		}

		$data = $this->db->query($sql, $params)->result_array();
		return !empty($data);
	}

	/**
	 * Save email template (insert or update)
	 */
	function saveData($data) {
		$return = array('success' => 0);

		try {
			if (!empty($data['id'])) {
				$id = $data['id'];
				unset($data['id']);
				$this->db->where('id', intval($id));
				if ($this->db->update($this->table, $data)) {
					$return['success'] = 1;
					$return['id'] = $id;
				}
			} else {
				unset($data['id']);
				if ($this->db->insert($this->table, $data)) {
					$return['success'] = 1;
					$return['id'] = $this->db->insert_id();
				}
			}
		} catch (Exception $e) {
			log_message('error', 'Emailtemplate_model::saveData - ' . $e->getMessage());
			$return['success'] = 0;
		}

		return $return;
	}

	/**
	 * Soft delete email template
	 */
	function deleteData($id) {
		$return = array('success' => 0);

		if (!is_numeric($id) || $id < 0) {
			return $return;
		}

		try {
			$data = array(
				'deleted_on' => date('Y-m-d H:i:s'),
				'deleted_by' => getAdminId()
			);
			$this->db->where('id', intval($id));
			if ($this->db->update($this->table, $data)) {
				$return['success'] = 1;
			}
		} catch (Exception $e) {
			log_message('error', 'Emailtemplate_model::deleteData - ' . $e->getMessage());
		}

		return $return;
	}

	/**
	 * SSP: Get records for DataTable
	 */
	function getDataTableRecords($query, $bindings) {
		return $this->db->query($query, $bindings)->result_array();
	}

	/**
	 * SSP: Count total records
	 */
	function countDataTableTotalRecords() {
		$sql = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE deleted_on IS NULL";
		$data = $this->db->query($sql)->result_array();
		return !empty($data) ? intval($data[0]['cnt']) : 0;
	}

	/**
	 * SSP: Count filtered records
	 */
	function countDataTableFilterRecords($where, $bindings) {
		$sql = "SELECT COUNT(*) as cnt FROM {$this->table} $where";
		$data = $this->db->query($sql, $bindings)->result_array();
		return !empty($data) ? intval($data[0]['cnt']) : 0;
	}

	/**
	 * Get all distinct categories
	 */
	function getCategories() {
		$sql = "SELECT DISTINCT category FROM {$this->table} WHERE deleted_on IS NULL ORDER BY category ASC";
		return $this->db->query($sql)->result_array();
	}
}
?>
