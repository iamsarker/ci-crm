<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Software_model
 * -------------------------------------------------------------------------
 * Manages WHMAZ software releases (`software_releases`). One build serves all
 * plans; exactly one row is is_current=1. Files live privately in
 * uploadedfiles/software/ and are streamed only via license-gated endpoints.
 *
 * @see src/controllers/whmazadmin/Software.php
 * @see src/modules/subscription/controllers/Subscription.php (download)
 * @see src/modules/license/controllers/License.php (download)
 */
class Software_model extends CI_Model {

	private $table = 'software_releases';

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Active releases, newest first. Pass a product id to get that product's
	 * releases plus any global (product_id IS NULL) releases; null returns all.
	 */
	function getReleases($productId = null)
	{
		$this->db->where('status', 1);
		if ($productId !== null) {
			$productId = (int) $productId;
			$this->db->group_start()
				->where('product_id', $productId)
				->or_where('product_id IS NULL', null, false)
				->group_end();
		}
		return $this->db->order_by('id', 'DESC')->get($this->table)->result_array();
	}

	function getRelease($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return array();
		}
		return $this->db->get_where($this->table, array('id' => $id, 'status' => 1))->row_array() ?: array();
	}

	/** The release customers download (is_current=1), or empty array. */
	function getCurrentRelease()
	{
		return $this->db
			->where('is_current', 1)
			->where('status', 1)
			->order_by('id', 'DESC')
			->limit(1)
			->get($this->table)
			->row_array() ?: array();
	}

	/**
	 * Insert a release. When $makeCurrent, it becomes the sole current release.
	 *
	 * @return int new id
	 */
	function saveRelease($data, $makeCurrent = true)
	{
		$this->db->insert($this->table, $data);
		$id = (int) $this->db->insert_id();

		if ($makeCurrent && $id > 0) {
			$this->setCurrent($id);
		}
		return $id;
	}

	/** Mark one release current and unset the rest. */
	function setCurrent($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return false;
		}
		$this->db->where('id !=', $id)->update($this->table, array('is_current' => 0));
		$this->db->where('id', $id)->update($this->table, array('is_current' => 1));
		return true;
	}

	/** Soft-delete a release (keeps the file on disk). */
	function softDelete($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return false;
		}
		return $this->db->where('id', $id)->update($this->table, array('is_current' => 0, 'status' => 0));
	}

	/** Absolute path to the private storage directory (no trailing slash). */
	function storageDir()
	{
		return realpath(APPPATH . '../uploadedfiles/software');
	}

	/** Absolute path to a release's file, or '' if missing on disk. */
	function filePath($release)
	{
		if (empty($release['file_name'])) {
			return '';
		}
		$path = $this->storageDir() . DIRECTORY_SEPARATOR . $release['file_name'];
		return is_file($path) ? $path : '';
	}
}
