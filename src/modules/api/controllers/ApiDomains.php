<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiDomains — availability check + the reseller's registered domains.
 *
 *   GET /api/v1/domains/check?domain=example.com   availability     [domains:read]
 *   GET /api/v1/domains                            list domains     [domains:read]
 *   GET /api/v1/domains/view/{id}                  single domain    [domains:read]
 *
 * Domain registration/renewal happens by placing an order (POST /orders) and
 * paying its invoice (POST /invoices/pay), which triggers provisioning.
 */
class ApiDomains extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('domain');
		$this->load->model('Cart_model');
	}

	public function check()
	{
		$this->requireScope('domains:read');
		$domain = trim((string) $this->param('domain'));
		if ($domain === '' || strpos($domain, '.') === false) {
			$this->fail(422, 'A valid domain (e.g. example.com) is required.', 'validation_error');
		}

		$registrar = $this->db->query(
			"SELECT * FROM dom_registers WHERE status = 1 AND is_selected = 1 LIMIT 1"
		)->row_array();
		if (empty($registrar)) {
			$this->fail(503, 'No default domain registrar configured.', 'registrar_unavailable');
		}

		$platform = strtolower($registrar['platform']);
		if ($platform !== 'namecheap') {
			// Availability lookup is only implemented for Namecheap today.
			$this->fail(501, 'Availability check is not supported for the configured registrar (' . $registrar['platform'] . ').', 'not_implemented');
		}

		$result = namecheap_check_domain($registrar, $domain);
		if (empty($result['success'])) {
			$this->fail(502, !empty($result['error']) ? $result['error'] : 'Registrar lookup failed.', 'registrar_error');
		}

		$parts      = explode('.', $domain);
		$ext        = '.' . implode('.', array_slice($parts, 1));
		$currencyId = intval($this->param('currency_id')) ?: 1;
		$price      = $this->_priceFor($this->Cart_model->getDomPricing(), $ext, $currencyId);

		$this->ok(array(
			'domain'         => $domain,
			'available'      => !empty($result['available']),
			'premium'        => !empty($result['premium']),
			'registrar'      => $registrar['platform'],
			'price'          => !empty($price['price']) ? (float) $price['price'] : null,
			'dom_pricing_id' => !empty($price['id']) ? intval($price['id']) : 0,  // -> cart/add_domain
		));
	}

	/**
	 * Domain name suggestions for a keyword, with pricing.
	 *   GET /api/v1/domains/suggest?keyword=example[&currency_id=1]
	 * Mirrors the storefront's cart/get_domain_suggestions (Namecheap → common
	 * TLD availability; ResellerClub/Resell.biz → registrar suggestion API).
	 */
	public function suggest()
	{
		$this->requireScope('domains:read');
		$keyword = trim((string) ($this->param('keyword') ?: $this->param('domkeyword') ?: $this->param('domain')));
		if ($keyword === '') {
			$this->fail(422, 'A keyword is required.', 'validation_error');
		}
		$currencyId = intval($this->param('currency_id')) ?: 1;

		$parts     = explode('.', $keyword);
		$sld       = $parts[0];
		$extension = count($parts) > 1 ? end($parts) : 'com';

		$registrar = $this->Cart_model->getDomRegister('.' . $extension);
		if (empty($registrar)) {
			$this->fail(503, 'No default domain registrar configured.', 'registrar_unavailable');
		}
		$priceList = $this->Cart_model->getDomPricing();
		$platform  = strtolower($registrar['platform'] ?? 'resellerclub');
		$out = array();

		if ($platform === 'namecheap') {
			foreach (array('com', 'net', 'org', 'io', 'co') as $tld) {
				$full  = $sld . '.' . $tld;
				$check = namecheap_check_domain($registrar, $full);
				if (!empty($check['success']) && !empty($check['available'])) {
					$price = $this->_priceFor($priceList, '.' . $tld, $currencyId);
					if (!empty($price)) $out[] = $this->_suggestionRow($full, $price);
				}
			}
		} else {
			// ResellerClub / Resell.biz suggestion API
			if (!empty($registrar['suggestion_api'])) {
				$url = $registrar['suggestion_api']
					. 'auth-userid=' . $registrar['auth_userid']
					. '&api-key=' . $registrar['auth_apikey']
					. '&keyword=' . rawurlencode($sld)
					. '&tld-only=' . rawurlencode($extension);
				$list = $this->curlGetJson($url);
				if (is_array($list)) {
					foreach ($list as $domainName => $info) {
						if (isset($info['status']) && $info['status'] === 'available') {
							$eparts = explode('.', $domainName);
							$price  = $this->_priceFor($priceList, '.' . end($eparts), $currencyId);
							if (!empty($price)) $out[] = $this->_suggestionRow($domainName, $price);
						}
					}
				}
			}
		}

		$this->ok(array('keyword' => $keyword, 'suggestions' => $out));
	}

	/**
	 * Transfer/renewal price for a domain extension.
	 *   GET /api/v1/domains/transfer_price?domain=example.com[&currency_id=1]
	 */
	public function transfer_price()
	{
		$this->requireScope('domains:read');
		$domain = strtolower(trim((string) $this->param('domain')));
		$domain = preg_replace('/^(https?:\/\/)?(www\.)?/i', '', $domain);
		$parts  = explode('.', $domain);
		if ($domain === '' || count($parts) < 2) {
			$this->fail(422, 'A valid domain (e.g. example.com) is required.', 'validation_error');
		}
		$currencyId = intval($this->param('currency_id')) ?: 1;
		$ext   = '.' . end($parts);
		$price = $this->_priceFor($this->Cart_model->getDomPricing(), $ext, $currencyId);

		if (empty($price) || empty($price['transfer'])) {
			$this->fail(404, "Transfer is not available for {$ext}.", 'not_found');
		}
		$this->ok(array(
			'domain'         => $domain,
			'extension'      => $ext,
			'transfer_price' => (float) $price['transfer'],
			'renewal_price'  => !empty($price['renewal']) ? (float) $price['renewal'] : 0,
			'dom_pricing_id' => !empty($price['id']) ? intval($price['id']) : 0,
		));
	}

	private function _priceFor($priceList, $ext, $currencyId)
	{
		foreach ((array) $priceList as $row) {
			if ($row['extension'] === $ext && intval($row['currency_id']) === intval($currencyId)) {
				return $row;
			}
		}
		return array();
	}

	private function _suggestionRow($name, $price)
	{
		return array(
			'domain'         => $name,
			'price'          => !empty($price['price']) ? (float) $price['price'] : 0.0,
			'transfer'       => !empty($price['transfer']) ? (float) $price['transfer'] : 0.0,
			'renewal'        => !empty($price['renewal']) ? (float) $price['renewal'] : 0.0,
			'dom_pricing_id' => !empty($price['id']) ? intval($price['id']) : 0,
		);
	}

	public function index()
	{
		$this->requireScope('domains:read');
		list($limit, $offset, $page) = $this->pagination();
		$ids = $this->scopedCompanyIds();
		$in  = implode(',', array_fill(0, count($ids), '?'));

		$rows = $this->db->query(
			"SELECT od.id, od.company_id, od.domain, od.status, od.order_type,
			        od.reg_date, od.exp_date, od.next_renewal_date, od.recurring_amount,
			        dr.name AS registrar_name
			 FROM order_domains od
			 LEFT JOIN dom_registers dr ON dr.id = od.dom_register_id
			 WHERE od.company_id IN ($in)
			 ORDER BY od.id DESC LIMIT ?, ?",
			array_merge($ids, array($offset, $limit))
		)->result_array();

		$total = $this->db->query("SELECT COUNT(*) AS cnt FROM order_domains WHERE company_id IN ($in)", $ids)->row_array();

		$this->ok(array(
			'domains'    => array_map(array($this, 'shapeDomain'), $rows),
			'pagination' => array('page' => $page, 'per_page' => $limit, 'total' => intval($total['cnt'])),
		));
	}

	public function view($id = 0)
	{
		$this->requireScope('domains:read');
		$ids = $this->scopedCompanyIds();
		$in  = implode(',', array_fill(0, count($ids), '?'));

		$row = $this->db->query(
			"SELECT od.*, dr.name AS registrar_name FROM order_domains od
			 LEFT JOIN dom_registers dr ON dr.id = od.dom_register_id
			 WHERE od.id = ? AND od.company_id IN ($in) LIMIT 1",
			array_merge(array(intval($id)), $ids)
		)->row_array();

		if (empty($row)) {
			$this->fail(404, 'Domain not found.', 'not_found');
		}
		$this->ok(array('domain' => $this->shapeDomain($row)));
	}

	private function shapeDomain($row)
	{
		$statusMap = array(0 => 'pending_registration', 1 => 'active', 2 => 'expired', 3 => 'grace_period', 4 => 'cancelled', 5 => 'pending_transfer');
		return array(
			'id'                => intval($row['id']),
			'company_id'        => intval($row['company_id']),
			'domain'            => $row['domain'],
			'status'            => $statusMap[intval($row['status'])] ?? 'unknown',
			'status_code'       => intval($row['status']),
			'registrar'         => $row['registrar_name'] ?? null,
			'reg_date'          => $row['reg_date'] ?? null,
			'exp_date'          => $row['exp_date'] ?? null,
			'next_renewal_date' => $row['next_renewal_date'] ?? null,
			'recurring_amount'  => isset($row['recurring_amount']) ? (float) $row['recurring_amount'] : null,
		);
	}
}
