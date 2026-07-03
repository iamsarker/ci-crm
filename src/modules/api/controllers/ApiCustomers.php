<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiCustomers — the reseller's sub-customer companies.
 *
 *   GET  /api/v1/customers            list sub-customers            [customers:read]
 *   GET  /api/v1/customers/view/{id}  single sub-customer           [customers:read]
 *   POST /api/v1/customers/create     create a sub-customer         [customers:write]
 *
 * All customers are scoped to companies.parent_company_id = the reseller.
 */
class ApiCustomers extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Company_model');
	}

	public function index()
	{
		$this->requireScope('customers:read');
		list($limit, $offset, $page) = $this->pagination();

		$rows = $this->db->query(
			"SELECT id, name, email, mobile, phone, city, country, status, inserted_on
			 FROM companies
			 WHERE parent_company_id = ? AND status = 1
			 ORDER BY id DESC LIMIT ?, ?",
			array($this->company_id, $offset, $limit)
		)->result_array();

		$total = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM companies WHERE parent_company_id = ? AND status = 1",
			array($this->company_id)
		)->row_array();

		$this->ok(array(
			'customers' => array_map(array($this, 'shapeCustomer'), $rows),
			'pagination' => array('page' => $page, 'per_page' => $limit, 'total' => intval($total['cnt'])),
		));
	}

	public function view($id = 0)
	{
		$this->requireScope('customers:read');
		$row = $this->db->query(
			"SELECT * FROM companies WHERE id = ? AND parent_company_id = ? AND status = 1 LIMIT 1",
			array(intval($id), $this->company_id)
		)->row_array();

		if (empty($row)) {
			$this->fail(404, 'Customer not found.', 'not_found');
		}
		$this->ok(array('customer' => $this->shapeCustomer($row)));
	}

	public function create()
	{
		$this->requireScope('customers:write');
		$this->requireMethod('POST');

		$name  = trim((string) $this->param('name'));
		$email = trim((string) $this->param('email'));
		if ($name === '' || $email === '') {
			$this->fail(422, 'name and email are required.', 'validation_error');
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->fail(422, 'A valid email is required.', 'validation_error');
		}
		// Email must be unique among users (login identity).
		$dupe = $this->db->query("SELECT id FROM users WHERE email = ? LIMIT 1", array($email))->row_array();
		if (!empty($dupe)) {
			$this->fail(409, 'A user with this email already exists.', 'conflict');
		}

		$now = getDateTime();
		$company = array(
			'name'          => $name,
			'first_name'    => trim((string) $this->param('first_name')),
			'last_name'     => trim((string) $this->param('last_name')),
			'email'         => $email,
			'mobile'        => trim((string) $this->param('mobile')),
			'phone'         => trim((string) $this->param('phone')),
			'address'       => trim((string) $this->param('address')),
			'city'          => trim((string) $this->param('city')),
			'state'         => trim((string) $this->param('state')),
			'zip_code'      => trim((string) $this->param('zip_code')),
			'country'       => trim((string) $this->param('country')),
			'kam_id'        => 0,
			'kam_name'      => '',
			'parent_company_id' => $this->company_id,   // scope under this reseller
			'is_reseller'   => 0,
			'status'        => 1,
			'inserted_on'   => $now,
			'inserted_by'   => 0,
		);

		$resp = $this->Company_model->saveData($company);
		if (empty($resp['id'])) {
			$this->fail(500, 'Failed to create customer.', 'server_error');
		}
		$companyId = intval($resp['id']);

		// Create the owner login (mirrors admin Company::manage).
		$plainPassword = function_exists('generate_secure_password') ? generate_secure_password(12, true) : bin2hex(random_bytes(6));
		$this->db->insert('users', array(
			'first_name'  => $company['first_name'],
			'last_name'   => $company['last_name'],
			'email'       => $email,
			'mobile'      => $company['mobile'],
			'phone'       => $company['phone'],
			'designation' => 'Company Owner',
			'password'    => password_hash($plainPassword, PASSWORD_DEFAULT),
			'company_id'  => $companyId,
			'user_type'   => '0',
			'status'      => '1',
			'login_try'   => '0',
			'inserted_on' => $now,
			'inserted_by' => 0,
		));

		$this->ok(array(
			'customer' => array(
				'id'       => $companyId,
				'name'     => $name,
				'email'    => $email,
				'parent_company_id' => $this->company_id,
			),
			'login' => array(
				'email'    => $email,
				'password' => $plainPassword,   // returned once so the reseller can share it
			),
		), 201);
	}

	private function shapeCustomer($row)
	{
		return array(
			'id'          => intval($row['id']),
			'name'        => $row['name'],
			'email'       => $row['email'],
			'mobile'      => $row['mobile'] ?? null,
			'phone'       => $row['phone'] ?? null,
			'city'        => $row['city'] ?? null,
			'country'     => $row['country'] ?? null,
			'status'      => intval($row['status']),
			'created_at'  => $row['inserted_on'] ?? null,
		);
	}
}
