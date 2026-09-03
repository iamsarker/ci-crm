<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	/**
	 * Filter packages by module, server, and service group
	 * Called from order_new.php via AJAX JSON POST
	 */
	public function filter_api()
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		$module_id = isset($_POST['module_id']) ? intval($_POST['module_id']) : 0;
		$server_id = isset($_POST['server_id']) ? intval($_POST['server_id']) : 0;
		$service_group_id = isset($_POST['service_group_id']) ? intval($_POST['service_group_id']) : 0;

		$this->db->select('id, product_name');
		$this->db->from('product_services');
		$this->db->where('status', 1);

		if ($module_id > 0) {
			$this->db->where('product_service_module_id', $module_id);
		}
		if ($server_id > 0) {
			$this->db->where('server_id', $server_id);
		}
		if ($service_group_id > 0) {
			$this->db->where('product_service_group_id', $service_group_id);
		}

		$this->db->order_by('product_name', 'ASC');
		$results = $this->db->get()->result_array();

		if (!empty($results)) {
			echo json_encode(array('code' => 200, 'data' => $results));
		} else {
			echo json_encode(array('code' => 404, 'data' => array()));
		}
	}

	/**
	 * Get package price by currency and billing cycle
	 * Called from order_new.php via AJAX JSON POST
	 */
	public function prices()
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		$currency_id = isset($_POST['currency_id']) ? intval($_POST['currency_id']) : 0;
		$billing_cycle_id = isset($_POST['billing_cycle_id']) ? intval($_POST['billing_cycle_id']) : 0;
		$product_service_id = isset($_POST['product_service_id']) ? intval($_POST['product_service_id']) : 0;
		// Whose price to quote. The form posts the selected customer so the
		// figure it fills in matches what saveOrderItemTable() will actually
		// write -- that path resolves for the buyer, and a form showing
		// platform retail while the saved order carries the reseller's price
		// is worse than either number on its own.
		$company_id = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;

		if ($currency_id <= 0 || $billing_cycle_id <= 0 || $product_service_id <= 0) {
			echo json_encode(array('code' => 400, 'data' => null));
			return;
		}

		// company_id is a POST field, so it is attacker-controlled. Anything
		// outside the caller's tenant scope is refused rather than silently
		// downgraded to platform retail -- a reseller must not be able to
		// probe another tenant's negotiated pricing.
		if ($company_id > 0) {
			$this->guardCompany($company_id);
		}

		$this->db->select('id, price');
		$this->db->from('product_service_pricing');
		$this->db->where('product_service_id', $product_service_id);
		$this->db->where('currency_id', $currency_id);
		$this->db->where('billing_cycle_id', $billing_cycle_id);
		$this->db->where('status', 1);
		$this->db->limit(1);
		$result = $this->db->get()->row_array();

		if (!empty($result)) {
			$this->load->model('Pricing_model');
			$r = $this->Pricing_model->resolve(2, $result['id'], $company_id);
			if (!empty($r)) {
				$result['price'] = $r['price'];
			}
			echo json_encode(array('code' => 200, 'data' => $result));
		} else {
			echo json_encode(array('code' => 404, 'data' => null));
		}
	}

}
