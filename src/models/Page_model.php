<?php
class Page_model extends CI_Model {
	var $table;
	var $history_table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "pages";
		$this->history_table = "page_history";
	}

	function loadAllData() {
		$sql = "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY sort_order ASC, page_title ASC";
		return $this->db->query($sql)->result_array();
	}

	function loadPublishedPages() {
		$sql = "SELECT * FROM {$this->table} WHERE status = 1 AND is_published = 1 ORDER BY sort_order ASC";
		return $this->db->query($sql)->result_array();
	}

	function getDetail($id) {
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}

		$sql = "SELECT * FROM {$this->table} WHERE id = ? AND status = 1";
		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function getBySlug($slug) {
		if (empty($slug)) {
			return array();
		}

		$sql = "SELECT * FROM {$this->table} WHERE page_slug = ? AND status = 1 AND is_published = 1";
		$data = $this->db->query($sql, array($slug))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function slugExists($slug, $excludeId = null) {
		$sql = "SELECT id FROM {$this->table} WHERE page_slug = ? AND status = 1";
		$bindings = array($slug);

		if ($excludeId) {
			$sql .= " AND id != ?";
			$bindings[] = intval($excludeId);
		}

		$data = $this->db->query($sql, $bindings)->result_array();
		return !empty($data);
	}

	function saveData($data) {
		$return = array('id' => 0, 'success' => false);

		if (!empty($data['id']) && $data['id'] > 0) {
			$this->db->where('id', $data['id']);
			if ($this->db->update($this->table, $data)) {
				$return['id'] = $data['id'];
				$return['success'] = true;
			}
		} else {
			unset($data['id']);
			if ($this->db->insert($this->table, $data)) {
				$return['id'] = $this->db->insert_id();
				$return['success'] = true;
			}
		}

		return $return;
	}

	function saveHistory($pageId, $data, $changeType = 'updated', $changeNote = '') {
		$historyData = array(
			'page_id' => $pageId,
			'page_title' => $data['page_title'] ?? '',
			'page_content' => $data['page_content'] ?? '',
			'meta_title' => $data['meta_title'] ?? '',
			'meta_description' => $data['meta_description'] ?? '',
			'change_type' => $changeType,
			'change_note' => $changeNote,
			'changed_by' => getAdminId(),
			'changed_at' => getDateTime()
		);

		return $this->db->insert($this->history_table, $historyData);
	}

	function getHistory($pageId, $limit = 20) {
		if (empty($pageId) || !is_numeric($pageId)) {
			return array();
		}

		$sql = "SELECT h.*, a.username as changed_by_name
				FROM {$this->history_table} h
				LEFT JOIN admin_users a ON h.changed_by = a.id
				WHERE h.page_id = ?
				ORDER BY h.changed_at DESC
				LIMIT ?";

		return $this->db->query($sql, array(intval($pageId), intval($limit)))->result_array();
	}

	function getHistoryDetail($historyId) {
		if (empty($historyId) || !is_numeric($historyId)) {
			return array();
		}

		$sql = "SELECT h.*, a.username as changed_by_name
				FROM {$this->history_table} h
				LEFT JOIN admin_users a ON h.changed_by = a.id
				WHERE h.id = ?";
		$data = $this->db->query($sql, array(intval($historyId)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function incrementView($id) {
		$sql = "UPDATE {$this->table} SET total_view = total_view + 1 WHERE id = ?";
		return $this->db->query($sql, array(intval($id)));
	}

	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);
		$results = $data->result_array();

		foreach ($results as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}

		return $results;
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("SELECT COUNT(id) as cnt FROM {$this->table} WHERE status = 1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("SELECT COUNT(id) as cnt FROM {$this->table} $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function getStats() {
		$stats = array(
			'total' => 0,
			'published' => 0,
			'draft' => 0,
			'total_views' => 0
		);

		$sql = "SELECT
					COUNT(*) as total,
					SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published,
					SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as draft,
					SUM(total_view) as total_views
				FROM {$this->table} WHERE status = 1";

		$data = $this->db->query($sql)->result_array();

		if (!empty($data[0])) {
			$stats = array(
				'total' => $data[0]['total'] ?? 0,
				'published' => $data[0]['published'] ?? 0,
				'draft' => $data[0]['draft'] ?? 0,
				'total_views' => $data[0]['total_views'] ?? 0
			);
		}

		return $stats;
	}
}
?>
