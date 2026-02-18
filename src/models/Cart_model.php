<?php

class Cart_model extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	function getServiceTypes()
	{

		$sql = "SELECT id, servce_type_name FROM product_service_types WHERE status=1 order by sort_order ";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function getServiceGroups()
	{

		$sql = "SELECT id, group_name, group_headline, tags FROM product_service_groups WHERE status=1 ";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function getProductServiceItemsDetails()
	{
		$sql = "SELECT ps.id, ps.product_name, ps.product_desc, ps.payment_type, psp.price FROM product_services ps WHERE ps.id = 1 ";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function getProductServiceItems($stype)
	{
		$cId = getCurrencyId();
		// SQL Injection Fix: Cast to integer and use parameterized query
		$stype = (int)$stype;
		$cId = (int)$cId;

		$sql = "SELECT ps.id, ps.product_name, ps.product_desc, CONCAT(CONCAT('[', GROUP_CONCAT(JSON_OBJECT('service_pricing_id', psp.id, 'price', psp.price, 'cycle_name', bc.cycle_name, 'currency', c.code))), ']') billing
				FROM product_services ps
				JOIN product_service_pricing psp on psp.product_service_id=ps.id
				JOIN billing_cycle bc on psp.billing_cycle_id=bc.id
				JOIN currencies c on psp.currency_id=c.id
				WHERE ps.product_service_group_id=? and psp.currency_id=? AND psp.status=1  and ps.is_hidden=0 and ps.status=1
				GROUP BY ps.id ";
		$data = $this->db->query($sql, array($stype, $cId))->result_array();
		return $data;
	}

	function getCurrencies()
	{

		$sql = "SELECT id, symbol, code, is_default FROM currencies WHERE status=1 ";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function deleteAllCarts($userId, $sessionId)
	{
		// SQL Injection Fix: Cast to integer and use parameterized query
		$userId = (int)$userId;
		$sessionId = (int)$sessionId;

		if( $userId > 0 ){
			$sql = " DELETE FROM add_to_carts where user_id=? ";
			$this->db->query($sql, array($userId));
		}

		if( $sessionId > 0 ){
			$sql = " DELETE FROM add_to_carts where customer_session_id=? ";
			$this->db->query($sql, array($sessionId));
		}
	}

	function getCartListData()
	{
		// SQL Injection Fix: Cast to integer and use parameterized query
		$customerId = (int)getCustomerId();
		$customerSessionId = (int)getCustomerSessionId();

		$sql = "SELECT * FROM add_to_carts where user_id=? or customer_session_id=?";
		$data = $this->db->query($sql, array($customerId, $customerSessionId))->result_array();
		return $data;
	}


	function getDomPricing()
	{
		// SQL Injection Fix: Cast to integer and use parameterized query
		$cId = (int)getCurrencyId();

		$sql = " SELECT dp.id, dp.currency_id, dp.price, dp.transfer, dp.renewal, de.extension
			FROM dom_pricing dp
			JOIN dom_extensions de on dp.dom_extension_id=de.id
			WHERE dp.status=1 AND dp.reg_period=1 AND dp.currency_id=? AND de.status=1 ";

		$data = $this->db->query($sql, array($cId))->result_array();
		return $data;
	}

	function getDomRegister($extension)
	{
		$sql = "SELECT id, name, domain_check_api, suggestion_api, domain_reg_api, price_list_api, auth_userid, auth_apikey FROM dom_registers WHERE status=1 and is_selected = 1 ";
		$data = $this->db->query($sql)->result_array();
		return $data[0];
	}

	function getCartServicePrice($id)
	{
		// SQL Injection Fix: Cast to integer and use parameterized query
		$id = (int)$id;

		$sql = "SELECT psp.price as item_price, psp.currency_id, psp.product_service_id, bc.id billing_cycle_id, bc.cycle_name
			FROM product_service_pricing psp
			JOIN billing_cycle bc on psp.billing_cycle_id=bc.id
			WHERE psp.id=? and psp.status=1 ";
		$data = $this->db->query($sql, array($id))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function getCartDomainPrice($id)
	{
		// SQL Injection Fix: Cast to integer and use parameterized query
		$id = (int)$id;

		$sql = "SELECT dp.price as item_price, dp.transfer, dp.renewal, dp.currency_id, dp.reg_period FROM dom_pricing dp WHERE dp.id=? and dp.status=1 ";
		$data = $this->db->query($sql, array($id))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function saveCart($data)
	{
		if ($this->db->insert('add_to_carts', $data)) {
			return $this->db->insert_id();
		}
		return false;
	}

	/**
	 * Get cart list with linked items (parent-child relationship)
	 * Returns hierarchical structure for display
	 */
	function getCartListWithChildren()
	{
		$customerId = (int)getCustomerId();
		$customerSessionId = (int)getCustomerSessionId();

		// Get all parent items (no parent_cart_id)
		$sql = "SELECT * FROM add_to_carts
				WHERE (user_id=? OR customer_session_id=?) AND parent_cart_id IS NULL
				ORDER BY id ASC";
		$parents = $this->db->query($sql, array($customerId, $customerSessionId))->result_array();

		// Get all child items
		$sql = "SELECT * FROM add_to_carts
				WHERE (user_id=? OR customer_session_id=?) AND parent_cart_id IS NOT NULL
				ORDER BY id ASC";
		$children = $this->db->query($sql, array($customerId, $customerSessionId))->result_array();

		// Attach children to parents
		foreach ($parents as &$parent) {
			$parent['children'] = array();
			foreach ($children as $child) {
				if ($child['parent_cart_id'] == $parent['id']) {
					$parent['children'][] = $child;
				}
			}
		}

		return $parents;
	}

	/**
	 * Delete cart item and its linked children
	 */
	function deleteCartWithChildren($cartId)
	{
		$cartId = (int)$cartId;
		$customerId = (int)getCustomerId();
		$customerSessionId = (int)getCustomerSessionId();

		// First delete children
		$this->db->where('parent_cart_id', $cartId);
		$this->db->where_group_start();
		$this->db->where('user_id', $customerId);
		$this->db->or_where('customer_session_id', $customerSessionId);
		$this->db->where_group_end();
		$this->db->delete('add_to_carts');

		// Then delete parent
		$this->db->where('id', $cartId);
		$this->db->where_group_start();
		$this->db->where('user_id', $customerId);
		$this->db->or_where('customer_session_id', $customerSessionId);
		$this->db->where_group_end();
		$this->db->delete('add_to_carts');

		return true;
	}

	/**
	 * Get cart item by ID
	 */
	function getCartById($cartId)
	{
		$cartId = (int)$cartId;
		return $this->db->get_where('add_to_carts', array('id' => $cartId))->row_array();
	}

	/**
	 * Get children of a cart item
	 */
	function getCartChildren($parentCartId)
	{
		$parentCartId = (int)$parentCartId;
		return $this->db->get_where('add_to_carts', array('parent_cart_id' => $parentCartId))->result_array();
	}

	/**
	 * Update cart item
	 */
	function updateCart($cartId, $data)
	{
		$this->db->where('id', (int)$cartId);
		return $this->db->update('add_to_carts', $data);
	}

	/**
	 * Get domain pricing by ID
	 */
	function getDomPricingById($domPricingId)
	{
		$domPricingId = (int)$domPricingId;
		$sql = "SELECT dp.*, de.extension, bc.cycle_name, bc.cycle_days
				FROM dom_pricing dp
				JOIN dom_extensions de ON dp.dom_extension_id = de.id
				LEFT JOIN billing_cycle bc ON bc.id = 1
				WHERE dp.id = ?";
		return $this->db->query($sql, array($domPricingId))->row_array();
	}

	/**
	 * Get product service pricing by ID with details
	 */
	function getProductServicePricingById($pricingId)
	{
		$pricingId = (int)$pricingId;
		$sql = "SELECT psp.*, ps.product_name, ps.product_desc,
				pst.servce_type_name, pst.key_name as service_type_key,
				bc.cycle_name, bc.cycle_days
				FROM product_service_pricing psp
				JOIN product_services ps ON psp.product_service_id = ps.id
				JOIN product_service_types pst ON ps.product_service_type_id = pst.id
				JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
				WHERE psp.id = ?";
		return $this->db->query($sql, array($pricingId))->row_array();
	}

}

?>
