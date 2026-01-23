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

		$usrCondition = " WHERE status=1 ";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " WHERE company_id=$companyId AND status=1 ";
		}

		$sql = "SELECT * FROM orders $usrCondition AND status=1 ORDER BY id DESC ";
		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();

		return $data;
 	}

	function loadOrderServices($companyId, $limit) {

		$usrCondition = "";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " company_id=$companyId ";
		}

		$sql = "SELECT * FROM order_services WHERE $usrCondition ORDER BY id DESC ";
		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

	function loadOrderServiceById($companyId, $id) {

		$usrCondition = "";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " os.id=$id and os.company_id=$companyId ";
		}

		$sql = "SELECT os.*, o.currency_code, o.instructions FROM order_services os join orders o on os.order_id=o.id WHERE $usrCondition ";
		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function loadOrderDomains($companyId, $limit) {

		$usrCondition = "";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " company_id=$companyId ";
		}

		$sql = "SELECT * FROM order_domains WHERE $usrCondition ORDER BY id DESC ";
		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

	function loadOrderDomainById($companyId, $id) {

		$usrCondition = "";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " od.id=$id and od.company_id=$companyId ";
		}

		$sql = "SELECT od.*, o.currency_code, o.instructions FROM order_domains od join orders o on od.order_id=o.id WHERE $usrCondition ";
		$data = $this->db->query($sql)->result_array();

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
