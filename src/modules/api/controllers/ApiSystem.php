<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiSystem — API meta endpoints.
 *
 *   GET /api/v1/ping   authenticate + health check
 *   GET /api/v1/me     info about the authenticated key + reseller account
 */
class ApiSystem extends API_Controller
{
	/** Lightweight authenticated health check. */
	public function ping()
	{
		$this->ok(array(
			'pong'    => true,
			'api'     => 'whmaz',
			'version' => 'v1',
		));
	}

	/** Details about the authenticated key and its reseller account. */
	public function me()
	{
		$company = $this->db->query(
			"SELECT c.id, c.name, c.email, c.is_reseller,
			        rp.discount_type, rp.discount_value, rp.credit_balance, rp.currency_id,
			        cur.code AS currency_code
			 FROM companies c
			 LEFT JOIN reseller_profiles rp ON rp.company_id = c.id AND rp.status = 1
			 LEFT JOIN currencies cur ON cur.id = rp.currency_id
			 WHERE c.id = ? LIMIT 1",
			array($this->company_id)
		)->row_array();

		$subCount = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM companies WHERE parent_company_id = ? AND status = 1",
			array($this->company_id)
		)->row_array();

		// The login user that owns the reseller's cart — the same one
		// actAsCustomer() resolves for /cart/* and /checkout (owner preferred).
		$owner = $this->db->query(
			"SELECT id FROM users WHERE company_id = ? AND status = 1 ORDER BY user_type ASC, id ASC LIMIT 1",
			array($this->company_id)
		)->row_array();

		$this->ok(array(
			'key' => array(
				'name'         => $this->api_key['name'],
				'key_id'       => $this->api_key['key_id'],
				'scopes'       => $this->scopes,
				'rate_limit'   => intval($this->api_key['rate_limit']),
				'expires_at'   => $this->api_key['expires_at'],
			),
			'reseller' => array(
				'company_id'     => intval($this->company_id),                    // companies.id
				'customer_id'    => !empty($owner) ? intval($owner['id']) : null, // users.id (the cart owner)
				'name'           => $company['name'] ?? null,
				'email'          => $company['email'] ?? null,
				'discount_type'  => $company['discount_type'] ?? null,
				'discount_value' => isset($company['discount_value']) ? (float) $company['discount_value'] : 0,
				'credit_balance' => isset($company['credit_balance']) ? (float) $company['credit_balance'] : 0,
				'currency'       => $company['currency_code'] ?? null,
				'sub_customers'  => intval($subCount['cnt'] ?? 0),
			),
		));
	}
}
