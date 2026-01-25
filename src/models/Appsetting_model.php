<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Appsetting_model extends CI_Model {
	var $table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "app_settings";
	}

	/**
	 * Get application settings
	 * Since there's typically only one row, we get the first record
	 */
	function getSettings() {
		$sql = "SELECT * FROM {$this->table} LIMIT 1";
		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Get settings by ID
	 */
	function getDetail($id) {
		if (!is_numeric($id) || $id < 0) {
			return array();
		}
		$sql = "SELECT * FROM {$this->table} WHERE id = ?";
		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Save application settings
	 */
	function saveData($data) {
		$return = array('success' => 0);

		try {
			// Check if record exists
			$existing = $this->getSettings();

			if (!empty($existing) && isset($existing['id'])) {
				// Update existing record
				$this->db->where('id', $existing['id']);
				if ($this->db->update($this->table, $data)) {
					$return['success'] = 1;
					$return['id'] = $existing['id'];
				}
			} else {
				// Insert new record
				if ($this->db->insert($this->table, $data)) {
					$return['success'] = 1;
					$return['id'] = $this->db->insert_id();
				}
			}
		} catch (Exception $e) {
			log_message('error', 'Appsetting_model::saveData - ' . $e->getMessage());
			$return['success'] = 0;
		}

		return $return;
	}
}
?>
