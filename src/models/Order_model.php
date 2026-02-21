<?php 
class Order_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	public function countOrder($companyId) {
		$this->db->select('count(*) as cnt');
		$this->db->from("orders");
		$this->db->where(array(
			'status'=>'1',
			'company_id'=> $companyId
		));
		$data = $this->db->get();
		if ($data) {
			$res = $data->result();
			return $res[0]->cnt;
		} else {
			return 0;
		}
	}

	public function generateNumber($no_type) {
		$this->db->select('id, last_no');
		$this->db->from("gen_numbers");
		$this->db->where(array(
			'no_type'=>strtoupper($no_type),
		));
		$data = $this->db->get();

		$last_no = 0;
		$id = 0;
		if ($data) {
			$res = $data->result();
			$id = $res[0]->id;
			$last_no = $res[0]->last_no + 1; // increment with one
		} else {
			$last_no = 100 + 1; // increment with one
		}

		$record = array(
			'no_type'	=>strtoupper($no_type),
			'last_no'	=>$last_no,
		);
		$this->db->where('id', $id);
		if ($this->db->update('gen_numbers', $record)) {
			return $last_no;
		}
	}


	function loadOrderList($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('*');
		$this->db->from('orders');
		$this->db->where('status', 1);

		if( is_numeric($companyId) && $companyId > 0 ){
			$this->db->where('company_id', intval($companyId));
		}

		$this->db->order_by('id', 'DESC');

		if( is_numeric($limit) && $limit > 0 ){
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
 	}

	function loadOrderServices($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('*');
		$this->db->from('order_services');

		if( is_numeric($companyId) && $companyId > 0 ){
			$this->db->where('company_id', intval($companyId));
		}

		$this->db->order_by('id', 'DESC');

		if( is_numeric($limit) && $limit > 0 ){
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
	}

	function loadOrderServiceById($companyId, $id) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		if( !is_numeric($companyId) || !is_numeric($id) || $companyId <= 0 || $id <= 0 ){
			return array();
		}

		$this->db->select('os.*, o.currency_code, o.instructions');
		$this->db->from('order_services os');
		$this->db->join('orders o', 'os.order_id = o.id');
		$this->db->where('os.id', intval($id));
		$this->db->where('os.company_id', intval($companyId));

		$data = $this->db->get()->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function loadOrderDomains($companyId, $limit) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		$this->db->select('*');
		$this->db->from('order_domains');

		if( is_numeric($companyId) && $companyId > 0 ){
			$this->db->where('company_id', intval($companyId));
		}

		$this->db->order_by('id', 'DESC');

		if( is_numeric($limit) && $limit > 0 ){
			$this->db->limit(intval($limit));
		}

		$data = $this->db->get()->result_array();

		return $data;
	}

	function loadOrderDomainById($companyId, $id) {
		// SECURITY FIX: Use query builder to prevent SQL injection
		if( !is_numeric($companyId) || !is_numeric($id) || $companyId <= 0 || $id <= 0 ){
			return array();
		}

		$this->db->select('od.*, o.currency_code, o.instructions');
		$this->db->from('order_domains od');
		$this->db->join('orders o', 'od.order_id = o.id');
		$this->db->where('od.id', intval($id));
		$this->db->where('od.company_id', intval($companyId));

		$data = $this->db->get()->result_array();

		return !empty($data) ? $data[0] : array();
	}

 	function saveOrder($data){
 		$data['status'] = 1;
 		$data['inserted_on'] = getDateTime();
 		$data['inserted_by'] = getCustomerId();
 		if ($this->db->insert('orders', $data)) {
			return $this->db->insert_id();
		}
		return -1;
 	}

	function saveOrderService($data){
		if ($this->db->insert('order_services', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}

	function saveOrderDomain($data){
		if ($this->db->insert('order_domains', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}

	function saveInvoice($data){
		if ($this->db->insert('invoices', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}

	function saveInvoiceItem($data){
		if ($this->db->insert('invoice_items', $data)) {
			return $this->db->insert_id();
		}
		return -1;
	}


	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);
		return $data->result_array();
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("select count(id) as cnt from order_view where status=1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from order_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function getDetail($id) {
		$this->db->select('*');
		$this->db->from("orders");
		$this->db->where('id', $id);
		$data = $this->db->get();
		if ($data && $data->num_rows() > 0) {
			return $data->row_array();
		} else {
			return array();
		}
	}

	/**
	 * Update order_services record
	 * @param int $orderServiceId Order service ID
	 * @param array $data Data to update
	 * @return bool Success status
	 */
	function updateOrderService($orderServiceId, $data) {
		if (!is_numeric($orderServiceId) || $orderServiceId <= 0 || empty($data)) {
			return false;
		}

		try {
			$this->db->where('id', intval($orderServiceId));
			return $this->db->update('order_services', $data);
		} catch (Exception $e) {
			log_message('error', 'updateOrderService error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Update order domain record
	 * @param int $orderDomainId The order_domains.id
	 * @param array $data Data to update
	 * @return bool Success status
	 */
	function updateOrderDomain($orderDomainId, $data) {
		if (!is_numeric($orderDomainId) || $orderDomainId <= 0 || empty($data)) {
			return false;
		}

		try {
			$this->db->where('id', intval($orderDomainId));
			return $this->db->update('order_domains', $data);
		} catch (Exception $e) {
			log_message('error', 'updateOrderDomain error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get product_services info by pricing ID
	 * Used to get cp_package for auto-provisioning
	 * @param int $pricingId product_service_pricing.id
	 * @return array Product service info including cp_package
	 */
	function getProductServiceByPricingId($pricingId) {
		if (!is_numeric($pricingId) || $pricingId <= 0) {
			return array();
		}

		try {
			$sql = "SELECT ps.*, psp.id as pricing_id, psp.price
				FROM product_service_pricing psp
				JOIN product_services ps ON psp.product_service_id = ps.id
				WHERE psp.id = ? AND psp.status = 1";

			$result = $this->db->query($sql, array(intval($pricingId)))->row_array();

			return !empty($result) ? $result : array();
		} catch (Exception $e) {
			log_message('error', 'getProductServiceByPricingId error: ' . $e->getMessage());
			return array();
		}
	}

	// =========================================
	// Order Confirmation Emails
	// =========================================

	/**
	 * Send order confirmation emails to customer and admin
	 *
	 * @param int $orderId Order ID
	 * @param int $invoiceId Invoice ID
	 * @return array Results of email sending
	 */
	function sendOrderConfirmationEmails($orderId, $invoiceId)
	{
		$result = array('customer' => false, 'admin' => false);

		// Get order details
		$order = $this->getDetail($orderId);
		if (empty($order)) {
			log_message('error', 'sendOrderConfirmationEmails: Order not found - ID: ' . $orderId);
			return $result;
		}

		// Get invoice details
		$invoice = $this->db->where('id', $invoiceId)->get('invoices')->row_array();
		if (empty($invoice)) {
			log_message('error', 'sendOrderConfirmationEmails: Invoice not found - ID: ' . $invoiceId);
			return $result;
		}

		// Get customer/company details
		$company = $this->db->where('id', $order['company_id'])->get('companies')->row_array();
		if (empty($company)) {
			log_message('error', 'sendOrderConfirmationEmails: Company not found - ID: ' . $order['company_id']);
			return $result;
		}

		// Get app settings
		$appSettings = getAppSettings();

		// Get currency symbol
		$currency = $this->db->where('id', $order['currency_id'])->get('currencies')->row();
		$currencySymbol = $currency ? $currency->currency_symbol : '$';

		// Get invoice items for order details
		$orderItemsHtml = $this->_buildOrderItemsHtml($invoiceId, $currencySymbol);

		// Common placeholders
		$placeholders = array(
			'{client_name}' => $company['first_name'] . ' ' . $company['last_name'],
			'{company_name_customer}' => !empty($company['company_name']) ? $company['company_name'] : '-',
			'{client_email}' => $company['email'],
			'{order_no}' => $order['order_no'],
			'{order_date}' => date('F j, Y', strtotime($order['order_date'])),
			'{invoice_no}' => $invoice['invoice_no'],
			'{total_amount}' => number_format($invoice['total'], 2),
			'{currency_symbol}' => $currencySymbol,
			'{pay_status}' => $invoice['pay_status'],
			'{due_date}' => date('F j, Y', strtotime($invoice['due_date'])),
			'{order_items}' => $orderItemsHtml,
			'{company_name}' => $appSettings->company_name,
			'{invoice_url}' => base_url() . 'billing/pay/' . $invoice['invoice_uuid'],
			'{admin_order_url}' => base_url() . 'whmazadmin/order/view/' . $order['order_uuid'],
			'{admin_invoice_url}' => base_url() . 'whmazadmin/invoice/view/' . $order['company_id'] . '/' . $invoice['invoice_uuid']
		);

		// Send customer email
		$result['customer'] = $this->_sendOrderEmail(
			'order_confirmation',
			$company['email'],
			$placeholders
		);

		// Send admin email if notifications enabled
		$this->load->model('Syscnf_model');
		$notifyAdmin = $this->Syscnf_model->get('notify_admin_new_order', '1', 'bool');

		if ($notifyAdmin) {
			// Get admin notification email (from sys_cnf or app_settings)
			$adminEmail = $this->Syscnf_model->getValue('admin_notification_email');
			if (empty($adminEmail)) {
				$adminEmail = $appSettings->email;
			}

			if (!empty($adminEmail)) {
				$result['admin'] = $this->_sendOrderEmail(
					'admin_order_notification',
					$adminEmail,
					$placeholders
				);
			}
		}

		log_message('info', 'Order confirmation emails sent for order #' . $orderId .
			' - Customer: ' . ($result['customer'] ? 'Yes' : 'No') .
			', Admin: ' . ($result['admin'] ? 'Yes' : 'No'));

		return $result;
	}

	/**
	 * Build HTML table of order items
	 *
	 * @param int $invoiceId Invoice ID
	 * @param string $currencySymbol Currency symbol
	 * @return string HTML table of items
	 */
	private function _buildOrderItemsHtml($invoiceId, $currencySymbol)
	{
		$items = $this->db->where('invoice_id', $invoiceId)->get('invoice_items')->result_array();

		if (empty($items)) {
			return '<p>No items found.</p>';
		}

		$html = '<table style="border-collapse: collapse; width: 100%; max-width: 600px;">';
		$html .= '<tr style="background: #f5f5f5;">';
		$html .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Item</th>';
		$html .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Amount</th>';
		$html .= '</tr>';

		foreach ($items as $item) {
			$itemDesc = !empty($item['item_desc']) ? $item['item_desc'] : $item['item'];
			$html .= '<tr>';
			$html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($itemDesc) . '</td>';
			$html .= '<td style="padding: 8px; border: 1px solid #ddd; text-align: right;">' . $currencySymbol . number_format($item['total'], 2) . '</td>';
			$html .= '</tr>';
		}

		$html .= '</table>';

		return $html;
	}

	/**
	 * Send order email using template
	 *
	 * @param string $templateKey Email template key
	 * @param string $toEmail Recipient email
	 * @param array $placeholders Placeholder values
	 * @return bool Success status
	 */
	private function _sendOrderEmail($templateKey, $toEmail, $placeholders)
	{
		// Get email template
		$this->db->where('template_key', $templateKey);
		$this->db->where('status', 1);
		$template = $this->db->get('email_templates')->row_array();

		if (empty($template)) {
			log_message('error', '_sendOrderEmail: Template not found - ' . $templateKey);
			return false;
		}

		// Replace placeholders in subject and body
		$subject = $template['subject'];
		$body = $template['body'];

		foreach ($placeholders as $key => $value) {
			$subject = str_replace($key, $value, $subject);
			$body = str_replace($key, $value, $body);
		}

		// Send email
		return sendHtmlEmail($toEmail, $subject, $body);
	}

}
?>
