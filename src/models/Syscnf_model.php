<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System Configuration Model
 * Handles sys_cnf table operations for key-value configuration pairs
 */
class Syscnf_model extends CI_Model
{
	private $table = 'sys_cnf';

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get all configurations grouped by cnf_group
	 *
	 * @return array Configurations grouped by group name
	 */
	function getAllGrouped()
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->order_by('cnf_group', 'ASC');
		$this->db->order_by('cnf_key', 'ASC');
		$data = $this->db->get()->result_array();

		$grouped = array();
		foreach ($data as $row) {
			$group = !empty($row['cnf_group']) ? $row['cnf_group'] : 'GENERAL';
			if (!isset($grouped[$group])) {
				$grouped[$group] = array();
			}
			$grouped[$group][] = $row;
		}

		return $grouped;
	}

	/**
	 * Get all configurations as flat array
	 *
	 * @return array All configurations
	 */
	function getAll()
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->order_by('cnf_group', 'ASC');
		$this->db->order_by('cnf_key', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Get configuration by ID
	 *
	 * @param int $id Config ID
	 * @return array|null Configuration row
	 */
	function getById($id)
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where('id', intval($id));
		return $this->db->get()->row_array();
	}

	/**
	 * Get configuration by key
	 *
	 * @param string $key Config key
	 * @return string|null Configuration value
	 */
	function getValue($key)
	{
		$this->db->select('cnf_val');
		$this->db->from($this->table);
		$this->db->where('cnf_key', $key);
		$row = $this->db->get()->row();
		return $row ? $row->cnf_val : null;
	}

	/**
	 * Update configuration value by ID
	 *
	 * @param int $id Config ID
	 * @param string $value New value
	 * @return array Response with success status
	 */
	function updateValue($id, $value)
	{
		$this->db->where('id', intval($id));
		$result = $this->db->update($this->table, array(
			'cnf_val' => $value,
			'updated_on' => date('Y-m-d H:i:s')
		));

		return array('success' => $result ? 1 : 0);
	}

	/**
	 * Update configuration value by key
	 *
	 * @param string $key Config key
	 * @param string $value New value
	 * @return array Response with success status
	 */
	function updateValueByKey($key, $value)
	{
		$this->db->where('cnf_key', $key);
		$result = $this->db->update($this->table, array(
			'cnf_val' => $value,
			'updated_on' => date('Y-m-d H:i:s')
		));

		return array('success' => $result ? 1 : 0);
	}

	/**
	 * Get all configurations by group as key-value pairs
	 *
	 * @param string $group Group name (BILLING, AUTOMATION, FEATURES, etc.)
	 * @return array Key-value pairs of configurations
	 */
	function getByGroup($group)
	{
		$this->db->select('cnf_key, cnf_val');
		$this->db->from($this->table);
		$this->db->where('cnf_group', $group);
		$data = $this->db->get()->result_array();

		$config = array();
		foreach ($data as $row) {
			$config[$row['cnf_key']] = $row['cnf_val'];
		}

		return $config;
	}

	/**
	 * Get configuration value with type casting
	 *
	 * @param string $key Config key
	 * @param mixed $default Default value if not found
	 * @param string $type Type to cast (string, int, float, bool)
	 * @return mixed Configuration value
	 */
	function get($key, $default = null, $type = 'string')
	{
		$value = $this->getValue($key);

		if ($value === null) {
			return $default;
		}

		switch ($type) {
			case 'int':
			case 'integer':
				return intval($value);
			case 'float':
			case 'double':
				return floatval($value);
			case 'bool':
			case 'boolean':
				return in_array(strtolower($value), array('1', 'true', 'yes', 'on'));
			default:
				return $value;
		}
	}

}
