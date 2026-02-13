<?php 
class Support_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadTicketList($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('tk.id, tk.title, tk.company_id, tk.message, tk.priority, tk.attachment, tk.flag, tk.ticket_dept_id, td.name dept_name, tk.order_service_id, os.description, tk.order_domain_id, od.domain, tk.updated_on, tk.inserted_on');
		$this->db->from('tickets tk');
		$this->db->join('ticket_depts td', 'tk.ticket_dept_id = td.id');
		$this->db->join('order_services os', 'tk.order_service_id = os.id', 'left');
		$this->db->join('order_domains od', 'tk.order_domain_id = od.id', 'left');

		$this->db->where('tk.status', 1);

		if (is_numeric($companyId) && $companyId > 0) {
			$this->db->where('tk.company_id', intval($companyId));
		}

		$this->db->order_by('tk.updated_on', 'DESC');

		if (is_numeric($limit) && $limit > 0) {
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
 	}

	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);

		return $data->result_array();
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("select count(id) as cnt from ticket_view where status=1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from ticket_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function viewTicket($tId, $companyId) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate inputs
		if (!is_numeric($tId) || !is_numeric($companyId) || $tId <= 0 || $companyId <= 0) {
			return array();
		}

		$sql = "SELECT tk.id, tk.title, tk.company_id, tk.message, tk.priority, tk.attachment, tk.flag, tk.ticket_dept_id, td.name dept_name, tk.order_service_id, os.description, tk.order_domain_id, od.domain, tk.updated_on, tk.inserted_on,
			concat(u.first_name, ' ', u.last_name) as user_name
			FROM tickets tk
			JOIN ticket_depts td on tk.ticket_dept_id=td.id
			INNER JOIN users u on tk.inserted_by=u.id
			LEFT JOIN order_services os on tk.order_service_id=os.id
			LEFT JOIN order_domains od on tk.order_domain_id=od.id
			WHERE tk.id=? AND tk.company_id=? AND tk.status=1";

		$data = $this->db->query($sql, array(intval($tId), intval($companyId)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function getTicketDetail($tId) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate input
		if (!is_numeric($tId) || $tId <= 0) {
			return array();
		}

		$sql = "SELECT tk.id, tk.title, tk.company_id, tk.message, tk.priority, tk.attachment, tk.flag, tk.ticket_dept_id, td.name dept_name, tk.order_service_id, os.description, tk.order_domain_id, od.domain, tk.updated_on, tk.inserted_on,
			concat(u.first_name, ' ', u.last_name) as user_name
			FROM tickets tk
			JOIN ticket_depts td on tk.ticket_dept_id=td.id
			INNER JOIN users u on tk.inserted_by=u.id
			LEFT JOIN order_services os on tk.order_service_id=os.id
			LEFT JOIN order_domains od on tk.order_domain_id=od.id
			WHERE tk.id=? AND tk.status=1";

		$data = $this->db->query($sql, array(intval($tId)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function viewTicketReplies($tId) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate input
		if (!is_numeric($tId) || $tId <= 0) {
			return array();
		}

		$sql = "SELECT tr.id, tr.ticket_id, tr.company_id, tr.admin_id, tr.message, tr.attachment, tr.rating, tr.inserted_on, tr.updated_on,
			CONCAT(u.first_name,' ', u.last_name) as user_name, CONCAT(au.first_name, au.last_name) as staff_name
			FROM ticket_replies tr
			LEFT JOIN users u on tr.inserted_by=u.id
			LEFT JOIN admin_users au on tr.admin_id=au.id
			WHERE tr.ticket_id=? AND tr.status=1 ORDER BY tr.id DESC";

		$data = $this->db->query($sql, array(intval($tId)))->result_array();

		return !empty($data) ? $data : array();
	}

 	function ticketSummary($companyId){
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		$sql = "SELECT sum(CASE WHEN flag=1 THEN 1 ELSE 0 END) opened,
		sum(CASE WHEN flag=2 THEN 1 ELSE 0 END) answered,
		sum(CASE WHEN flag=3 THEN 1 ELSE 0 END) replied,
		sum(CASE WHEN flag=4 THEN 1 ELSE 0 END) closed
		FROM tickets WHERE status=1";

		$bindings = array();

		if (is_numeric($companyId) && $companyId > 0) {
			$sql .= " AND company_id=?";
			$bindings[] = intval($companyId);
		}

		$data = $this->db->query($sql, $bindings)->result_array();

		return $data;
	}

 	function saveUserLogins($data){
 		$data['active'] = 1;
 		if ($this->db->insert('user_logins', $data)) {
		}
 	}


	function loadKBCatList($limit) {
		// SECURITY FIX: Use prepared statement for LIMIT to prevent SQL injection
		$sql = " SELECT kc.id, kc.cat_title, kc.parent_id, kc.slug, kc.description, COUNT(kcm.id) total_kb
			FROM kb_cats kc
			LEFT JOIN kb_cat_mapping kcm on kc.id=kcm.kb_cat_id
			WHERE kc.status=1 and kc.is_hidden=0
			GROUP BY kc.id
			ORDER BY kc.cat_title ";

		$bindings = array();
		if (is_numeric($limit) && $limit > 0) {
			$sql .= " LIMIT ?";
			$bindings[] = intval($limit);
		}

		$data = $this->db->query($sql, $bindings)->result_array();

		return $data;
	}

	function loadKBList($limit, $offset = 0) {
		// SECURITY FIX: Use prepared statement for LIMIT to prevent SQL injection
		$sql = " SELECT k.id, k.title, k.slug, k.article, k.tags, k.total_view, k.useful, k.upvote, k.downvote, CONCAT('[', GROUP_CONCAT(JSON_OBJECT( 'id',kc.id, 'title', kc.cat_title, 'slug', kc.slug)), ']') as kb_cats
			FROM kbs k
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id
			JOIN kb_cats kc on kcm.kb_cat_id=kc.id
			WHERE k.status=1
			GROUP BY k.id
			ORDER BY k.sort_order ASC ";

		$bindings = array();
		if (is_numeric($limit) && $limit > 0) {
			$sql .= " LIMIT ?, ?";
			$bindings[] = intval($offset);
			$bindings[] = intval($limit);
		}

		$data = $this->db->query($sql, $bindings)->result_array();

		return $data;
	}

	function countKBList() {
		$sql = "SELECT COUNT(DISTINCT k.id) as total
			FROM kbs k
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id
			WHERE k.status=1";
		$data = $this->db->query($sql)->result_array();
		return !empty($data) ? intval($data[0]['total']) : 0;
	}

	function loadKBListByCategory($catId, $limit = -1, $offset = 0) {
		if (!is_numeric($catId) || $catId <= 0) {
			return array();
		}

		$sql = "SELECT k.id, k.title, k.slug, k.article, k.tags, k.total_view, k.useful, k.upvote, k.downvote
			FROM kbs k
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id
			WHERE kcm.kb_cat_id=? AND k.status=1
			ORDER BY k.sort_order ASC";

		$bindings = array(intval($catId));
		if (is_numeric($limit) && $limit > 0) {
			$sql .= " LIMIT ?, ?";
			$bindings[] = intval($offset);
			$bindings[] = intval($limit);
		}

		$data = $this->db->query($sql, $bindings)->result_array();
		return $data;
	}

	function countKBListByCategory($catId) {
		if (!is_numeric($catId) || $catId <= 0) {
			return 0;
		}

		$sql = "SELECT COUNT(k.id) as total
			FROM kbs k
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id
			WHERE kcm.kb_cat_id=? AND k.status=1";
		$data = $this->db->query($sql, array(intval($catId)))->result_array();
		return !empty($data) ? intval($data[0]['total']) : 0;
	}

	function getKBCategoryById($catId) {
		if (!is_numeric($catId) || $catId <= 0) {
			return array();
		}

		$sql = "SELECT id, cat_title, slug, description FROM kb_cats WHERE id=? AND status=1";
		$data = $this->db->query($sql, array(intval($catId)))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function loadKbDetails($id, $slug) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate inputs
		if (!is_numeric($id) || $id <= 0 || empty($slug)) {
			return array();
		}

		$sql = "SELECT k.id, k.title, k.slug, k.article, k.tags, k.total_view, k.useful, k.upvote, k.downvote, CONCAT('[', GROUP_CONCAT(JSON_OBJECT( 'id',kc.id, 'title', kc.cat_title, 'slug', kc.slug)), ']') as kb_cats
			FROM kbs k
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id
			JOIN kb_cats kc on kcm.kb_cat_id=kc.id
			WHERE k.id=? and k.slug=? and k.status=1
			GROUP BY k.id
			ORDER BY k.sort_order ASC";

		$data = $this->db->query($sql, array(intval($id), $slug))->result_array();

		return !empty($data) ? $data[0] : array();
	}


	function loadAnnouncements($limit, $offset = 0) {
		// SECURITY FIX: Use prepared statement for LIMIT to prevent SQL injection
		$sql = " SELECT a.id, a.title, a.slug, a.description, a.tags, a.total_view, a.publish_date
			FROM announcements a
			WHERE a.status=1 and a.is_published=1
			ORDER BY a.publish_date DESC ";

		$bindings = array();
		if (is_numeric($limit) && $limit > 0) {
			$sql .= " LIMIT ?, ?";
			$bindings[] = intval($offset);
			$bindings[] = intval($limit);
		}

		$data = $this->db->query($sql, $bindings)->result_array();
		return $data;
	}

	function countAnnouncements() {
		$sql = "SELECT COUNT(id) as total FROM announcements WHERE status=1 AND is_published=1";
		$data = $this->db->query($sql)->result_array();
		return !empty($data) ? intval($data[0]['total']) : 0;
	}

	function getAnnouncementArchive() {
		$sql = "SELECT
				YEAR(publish_date) as `year`,
				MONTH(publish_date) as `month`,
				DATE_FORMAT(publish_date, '%Y-%m') as `year_month`,
				DATE_FORMAT(publish_date, '%M %Y') as `month_name`,
				COUNT(id) as `total`
			FROM announcements
			WHERE status=1 AND is_published=1
			GROUP BY YEAR(publish_date), MONTH(publish_date)
			ORDER BY `year` DESC, `month` DESC";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function loadAnnouncementsByMonth($year, $month, $limit = -1, $offset = 0) {
		if (!is_numeric($year) || !is_numeric($month)) {
			return array();
		}

		$sql = "SELECT a.id, a.title, a.slug, a.description, a.tags, a.total_view, a.publish_date
			FROM announcements a
			WHERE a.status=1 AND a.is_published=1
			AND YEAR(a.publish_date) = ? AND MONTH(a.publish_date) = ?
			ORDER BY a.publish_date DESC";

		$bindings = array(intval($year), intval($month));
		if (is_numeric($limit) && $limit > 0) {
			$sql .= " LIMIT ?, ?";
			$bindings[] = intval($offset);
			$bindings[] = intval($limit);
		}

		$data = $this->db->query($sql, $bindings)->result_array();
		return $data;
	}

	function countAnnouncementsByMonth($year, $month) {
		if (!is_numeric($year) || !is_numeric($month)) {
			return 0;
		}

		$sql = "SELECT COUNT(id) as total FROM announcements
			WHERE status=1 AND is_published=1
			AND YEAR(publish_date) = ? AND MONTH(publish_date) = ?";
		$data = $this->db->query($sql, array(intval($year), intval($month)))->result_array();
		return !empty($data) ? intval($data[0]['total']) : 0;
	}

	function loadAnnouncementDetail($id, $slug) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate inputs
		if (!is_numeric($id) || $id <= 0 || empty($slug)) {
			return array();
		}

		$sql = "SELECT a.id, a.title, a.slug, a.description, a.tags, a.total_view
			FROM announcements a
			WHERE a.id=? and a.slug=? and a.status=1 and a.is_published=1";

		$data = $this->db->query($sql, array(intval($id), $slug))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	/**
	 * Get active services for a company as dropdown options
	 * @param int $companyId Company ID
	 * @return array Dropdown array with service id => display name
	 */
	function getActiveServicesDropdown($companyId) {
		if (!is_numeric($companyId) || $companyId <= 0) {
			return array('' => '-- None --');
		}

		$sql = "SELECT
				os.id,
				os.hosting_domain,
				ps.product_name
			FROM order_services os
			LEFT JOIN product_service_pricing psp ON os.product_service_pricing_id = psp.id
			LEFT JOIN product_services ps ON psp.product_service_id = ps.id
			WHERE os.company_id = ? AND os.status = 1
			ORDER BY os.id DESC";

		$data = $this->db->query($sql, array(intval($companyId)))->result_array();

		$dropdown = array('' => '-- None --');
		if (!empty($data)) {
			foreach ($data as $row) {
				$displayName = !empty($row['product_name']) ? $row['product_name'] : 'Service #' . $row['id'];
				if (!empty($row['hosting_domain'])) {
					$displayName .= ' (' . $row['hosting_domain'] . ')';
				}
				$dropdown[$row['id']] = $displayName;
			}
		}

		return $dropdown;
	}

	/**
	 * Get all active admin email addresses
	 */
	function getAdminEmails()
	{
		$emails = array();
		$sql = "SELECT email FROM admin_users WHERE status = 1 AND email IS NOT NULL AND email != ''";
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
					$emails[] = $row['email'];
				}
			}
		}

		return $emails;
	}

}
?>
