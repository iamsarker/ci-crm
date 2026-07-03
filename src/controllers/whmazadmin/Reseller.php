<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reseller (admin)
 * -------------------------------------------------------------------------
 * CRUD for reseller accounts: promote a company to a reseller, set its
 * discount / credit / API access, and assign sub-customer companies under it.
 *
 * Mirrors the Promocode admin controller conventions (index / manage /
 * delete_records / ssp_list_api). Auth guarded in the constructor.
 *
 * @see src/models/Reseller_model.php
 */
class Reseller extends WHMAZADMIN_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Reseller_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index() {
		$data['results'] = array();
		$this->load->view('whmazadmin/reseller_list', $data);
	}

	public function manage($id_val = null) {
		if ($this->input->post()) {
			$this->form_validation->set_rules('company_id', 'Company', 'required|greater_than[0]');
			$this->form_validation->set_rules('discount_type', 'Discount Type', 'required');
			$this->form_validation->set_rules('discount_value', 'Discount Value', 'required|numeric|greater_than_equal_to[0]');

			if ($this->form_validation->run() == true) {

				$pkId      = intval(safe_decode($this->input->post('id')));
				$companyId = intval($this->input->post('company_id'));

				// Guard: one profile per company.
				$existing = $this->Reseller_model->getByCompany($companyId);
				if (!empty($existing) && intval($existing['id']) !== $pkId) {
					$this->session->set_flashdata('admin_error', 'This company is already a reseller.');
					redirect('whmazadmin/reseller/manage' . ($pkId > 0 ? '/' . safe_encode($pkId) : ''));
					return;
				}

				$form_data = array(
					'id'             => $pkId,
					'company_id'     => $companyId,
					'discount_type'  => $this->input->post('discount_type') === 'fixed' ? 'fixed' : 'percent',
					'discount_value' => floatval($this->input->post('discount_value')),
					'credit_balance' => floatval($this->input->post('credit_balance')),
					'currency_id'    => $this->input->post('currency_id') ?: null,
					'allow_api'      => $this->input->post('allow_api') ? 1 : 0,
					'notes'          => $this->input->post('notes'),
					'status'         => 1,
				);

				if ($pkId > 0) {
					$old = $this->Reseller_model->getDetail($pkId);
					$form_data['updated_on']  = getDateTime();
					$form_data['updated_by']  = getAdminId();
					$form_data['inserted_on'] = $old['inserted_on'];
					$form_data['inserted_by'] = $old['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				$saved = $this->Reseller_model->saveData($form_data);
				if ($saved['id']) {
					// Flag the company as a reseller and (re)assign sub-customers.
					$this->Reseller_model->setResellerFlag($companyId, 1);
					$subCustomers = $this->input->post('sub_customer_ids') ?: array();
					$this->Reseller_model->assignSubCustomers($companyId, $subCustomers);

					$this->session->set_flashdata('admin_success', 'Reseller has been saved successfully.');
					redirect('whmazadmin/reseller/index');
					return;
				}
				$this->session->set_flashdata('admin_error', 'Failed to save reseller. Please try again.');
			} else {
				$this->session->set_flashdata('admin_error', 'Validation error. Please check the form and try again.');
			}
		}

		$data['detail'] = array();
		$data['sub_customer_ids'] = array();
		$currentCompanyId = 0;

		if (!empty($id_val)) {
			$data['detail'] = $this->Reseller_model->getDetail(safe_decode($id_val));
			if (!empty($data['detail'])) {
				$currentCompanyId = intval($data['detail']['company_id']);
				$subs = $this->Reseller_model->getSubCustomers($currentCompanyId);
				$data['sub_customer_ids'] = array_column($subs, 'id');
			}
		}

		$data['companies']            = $this->Reseller_model->getSelectableCompanies($currentCompanyId);
		$data['assignable_companies'] = $this->Reseller_model->getAssignableCompanies($currentCompanyId);
		$data['currencies']           = $this->Reseller_model->getCurrencies();

		$this->load->view('whmazadmin/reseller_manage', $data);
	}

	public function delete_records($id_val) {
		$entity = $this->Reseller_model->getDetail(safe_decode($id_val));
		if (!empty($entity)) {
			// Soft-delete the profile, drop the reseller flag, and detach subs.
			$this->Reseller_model->assignSubCustomers($entity['company_id'], array());
			$this->Reseller_model->setResellerFlag($entity['company_id'], 0);
			$this->Reseller_model->saveData(array(
				'id'         => $entity['id'],
				'status'     => 0,
				'deleted_on' => getDateTime(),
				'deleted_by' => getAdminId(),
			));
			$this->session->set_flashdata('admin_success', 'Reseller has been removed successfully.');
		}
		redirect('whmazadmin/reseller/index');
	}

	public function ssp_list_api() {
		$this->processRestCall();
		header('Content-Type: application/json');

		try {
			$params   = $this->input->get();
			$bindings = array();
			$where    = '';

			$sqlQuery = ssp_sql_query($params, "reseller_view", $bindings, $where);
			$data     = $this->Reseller_model->getDataTableRecords($sqlQuery, $bindings);
			$stats    = $this->Reseller_model->getStats();

			echo json_encode(array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Reseller_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Reseller_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data,
				"stats"           => $stats,
			));
			exit;
		} catch (Exception $e) {
			echo json_encode(array(
				"draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0,
				"data" => array(), "error" => $e->getMessage(),
			));
			exit;
		}
	}
}
