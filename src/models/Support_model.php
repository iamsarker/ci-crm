<?php 
class Support_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadTicketList($companyId, $limit) {

		$usrCondition = " WHERE tk.status=1 ";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " WHERE tk.company_id=$companyId AND tk.status=1";
		}

		$sql = "SELECT tk.id, tk.title, tk.company_id, tk.message, tk.priority, tk.attachment, tk.flag, tk.ticket_dept_id, td.name dept_name, tk.order_service_id, os.description, tk.order_domain_id, od.domain, tk.updated_on, tk.inserted_on
			FROM tickets tk 
			JOIN ticket_depts td on tk.ticket_dept_id=td.id 
			LEFT JOIN order_services os on tk.order_service_id=os.id 
			LEFT JOIN order_domains od on tk.order_domain_id=od.id 
			$usrCondition ORDER BY tk.updated_on DESC ";

		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();
		
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

		if( is_numeric($tId) && $tId > 0 ){
			$tkCondition = " tk.id=$tId AND ";
		} else{
			return array();
		}

		$sql = "SELECT tk.id, tk.title, tk.company_id, tk.message, tk.priority, tk.attachment, tk.flag, tk.ticket_dept_id, td.name dept_name, tk.order_service_id, os.description, tk.order_domain_id, od.domain, tk.updated_on, tk.inserted_on, 
			concat(u.first_name, ' ', u.last_name) as user_name
			FROM tickets tk 
			JOIN ticket_depts td on tk.ticket_dept_id=td.id 
			INNER JOIN users u on tk.inserted_by=u.id
			LEFT JOIN order_services os on tk.order_service_id=os.id 
			LEFT JOIN order_domains od on tk.order_domain_id=od.id 
			WHERE $tkCondition tk.company_id=$companyId AND tk.status=1 ";

		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function getTicketDetail($tId) {

		if( is_numeric($tId) && $tId > 0 ){
			$tkCondition = " tk.id=$tId AND ";
		} else{
			return array();
		}

		$sql = "SELECT tk.id, tk.title, tk.company_id, tk.message, tk.priority, tk.attachment, tk.flag, tk.ticket_dept_id, td.name dept_name, tk.order_service_id, os.description, tk.order_domain_id, od.domain, tk.updated_on, tk.inserted_on, 
			concat(u.first_name, ' ', u.last_name) as user_name
			FROM tickets tk 
			JOIN ticket_depts td on tk.ticket_dept_id=td.id 
			INNER JOIN users u on tk.inserted_by=u.id
			LEFT JOIN order_services os on tk.order_service_id=os.id 
			LEFT JOIN order_domains od on tk.order_domain_id=od.id 
			WHERE $tkCondition tk.status=1 ";

		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function viewTicketReplies($tId) {

		if( is_numeric($tId) && $tId > 0 ){
			$tkCondition = " tr.ticket_id=$tId AND ";
		} else{
			return array();
		}

		$sql = "SELECT tr.id, tr.ticket_id, tr.company_id, tr.admin_id, tr.message, tr.attachment, tr.rating, tr.inserted_on, tr.updated_on, 
			CONCAT(u.first_name,' ', u.last_name) as user_name, CONCAT(au.first_name, au.last_name) as staff_name
			FROM ticket_replies tr 
			LEFT JOIN users u on tr.inserted_by=u.id
			LEFT JOIN admin_users au on tr.admin_id=au.id
			WHERE $tkCondition tr.status=1 ORDER BY tr.id DESC ";

		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data : array();
	}

 	function ticketSummary($companyId){
		$sql = " SELECT sum(CASE WHEN flag=1 THEN 1 ELSE 0 END) opened, 
		sum(CASE WHEN flag=2 THEN 1 ELSE 0 END) answered, 
		sum(CASE WHEN flag=3 THEN 1 ELSE 0 END) replied, 
		sum(CASE WHEN flag=4 THEN 1 ELSE 0 END) closed 
		FROM tickets WHERE  ";

		if( is_numeric($companyId) && $companyId > 0 ){
			$sql .= " company_id=$companyId and  ";
		}
		$sql .= " status=1 ";

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

 	function saveUserLogins($data){
 		$data['active'] = 1;
 		if ($this->db->insert('user_logins', $data)) {
		}
 	}


	function loadKBCatList($limit) {

		$sql = " SELECT kc.id, kc.cat_title, kc.parent_id, kc.slug, kc.description, COUNT(kcm.id) total_kb 
			FROM kb_cats kc 
			LEFT JOIN kb_cat_mapping kcm on kc.id=kcm.kb_cat_id 
			WHERE kc.status=1 and kc.is_hidden=0 
			GROUP BY kc.id 
			ORDER BY kc.cat_title ";

		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

	function loadKBList($limit) {

		$sql = " SELECT k.id, k.title, k.slug, k.article, k.tags, k.total_view, k.useful, k.upvote, k.downvote, CONCAT('[', GROUP_CONCAT(JSON_OBJECT( 'id',kc.id, 'title', kc.cat_title, 'slug', kc.slug)), ']') as kb_cats 
			FROM kbs k 
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id 
			JOIN kb_cats kc on kcm.kb_cat_id=kc.id 
			WHERE k.status=1 
			GROUP BY k.id 
			ORDER BY k.sort_order ASC ";

		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

	function loadKbDetails($id, $slug) {

		$sql = " SELECT k.id, k.title, k.slug, k.article, k.tags, k.total_view, k.useful, k.upvote, k.downvote, CONCAT('[', GROUP_CONCAT(JSON_OBJECT( 'id',kc.id, 'title', kc.cat_title, 'slug', kc.slug)), ']') as kb_cats 
			FROM kbs k 
			JOIN kb_cat_mapping kcm on k.id=kcm.kb_id 
			JOIN kb_cats kc on kcm.kb_cat_id=kc.id 
			WHERE k.id=$id and k.slug='$slug' and k.status=1 
			GROUP BY k.id 
			ORDER BY k.sort_order ASC ";

		$data = $this->db->query($sql)->result_array();

		return $data[0];
	}


	function loadAnnouncements($limit) {

		$sql = " SELECT a.id, a.title, a.slug, a.description, a.tags, a.total_view 
			FROM announcements a 
			WHERE a.status=1 and a.is_published=1 
			ORDER BY a.publish_date DESC ";

		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function loadAnnouncementDetail($id, $slug) {

		$sql = " SELECT a.id, a.title, a.slug, a.description, a.tags, a.total_view 
			FROM announcements a 
			WHERE a.id=$id and a.slug='$slug' and a.status=1 and a.is_published=1 ";

		$data = $this->db->query($sql)->result_array();

		return $data[0];
	}

}
?>
