<?php 
class Order_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	public function countOrder($companyId) {
		$this->db->select('count(*) as cnt');
		$this->db->from("orders");
		$this->db->where(array(
			'status'=>'1',
			'company_id'=> $companyId
		));
		$data = $this->db->get();
		if ($data) {
			$res = $data->result();
			return $res[0]->cnt;
		} else {
			return 0;
		}
	}

	public function generateNumber($no_type) {
		$this->db->select('id, last_no');
		$this->db->from("gen_numbers");
		$this->db->where(array(
			'no_type'=>strtoupper($no_type),
		));
		$data = $this->db->get();

		$last_no = 0;
		$id = 0;
		if ($data) {
			$res = $data->result();
			$id = $res[0]->id;
			$last_no = $res[0]->last_no + 1; // increment with one
		} else {
			$last_no = 100 + 1; // increment with one
		}

		$record = array(
			'no_type'	=>strtoupper($no_type),
			'last_no'	=>$last_no,
		);
		$this->db->where('id', $id);
		if ($this->db->update('gen_numbers', $record)) {
			return $last_no;
		}
	}


	function loadOrderList($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('*');
		$this->db->from('orders');
		$this->db->where('status', 1);

		if( is_numeric($companyId) && $companyId > 0 ){
			$this->db->where('company_id', intval($companyId));
		}

		$this->db->order_by('id', 'DESC');

		if( is_numeric($limit) && $limit > 0 ){
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
 	}

	function loadOrderServices($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('*');
		$this->db->from('order_services');

		if( is_numeric($companyId) && $companyId > 0 ){
			$this->db->where('company_id', intval($companyId));
		}

		$this->db->order_by('id', 'DESC');

		if( is_numeric($limit) && $limit > 0 ){
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
	}

	function loadOrderServiceById($companyId, $id) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		if( !is_numeric($companyId) || !is_numeric($id) || $companyId <= 0 || $id <= 0 ){
			return array();
		}

		$this->db->select('os.*, o.currency_code, o.instructions');
		$this->db->from('order_services os');
		$this->db->join('orders o', 'os.order_id = o.id');
		$this->db->where('os.id', intval($id));
		$this->db->where('os.company_id', intval($companyId));

		$data = $this->db->get()->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function loadOrderDomains($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('*');
		$this->db->from('order_domains');

		if( is_numeric($companyId) && $companyId > 0 ){
			$this->db->where('company_id', intval($companyId));
		}

		$this->db->order_by('id', 'DESC');

		if( is_numeric($limit) && $limit > 0 ){
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
	}

	function loadOrderDomainById($companyId, $id) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		if( !is_numeric($companyId) || !is_numeric($id) || $companyId <= 0 || $id <= 0 ){
			return array();
		}

		$this->db->select('od.*, o.currency_code, o.instructions');
		$this->db->from('order_domains od');
		$this->db->join('orders o', 'od.order_id = o.id');
		$this->db->where('od.id', intval($id));
		$this->db->where('od.company_id', intval($companyId));

		$data = $this->db->get()->result_array();

		return !empty($data) ? $data[0] : array();
	}

 	function saveOrder($data){
 		$data['status'] = 1;
 		$data['inserted_on'] = getDateTime();
 		$data['inserted_by'] = getCustomerId();
 		if ($this->db->insert('orders', $data)) {
			return $this->db->insert_id();
		}
		return -1;
 	}

	function saveOrderService($data){
		if ($this->db->insert('order_services', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}

	function saveOrderDomain($data){
		if ($this->db->insert('order_domains', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}

	function saveInvoice($data){
		if ($this->db->insert('invoices', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}

	function saveInvoiceItem($data){
		if ($this->db->insert('invoice_items', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}


	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);
		return $data->result_array();
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("select count(id) as cnt from order_view where status=1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from order_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function getDetail($id) {
		$this->db->select('*');
		$this->db->from("orders");
		$this->db->where('id', $id);
		$data = $this->db->get();
		if ($data && $data->num_rows() > 0) {
			return $data->row_array();
		} else {
			return array();
		}
	}

}
?>
