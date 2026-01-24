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
		$sql = "SELECT ps.id, ps.product_name, ps.product_desc, CONCAT(CONCAT('[', GROUP_CONCAT(JSON_OBJECT('service_pricing_id', psp.id, 'price', psp.price, 'cycle_name', bc.cycle_name, 'currency', c.code))), ']') billing 
				FROM product_services ps 
				JOIN product_service_pricing psp on psp.product_service_id=ps.id 
				JOIN billing_cycle bc on psp.billing_cycle_id=bc.id 
				JOIN currencies c on psp.currency_id=c.id 
				WHERE ps.product_service_group_id=$stype and psp.currency_id=$cId AND psp.status=1  and ps.is_hidden=0 and ps.status=1 
				GROUP BY ps.id ";
		$data = $this->db->query($sql)->result_array();
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
		if( $userId > 0 ){
			$sql = " DELETE FROM add_to_carts where user_id=$userId ";
			$this->db->query($sql);
		}

		if( $sessionId > 0 ){
			$sql = " DELETE FROM add_to_carts where customer_session_id=$sessionId ";
			$this->db->query($sql);
		}
	}

	function getCartListData()
	{
		$customerId = getCustomerId();
		$customerSessionId = getCustomerSessionId();

		$sql = "SELECT * FROM add_to_carts where user_id=$customerId or customer_session_id=$customerSessionId";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}


	function getDomPricing()
	{
		$cId = getCurrencyId();
		$sql = " SELECT dp.id, dp.currency_id, dp.price, dp.transfer, dp.renewal, de.extension
			FROM dom_pricing dp
			JOIN dom_extensions de on dp.dom_extension_id=de.id
			WHERE dp.status=1 AND dp.reg_period=1 AND dp.currency_id=$cId AND de.status=1 ";

		$data = $this->db->query($sql)->result_array();
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
		$sql = "SELECT psp.price as item_price, psp.currency_id, bc.id billing_cycle_id, bc.cycle_name 
			FROM product_service_pricing psp 
			JOIN billing_cycle bc on psp.billing_cycle_id=bc.id 
			WHERE psp.id=$id and psp.status=1 ";
		$data = $this->db->query($sql)->result_array();
		return $data[0];
	}

	function getCartDomainPrice($id)
	{
		$sql = "SELECT dp.price as item_price, dp.currency_id, dp.reg_period  FROM dom_pricing dp WHERE dp.id=$id and dp.status=1 ";
		$data = $this->db->query($sql)->result_array();
		return $data[0];
	}

	function saveCart($data)
	{
		if ($this->db->insert('add_to_carts', $data)) {
			return true;
		}
		return false;
	}

}

?>
