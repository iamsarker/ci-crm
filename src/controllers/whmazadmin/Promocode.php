<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promocode extends WHMAZADMIN_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Promocode_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/promocode_list', $data);
	}

	public function manage($id_val = null)
	{
		if ($this->input->post()) {
			$this->form_validation->set_rules('code', 'Promo Code', 'required|trim');
			$this->form_validation->set_message('code', 'Promo Code is required');

			$this->form_validation->set_rules('discount_type', 'Discount Type', 'required');
			$this->form_validation->set_rules('discount_value', 'Discount Value', 'required|numeric|greater_than[0]');

			if ($this->form_validation->run() == true) {

				$form_data = array(
					'id'                   => safe_decode($this->input->post('id')),
					'code'                 => strtoupper(trim($this->input->post('code'))),
					'description'          => $this->input->post('description'),
					'discount_type'        => $this->input->post('discount_type'),
					'discount_value'       => floatval($this->input->post('discount_value')),
					'currency_id'          => $this->input->post('currency_id') ?: null,
					'is_lifetime'          => $this->input->post('is_lifetime') ? 1 : 0,
					'start_date'           => $this->input->post('is_lifetime') ? null : ($this->input->post('start_date') ?: null),
					'end_date'             => $this->input->post('is_lifetime') ? null : ($this->input->post('end_date') ?: null),
					'max_uses'             => intval($this->input->post('max_uses')),
					'max_uses_per_customer'=> intval($this->input->post('max_uses_per_customer')),
					'min_order_amount'     => floatval($this->input->post('min_order_amount')),
					'max_discount_amount'  => floatval($this->input->post('max_discount_amount')),
					'applies_to'           => $this->input->post('applies_to') ?: 'all',
					'is_active'            => $this->input->post('is_active') ? 1 : 0,
					'status'               => 1
				);

				$pkId = 0;
				if (intval($form_data['id']) > 0) {
					$oldEntity = $this->Promocode_model->getDetail(safe_decode($id_val));
					$pkId = $oldEntity['id'];
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();
					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if ($this->Promocode_model->saveData($form_data)) {
					if ($pkId == 0) {
						$pkId = $this->Promocode_model->getLastId();
					}

					// Save product/customer mappings
					$productIds = $this->input->post('product_ids') ?: array();
					$companyIds = $this->input->post('company_ids') ?: array();
					$this->Promocode_model->saveMappings($pkId, $productIds, $companyIds);

					$this->session->set_flashdata('admin_success', 'Promo code has been saved successfully.');
					redirect("whmazadmin/promocode/index");
				}
			} else {
				$this->session->set_flashdata('admin_error', 'Validation error. Please check the form and try again.');
			}
		}

		if (!empty($id_val)) {
			$data['detail'] = $this->Promocode_model->getDetail(safe_decode($id_val));
			if (!empty($data['detail'])) {
				$data['product_mappings'] = $this->Promocode_model->getProductMappings($data['detail']['id']);
				$data['customer_mappings'] = $this->Promocode_model->getCustomerMappings($data['detail']['id']);
			} else {
				$data['product_mappings'] = array();
				$data['customer_mappings'] = array();
			}
		} else {
			$data['detail'] = array();
			$data['product_mappings'] = array();
			$data['customer_mappings'] = array();
		}

		$data['products'] = $this->Promocode_model->getAllProducts();
		$data['companies'] = $this->Promocode_model->getAllCompanies();
		$data['currencies'] = $this->Promocode_model->getCurrencies();

		$this->load->view('whmazadmin/promocode_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Promocode_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Promocode_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Promo code has been deleted successfully.');

		redirect('whmazadmin/promocode/index');
	}

	public function ssp_list_api()
	{
		$this->processRestCall();

		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "promo_codes", $bindings, $where);

			$data = $this->Promocode_model->getDataTableRecords($sqlQuery, $bindings);

			// Get stats
			$stats = $this->Promocode_model->getPromoStats();

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Promocode_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Promocode_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data,
				"stats"           => $stats
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			echo json_encode(array(
				"draw"            => 0,
				"recordsTotal"    => 0,
				"recordsFiltered" => 0,
				"data"            => array(),
				"error"           => $e->getMessage()
			));
			exit;
		}
	}

	public function toggle_active($id_val)
	{
		header('Content-Type: application/json');

		$id = safe_decode($id_val);
		$newStatus = $this->Promocode_model->toggleActive($id);

		if ($newStatus !== false) {
			echo json_encode(array('success' => true, 'is_active' => $newStatus));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Promo code not found.'));
		}
		exit;
	}
}
