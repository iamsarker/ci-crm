<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Apikey (admin)
 * -------------------------------------------------------------------------
 * CRUD for third-party REST API credentials. A key belongs to an API-enabled
 * reseller company. The secret is generated server-side and revealed to the
 * admin exactly once (via one-time flashdata) at creation / regeneration.
 *
 * @see src/models/Apikey_model.php
 */
class Apikey extends WHMAZADMIN_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Apikey_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index() {
		$data['results'] = array();
		$this->load->view('whmazadmin/apikey_list', $data);
	}

	public function manage($id_val = null) {
		if ($this->input->post()) {
			$this->form_validation->set_rules('name', 'Key Name', 'required|trim');
			$this->form_validation->set_rules('company_id', 'Reseller', 'required|greater_than[0]');

			if ($this->form_validation->run() == true) {

				$pkId = intval(safe_decode($this->input->post('id')));

				// Sanitise scopes against the canonical list.
				$postedScopes = $this->input->post('scopes') ?: array();
				$validScopes  = array_values(array_intersect($postedScopes, Apikey_model::availableScopes()));

				$form_data = array(
					'name'         => trim($this->input->post('name')),
					'scopes'       => json_encode($validScopes),
					'ip_whitelist' => trim($this->input->post('ip_whitelist')),
					'rate_limit'   => intval($this->input->post('rate_limit')),
					'expires_at'   => $this->input->post('expires_at') ?: null,
				);

				if ($pkId > 0) {
					// Edit: never touch company_id / secret here.
					$form_data['id']         = $pkId;
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();
					$this->Apikey_model->saveData($form_data);
					$this->session->set_flashdata('admin_success', 'API key has been updated.');
					redirect('whmazadmin/apikey/index');
					return;
				}

				// Create: generate credentials and reveal the secret once.
				$cred = $this->Apikey_model->generateCredentials();
				$form_data['company_id']     = intval($this->input->post('company_id'));
				$form_data['key_id']         = $cred['key_id'];
				$form_data['secret_hash']    = $cred['secret_hash'];
				$form_data['secret_preview'] = $cred['secret_preview'];
				$form_data['status']         = 1;
				$form_data['inserted_on']    = getDateTime();
				$form_data['inserted_by']    = getAdminId();

				$saved = $this->Apikey_model->saveData($form_data);
				if ($saved['id']) {
					$this->session->set_flashdata('new_api_credential', array(
						'key_id' => $cred['key_id'],
						'secret' => $cred['secret'],
						'name'   => $form_data['name'],
					));
					$this->session->set_flashdata('admin_success', 'API key created. Copy the secret now — it will not be shown again.');
					redirect('whmazadmin/apikey/index');
					return;
				}
				$this->session->set_flashdata('admin_error', 'Failed to create API key. Please try again.');
			} else {
				$this->session->set_flashdata('admin_error', 'Validation error. Please check the form and try again.');
			}
		}

		$data['detail'] = array();
		$data['selected_scopes'] = array();
		if (!empty($id_val)) {
			$data['detail'] = $this->Apikey_model->getDetail(safe_decode($id_val));
			if (!empty($data['detail']) && !empty($data['detail']['scopes'])) {
				$decoded = json_decode($data['detail']['scopes'], true);
				$data['selected_scopes'] = is_array($decoded) ? $decoded : array();
			}
		}

		// Pre-select a reseller when arriving via ?company=ID from the reseller list.
		$data['preselect_company'] = intval($this->input->get('company'));
		$data['resellers']  = $this->Apikey_model->getApiEnabledResellers();
		$data['scope_groups'] = Apikey_model::scopeGroups();

		$this->load->view('whmazadmin/apikey_manage', $data);
	}

	public function regenerate($id_val) {
		$id  = safe_decode($id_val);
		$key = $this->Apikey_model->getDetail($id);
		if (!empty($key)) {
			$secret = $this->Apikey_model->regenerateSecret($id);
			$this->session->set_flashdata('new_api_credential', array(
				'key_id' => $key['key_id'],
				'secret' => $secret,
				'name'   => $key['name'],
			));
			$this->session->set_flashdata('admin_success', 'Secret regenerated. Copy it now — it will not be shown again.');
		}
		redirect('whmazadmin/apikey/index');
	}

	public function revoke($id_val) {
		$this->Apikey_model->setStatus(safe_decode($id_val), 2);
		$this->session->set_flashdata('admin_success', 'API key revoked.');
		redirect('whmazadmin/apikey/index');
	}

	public function activate($id_val) {
		$this->Apikey_model->setStatus(safe_decode($id_val), 1);
		$this->session->set_flashdata('admin_success', 'API key re-activated.');
		redirect('whmazadmin/apikey/index');
	}

	public function delete_records($id_val) {
		$id = safe_decode($id_val);
		$this->Apikey_model->saveData(array(
			'id'         => $id,
			'status'     => 0,
			'deleted_on' => getDateTime(),
			'deleted_by' => getAdminId(),
		));
		$this->session->set_flashdata('admin_success', 'API key deleted.');
		redirect('whmazadmin/apikey/index');
	}

	public function ssp_list_api() {
		$this->processRestCall();
		header('Content-Type: application/json');

		try {
			$params   = $this->input->get();
			$bindings = array();
			$where    = '';

			// Show active (1) and revoked (2), hide soft-deleted (0).
			$sqlQuery = ssp_sql_query($params, "api_key_view", $bindings, $where, '', " `status` IN (1,2) ");
			$data     = $this->Apikey_model->getDataTableRecords($sqlQuery, $bindings);
			$stats    = $this->Apikey_model->getStats();

			echo json_encode(array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Apikey_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Apikey_model->countDataTableFilterRecords($where, $bindings)),
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
