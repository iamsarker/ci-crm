<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_pricing extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Domainpricing_model');
		$this->load->model('Common_model');
		$this->load->model('Pricing_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/domain_pricing_list', $data);
	}

	public function ssp_list_api()
	{
		$this->processRestCall();
		$params = $this->input->get();

		$bindings = array();
		$where = '';

		try {
			$sqlQuery = $this->Domainpricing_model->buildDataTableQuery($params, $bindings, $where);
			$data = $this->Domainpricing_model->getDataTableRecords($sqlQuery, $bindings);

			// Get pricing stats for dashboard cards
			$stats = $this->Domainpricing_model->getPricingStats();

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Domainpricing_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Domainpricing_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data,
				"stats"           => $stats
			);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('ssp_list_api', 'DataTables API', $e->getMessage());
			header('Content-Type: application/json');
			echo json_encode(array(
				"draw" => 0,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => array(),
				"error" => $e->getMessage()
			));
			exit;
		}
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('dom_extension_id', 'Domain Extension', 'required|trim');
			$this->form_validation->set_message('dom_extension_id', 'Domain Extension is required');

			$this->form_validation->set_rules('currency_id', 'Currency', 'required|trim');
			$this->form_validation->set_message('currency_id', 'Currency is required');

			$this->form_validation->set_rules('reg_period', 'Registration Period', 'required|trim|integer');
			$this->form_validation->set_message('reg_period', 'Registration Period is required and must be an integer');

			$this->form_validation->set_rules('price', 'Registration Price', 'required|trim|numeric');
			$this->form_validation->set_message('price', 'Registration Price is required and must be numeric');

			$this->form_validation->set_rules('transfer', 'Transfer Price', 'required|trim|numeric');
			$this->form_validation->set_message('transfer', 'Transfer Price is required and must be numeric');

			$this->form_validation->set_rules('renewal', 'Renewal Price', 'required|trim|numeric');
			$this->form_validation->set_message('renewal', 'Renewal Price is required and must be numeric');

			if ($this->form_validation->run() == true){

				// dom_pricing carries a UNIQUE key on (extension, currency,
				// reg_period) as of Phase 2, and saveData() uses REPLACE INTO.
				// On a collision REPLACE would DELETE the existing row and
				// insert a new one under a different id, orphaning every
				// order_domains.dom_pricing_id and price_overrides.pricing_id
				// that pointed at it. Refuse instead.
				$editingId = intval(safe_decode($this->input->post('id')));
				$clash = $this->Domainpricing_model->findByKey(
					$this->input->post('dom_extension_id'),
					$this->input->post('currency_id'),
					$this->input->post('reg_period')
				);
				if (!empty($clash) && intval($clash['id']) !== $editingId) {
					$this->session->set_flashdata('admin_error', 'A price for that extension, currency and registration period already exists. Edit that row instead.');
					redirect('whmazadmin/domain_pricing/index');
					return;
				}

				$form_data = array(
					'id'				=> safe_decode($this->input->post('id')),
					'dom_extension_id'	=> $this->input->post('dom_extension_id'),
					'currency_id'		=> $this->input->post('currency_id'),
					'reg_period'		=> $this->input->post('reg_period'),
					'price'				=> $this->input->post('price'),
					'transfer'			=> $this->input->post('transfer'),
					'renewal'			=> $this->input->post('renewal'),
					'status'       		=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Domainpricing_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Domainpricing_model->saveData($form_data)){

					// Reseller cost lives in price_overrides, never in
					// dom_pricing -- that table stays "platform retail" so the
					// direct-customer price path is provably untouched.
					// owner_company_id 0 = the default cost every reseller
					// inherits unless they have a negotiated one.
					$pricingId = intval($form_data['id']) > 0
						? intval($form_data['id'])
						: (int) $this->db->insert_id();
					if ($pricingId <= 0) {
						$row = $this->Domainpricing_model->findByKey(
							$form_data['dom_extension_id'], $form_data['currency_id'], $form_data['reg_period']
						);
						$pricingId = !empty($row) ? (int) $row['id'] : 0;
					}

					$costMsg = '';
					if ($pricingId > 0) {
						$res = $this->Pricing_model->saveCostOverride(1, $pricingId, 0, array(
							'price'          => $this->input->post('cost_price'),
							'transfer_price' => $this->input->post('cost_transfer'),
							'renewal_price'  => $this->input->post('cost_renewal'),
						));
						if (empty($res['success'])) {
							$costMsg = ' Reseller cost was not saved: ' . $res['message'];
						} elseif (!empty($res['lifted'])) {
							// A cost RISE can strand reseller selling prices
							// below the new floor. They were lifted; say so,
							// and tell the affected resellers by email.
							$n = count($res['lifted']);
							$this->Pricing_model->notifyLiftedResellers($res['lifted'], 1, $pricingId);
							$costMsg = ' ' . $n . ' reseller selling price(s) were below the new cost and have been raised to it; those resellers have been emailed.';
						}
					}

					$this->session->set_flashdata('admin_success', 'Domain pricing has been saved successfully.' . $costMsg);
					redirect("whmazadmin/domain_pricing/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Domainpricing_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		// Load dropdown data
		$data['extensions'] = $this->Domainpricing_model->getAllExtensions();
		$data['currencies'] = $this->Domainpricing_model->getAllCurrencies();

		// Existing platform-wide reseller cost for this row, if any. Blank
		// means "no cost set" -- the resolver then falls back to the reseller's
		// profile discount, and finally to retail.
		$data['cost'] = array();
		if (!empty($data['detail']['id'])) {
			$overrides = $this->Pricing_model->overridesFor(1, 0, 1); // item=domain, owner=platform, audience=cost
			$pid = (int) $data['detail']['id'];
			if (isset($overrides[$pid])) $data['cost'] = $overrides[$pid];
		}

		$this->load->view('whmazadmin/domain_pricing_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Domainpricing_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Domainpricing_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Domain pricing has been deleted successfully.');

		redirect('whmazadmin/domain_pricing/index');
	}

	public function prices()
	{
		$this->processRestCall();
		$rqData = $this->input->post();

		$domain_array = explode(".", $rqData['domain']);
		if ( count($domain_array) == 3 ){
			$extension = ".".$domain_array[1].'.'.$domain_array[2];
		} else if ( count($domain_array) == 2 ){
			$extension = ".".$domain_array[1];
		} else {
			$extension = "";
		}
		// Quote the selected CUSTOMER's price so the new-order form agrees with
		// what saveOrderItemTable() writes. company_id is attacker-controlled,
		// so anything outside the caller's tenant scope is refused outright
		// rather than quietly answered with platform retail.
		$companyId = !empty($rqData['company_id']) ? (int) $rqData['company_id'] : 0;
		if ($companyId > 0) {
			$this->guardCompany($companyId);
		}

		echo json_encode(buildSuccessResponse(
			$this->Common_model->getDomainPrices($rqData['currency_id'], $rqData['reg_period'], $extension, $companyId),
			"OK"
		));
	}

}
