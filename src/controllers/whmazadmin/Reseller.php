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
				// getByCompany() now returns soft-deleted rows too, because
				// `uniq_reseller_company` is a real UNIQUE index: a previously
				// removed reseller still occupies the slot. Re-adding one must
				// REACTIVATE that row, not insert a second and hit a duplicate
				// key with a generic "Failed to save reseller."
				$existing = $this->Reseller_model->getByCompany($companyId);
				if (!empty($existing) && intval($existing['id']) !== $pkId) {
					if (intval($existing['status']) === 1) {
						$this->session->set_flashdata('admin_error', 'This company is already a reseller.');
						redirect('whmazadmin/reseller/manage' . ($pkId > 0 ? '/' . safe_encode($pkId) : ''));
						return;
					}
					// Soft-deleted: adopt the existing row and bring it back.
					$pkId = intval($existing['id']);
				}

				$form_data = array(
					'id'             => $pkId,
					'company_id'     => $companyId,
					'discount_type'  => $this->input->post('discount_type') === 'fixed' ? 'fixed' : 'percent',
					'discount_value' => floatval($this->input->post('discount_value')),
					// credit_balance is deliberately NOT in this array (v2.0.0
					// Phase 3). It is now a CACHE of the reseller_credit_transactions
					// ledger, written only by Resellercredit_model inside a row
					// lock. Setting it from a form field would silently break the
					// invariant that balance == SUM(ledger) -- and it was never
					// safe anyway: it accepted any value, including a negative
					// one, with no author, no reason and no trail.
					// Corrections now go through Reseller Wallet -> Manual
					// Adjustment, which writes an audited ledger row.
					'currency_id'    => $this->input->post('currency_id') ?: null,
					'allow_api'      => $this->input->post('allow_api') ? 1 : 0,
					'notes'          => $this->input->post('notes'),
					'status'         => 1,
				);

				if ($pkId > 0) {
					$old = $this->Reseller_model->getDetail($pkId);
					// getDetail() filters status = 1, so a reactivated row reads
					// back empty — fall back to the row we already looked up.
					if (empty($old)) { $old = $existing; }
					$form_data['updated_on']  = getDateTime();
					$form_data['updated_by']  = getAdminId();
					$form_data['inserted_on'] = !empty($old['inserted_on']) ? $old['inserted_on'] : getDateTime();
					$form_data['inserted_by'] = !empty($old['inserted_by']) ? $old['inserted_by'] : getAdminId();
					// status => 1 above already revives it; clear the tombstone.
					$form_data['deleted_on']  = null;
					$form_data['deleted_by']  = null;
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

					// The reseller signs in through the ADMIN login page, so it
					// needs an admin_users row bound to its company.
					$loginMsg = $this->_provisionResellerLogin($companyId);

					$this->session->set_flashdata('admin_success', 'Reseller has been saved successfully.' . $loginMsg);
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

		// Existing admin login for this reseller, so the form can prefill.
		$data['adminLogin'] = !empty($data['detail']['company_id'])
			? $this->Reseller_model->getAdminUser($data['detail']['company_id'])
			: array();

		$this->load->view('whmazadmin/reseller_manage', $data);
	}

	public function delete_records($id_val) {
		$entity = $this->Reseller_model->getDetail(safe_decode($id_val));
		if (!empty($entity)) {
			// Deactivate as one unit. Leaving any of these three behind is a
			// live hole: a disabled profile with an enabled admin_users row
			// still logs in, and is_reseller=1 with no profile fails the
			// liveness check in a confusing way.
			$this->db->trans_start();

			$this->Reseller_model->assignSubCustomers($entity['company_id'], array());
			$this->Reseller_model->setResellerFlag($entity['company_id'], 0);
			// Kills the admin login. WHMAZADMIN_Controller::isLogin() re-checks
			// reseller liveness on every request, so any session they already
			// hold dies on their next click rather than at session expiry.
			$this->Reseller_model->setAdminUsersStatus($entity['company_id'], 0);
			$this->Reseller_model->saveData(array(
				'id'         => $entity['id'],
				'status'     => 0,
				'deleted_on' => getDateTime(),
				'deleted_by' => getAdminId(),
			));

			$this->db->trans_complete();

			$this->session->set_flashdata('admin_success', 'Reseller has been removed successfully.');
		}
		redirect('whmazadmin/reseller/index');
	}

	/**
	 * Create or update the reseller's admin_users login.
	 *
	 * Mirrors how Company::manage() provisions a customer's `users` owner row:
	 * generate a password, save, and flash it back once. On edit the password
	 * is only rewritten when a new one was actually typed.
	 *
	 * @return string Message fragment appended to the success toast.
	 */
	private function _provisionResellerLogin($companyId) {
		$company = $this->db->query(
			"SELECT name, email, first_name, last_name, mobile, phone FROM companies WHERE id = ? LIMIT 1",
			array(intval($companyId))
		)->row_array();
		if (empty($company)) return '';

		$existing = $this->Reseller_model->getAdminUser($companyId);

		$username = trim((string) $this->input->post('admin_username'));
		$email    = trim((string) $this->input->post('admin_email'));
		$password = (string) $this->input->post('admin_password');

		if ($email === '')    { $email    = $company['email']; }
		if ($username === '') { $username = !empty($existing['username']) ? $existing['username'] : $email; }
		if ($username === '' || $email === '') {
			return ' (no admin login created — the company has no email address)';
		}

		if ($this->Reseller_model->adminLoginExists($username, $email, !empty($existing['id']) ? $existing['id'] : 0)) {
			return ' (admin login NOT created — that username or email is already in use)';
		}

		$row = array(
			'admin_type'  => 1,
			'company_id'  => intval($companyId),
			'first_name'  => !empty($company['first_name']) ? $company['first_name'] : $company['name'],
			'last_name'   => (string) $company['last_name'],
			'username'    => $username,
			'email'       => $email,
			'mobile'      => $company['mobile'],
			'phone'       => $company['phone'],
			'designation' => 'Reseller',
			'status'      => 1,
		);

		if (!empty($existing['id'])) {
			$row['id']         = $existing['id'];
			$row['updated_on'] = getDateTime();
			$row['updated_by'] = getAdminId();
			// Only rewrite the password when one was actually typed, so simply
			// re-saving the reseller does not silently lock them out.
			if ($password !== '') {
				$row['password'] = password_hash($password, PASSWORD_DEFAULT);
			}
			$this->Reseller_model->saveAdminUser($row);
			return ($password !== '') ? ' Admin password updated.' : '';
		}

		$plain = ($password !== '') ? $password : generate_secure_password(12, true);
		$row['password']    = password_hash($plain, PASSWORD_DEFAULT);
		$row['admin_role_id'] = 0;
		$row['inserted_on'] = getDateTime();
		$row['inserted_by'] = getAdminId();
		$this->Reseller_model->saveAdminUser($row);

		// Shown once, same pattern as Company::manage()'s new_user_credentials.
		$this->session->set_flashdata('new_reseller_credentials', array(
			'username' => $username,
			'email'    => $email,
			'password' => $plain,
			'company'  => $company['name'],
		));
		return ' Admin login created — copy the password now.';
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
