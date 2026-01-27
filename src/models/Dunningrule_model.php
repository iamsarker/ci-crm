<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dunningrule_model extends CI_Model {
	var $table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "dunning_rules";
	}

	/**
	 * Load all active dunning rules ordered by step_number
	 */
	function loadAllData() {
		$sql = "SELECT * FROM {$this->table} ORDER BY step_number ASC";
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get single rule by ID
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
	 * Save dunning rule (insert or update)
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
			log_message('error', 'Dunningrule_model::saveData - ' . $e->getMessage());
			$return['success'] = 0;
		}

		return $return;
	}

	/**
	 * Delete dunning rule by ID
	 */
	function deleteData($id) {
		$return = array('success' => 0);

		if (!is_numeric($id) || $id < 0) {
			return $return;
		}

		try {
			$this->db->where('id', intval($id));
			if ($this->db->delete($this->table)) {
				$return['success'] = 1;
			}
		} catch (Exception $e) {
			log_message('error', 'Dunningrule_model::deleteData - ' . $e->getMessage());
		}

		return $return;
	}

	/**
	 * Check if step_number already exists (excluding given ID for updates)
	 */
	function isStepExists($step_number, $exclude_id = 0) {
		$sql = "SELECT id FROM {$this->table} WHERE step_number = ?";
		$params = array(intval($step_number));

		if ($exclude_id > 0) {
			$sql .= " AND id != ?";
			$params[] = intval($exclude_id);
		}

		$data = $this->db->query($sql, $params)->result_array();
		return !empty($data);
	}
}
?>
