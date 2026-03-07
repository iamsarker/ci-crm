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

	/**
	 * Get order statistics for dashboard cards
	 *
	 * @return array Stats including total, active, this month counts and total revenue
	 */
	function getOrderStats() {
		$query = $this->db->query("
			SELECT
				COUNT(*) as total_orders,
				SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_orders,
				SUM(CASE WHEN YEAR(order_date) = YEAR(CURDATE()) AND MONTH(order_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month_orders,
				COALESCE(SUM(total_amount), 0) as total_revenue
			FROM orders
			WHERE status = 1
		");
		$data = $query->row_array();
		return array(
			'total_orders' => intval($data['total_orders'] ?? 0),
			'active_orders' => intval($data['active_orders'] ?? 0),
			'this_month_orders' => intval($data['this_month_orders'] ?? 0),
			'total_revenue' => floatval($data['total_revenue'] ?? 0)
		);
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

		// Get currency symbol from session or database
		$currencySymbol = "";
		if (empty($currencySymbol)) {
			$currency = $this->db->where('id', $order['currency_id'])->get('currencies')->row();
			$currencySymbol = $currency ? $currency->symbol : '$';
		}

		// Get invoice items for order details
		$orderItemsHtml = $this->_buildOrderItemsHtml($invoiceId, $currencySymbol);

		// Common placeholders
		$placeholders = array(
			'{client_name}' => ($company['first_name'] ?? '') . ' ' . ($company['last_name'] ?? ''),
			'{company_name_customer}' => !empty($company['name']) ? $company['name'] : '-',
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
			'{site_name}' => $appSettings->company_name,
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
		$subject = $template['subject'] ?? '';
		$body = $template['body'] ?? '';

		foreach ($placeholders as $key => $value) {
			// Ensure value is a string to avoid deprecation warnings
			$value = $value ?? '';
			$subject = str_replace($key, $value, $subject);
			$body = str_replace($key, $value, $body);
		}

		// Send email
		return sendHtmlEmail($toEmail, $subject, $body);
	}

	// =========================================
	// Order Management Methods
	// =========================================

	/**
	 * Get order with all domain and service items
	 * @param int $orderId Order ID
	 * @return array Order data with items
	 */
	function getOrderWithItems($orderId)
	{
		if (!is_numeric($orderId) || $orderId <= 0) {
			return array();
		}

		// Get order details with company info
		$sql = "SELECT o.*, c.name as company_name, c.first_name, c.last_name, c.email as company_email
				FROM orders o
				LEFT JOIN companies c ON o.company_id = c.id
				WHERE o.id = ? AND o.status IN (0,1,2,3,4,5,6)";
		$order = $this->db->query($sql, array(intval($orderId)))->row_array();

		if (empty($order)) {
			return array();
		}

		// Get domain items with registrar info
		$sqlDomains = "SELECT od.*, dr.name as registrar_name, dr.platform as registrar_platform
					   FROM order_domains od
					   LEFT JOIN dom_registers dr ON od.dom_register_id = dr.id
					   WHERE od.order_id = ?
					   ORDER BY od.id ASC";
		$order['domains'] = $this->db->query($sqlDomains, array(intval($orderId)))->result_array();

		// Get service items with package and server info
		$sqlServices = "SELECT os.*, ps.product_name, ps.server_id, ps.cp_package,
						       psg.group_name, s.name as server_name, s.hostname as server_hostname,
						       bc.cycle_name
						FROM order_services os
						LEFT JOIN product_services ps ON os.product_service_id = ps.id
						LEFT JOIN product_service_groups psg ON ps.product_service_group_id = psg.id
						LEFT JOIN servers s ON ps.server_id = s.id
						LEFT JOIN billing_cycle bc ON os.billing_cycle_id = bc.id
						WHERE os.order_id = ?
						ORDER BY os.id ASC";
		$order['services'] = $this->db->query($sqlServices, array(intval($orderId)))->result_array();

		return $order;
	}

	/**
	 * Get single domain item with registrar info
	 * @param int $domainId Order domain ID
	 * @return array Domain item data
	 */
	function getDomainItem($domainId)
	{
		if (!is_numeric($domainId) || $domainId <= 0) {
			return array();
		}

		$sql = "SELECT od.*, dr.name as registrar_name, dr.platform as registrar_platform,
				       o.company_id, o.currency_code
				FROM order_domains od
				LEFT JOIN dom_registers dr ON od.dom_register_id = dr.id
				LEFT JOIN orders o ON od.order_id = o.id
				WHERE od.id = ?";
		return $this->db->query($sql, array(intval($domainId)))->row_array();
	}

	/**
	 * Get single service item with package/server info
	 * @param int $serviceId Order service ID
	 * @return array Service item data
	 */
	function getServiceItem($serviceId)
	{
		if (!is_numeric($serviceId) || $serviceId <= 0) {
			return array();
		}

		$sql = "SELECT os.*, ps.product_name, ps.server_id, ps.cp_package,
				       psg.group_name, s.name as server_name, s.hostname as server_hostname,
				       bc.cycle_name, o.company_id, o.currency_code
				FROM order_services os
				LEFT JOIN product_services ps ON os.product_service_id = ps.id
				LEFT JOIN product_service_groups psg ON ps.product_service_group_id = psg.id
				LEFT JOIN servers s ON ps.server_id = s.id
				LEFT JOIN billing_cycle bc ON os.billing_cycle_id = bc.id
				LEFT JOIN orders o ON os.order_id = o.id
				WHERE os.id = ?";
		return $this->db->query($sql, array(intval($serviceId)))->row_array();
	}

	/**
	 * Cancel domain item
	 * @param int $domainId Order domain ID
	 * @param string $cancelType 'immediate' or 'end_of_period'
	 * @param string $reason Cancellation reason
	 * @return bool Success status
	 */
	function cancelDomain($domainId, $cancelType = 'immediate', $reason = '')
	{
		if (!is_numeric($domainId) || $domainId <= 0) {
			return false;
		}

		$domain = $this->getDomainItem($domainId);
		if (empty($domain)) {
			return false;
		}

		$data = array(
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => getAdminId()
		);

		if ($cancelType === 'immediate') {
			$data['status'] = 4; // cancelled
			$data['termination_date'] = date('Y-m-d');
			$data['suspension_reason'] = !empty($reason) ? $reason : 'Cancelled by admin';
		} else {
			// End of period - set termination_date to exp_date
			$data['termination_date'] = $domain['exp_date'];
			$data['suspension_reason'] = !empty($reason) ? $reason : 'Pending cancellation at end of period';
		}

		$this->db->where('id', intval($domainId));
		return $this->db->update('order_domains', $data);
	}

	/**
	 * Cancel service item
	 * @param int $serviceId Order service ID
	 * @param string $cancelType 'immediate' or 'end_of_period'
	 * @param string $reason Cancellation reason
	 * @return bool Success status
	 */
	function cancelService($serviceId, $cancelType = 'immediate', $reason = '')
	{
		if (!is_numeric($serviceId) || $serviceId <= 0) {
			return false;
		}

		$service = $this->getServiceItem($serviceId);
		if (empty($service)) {
			return false;
		}

		$data = array(
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => getAdminId()
		);

		if ($cancelType === 'immediate') {
			$data['status'] = 4; // terminated
			$data['termination_date'] = date('Y-m-d');
			$data['suspension_reason'] = !empty($reason) ? $reason : 'Cancelled by admin';
		} else {
			// End of period - set termination_date to exp_date
			$data['termination_date'] = $service['exp_date'];
			$data['suspension_reason'] = !empty($reason) ? $reason : 'Pending cancellation at end of period';
		}

		$this->db->where('id', intval($serviceId));
		return $this->db->update('order_services', $data);
	}

	/**
	 * Cancel entire order and all its items
	 * @param int $orderId Order ID
	 * @param string $cancelType 'immediate' or 'end_of_period'
	 * @param string $reason Cancellation reason
	 * @return bool Success status
	 */
	function cancelOrder($orderId, $cancelType = 'immediate', $reason = '')
	{
		if (!is_numeric($orderId) || $orderId <= 0) {
			return false;
		}

		$order = $this->getOrderWithItems($orderId);
		if (empty($order)) {
			return false;
		}

		// Cancel all domains
		foreach ($order['domains'] as $domain) {
			$this->cancelDomain($domain['id'], $cancelType, $reason);
		}

		// Cancel all services
		foreach ($order['services'] as $service) {
			$this->cancelService($service['id'], $cancelType, $reason);
		}

		// Cancel unpaid or partially paid invoices for this order
		$this->cancelUnpaidInvoices($orderId, $reason);

		// Update order status
		$orderData = array(
			'status' => $cancelType === 'immediate' ? 0 : 1, // 0 = cancelled/inactive
			'remarks' => ($order['remarks'] ? $order['remarks'] . "\n" : '') . 'Cancelled: ' . ($reason ?: 'By admin'),
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => getAdminId()
		);

		$this->db->where('id', intval($orderId));
		return $this->db->update('orders', $orderData);
	}

	/**
	 * Cancel unpaid or partially paid invoices for an order
	 * @param int $orderId Order ID
	 * @param string $reason Cancellation reason
	 * @return int Number of invoices cancelled
	 */
	function cancelUnpaidInvoices($orderId, $reason = '')
	{
		if (!is_numeric($orderId) || $orderId <= 0) {
			return 0;
		}

		// Find all unpaid or partially paid invoices for this order
		$invoices = $this->db->select('id, invoice_no, pay_status')
							 ->from('invoices')
							 ->where('order_id', intval($orderId))
							 ->where_in('pay_status', array('DUE', 'PARTIAL'))
							 ->where('status', 1)
							 ->get()
							 ->result_array();

		if (empty($invoices)) {
			return 0;
		}

		$cancelledCount = 0;
		$cancellationNote = 'Invoice cancelled due to order cancellation' . ($reason ? ': ' . $reason : '');

		foreach ($invoices as $invoice) {
			$invoiceData = array(
				'pay_status' => 'CANCELLED',
				'status' => 0, // Inactive
				'cancel_date' => date('Y-m-d'),
				'remarks' => $cancellationNote,
				'updated_on' => date('Y-m-d H:i:s'),
				'updated_by' => getAdminId()
			);

			$this->db->where('id', $invoice['id']);
			if ($this->db->update('invoices', $invoiceData)) {
				$cancelledCount++;
				log_message('info', 'Invoice #' . $invoice['invoice_no'] . ' cancelled due to order cancellation');
			}
		}

		return $cancelledCount;
	}

	/**
	 * Update domain registrar
	 * @param int $domainId Order domain ID
	 * @param int $newRegistrarId New registrar ID
	 * @return array Result with status and message
	 */
	function updateDomainRegistrar($domainId, $newRegistrarId)
	{
		if (!is_numeric($domainId) || $domainId <= 0 || !is_numeric($newRegistrarId) || $newRegistrarId <= 0) {
			return array('success' => false, 'message' => 'Invalid parameters');
		}

		$domain = $this->getDomainItem($domainId);
		if (empty($domain)) {
			return array('success' => false, 'message' => 'Domain not found');
		}

		$data = array(
			'dom_register_id' => intval($newRegistrarId),
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => getAdminId()
		);

		// If domain is active, set status to pending transfer
		if ($domain['status'] == 1) {
			$data['status'] = 5; // pending transfer
		}

		$this->db->where('id', intval($domainId));
		$result = $this->db->update('order_domains', $data);

		return array(
			'success' => $result,
			'message' => $result ? 'Registrar updated successfully' : 'Failed to update registrar',
			'needs_transfer' => ($domain['status'] == 1)
		);
	}

	/**
	 * Update service package/server
	 * @param int $serviceId Order service ID
	 * @param array $updateData Array with product_service_id, product_service_pricing_id, etc.
	 * @return array Result with status and message
	 */
	function updateServicePackage($serviceId, $updateData)
	{
		if (!is_numeric($serviceId) || $serviceId <= 0) {
			return array('success' => false, 'message' => 'Invalid service ID');
		}

		$service = $this->getServiceItem($serviceId);
		if (empty($service)) {
			return array('success' => false, 'message' => 'Service not found');
		}

		$data = array(
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => getAdminId()
		);

		if (!empty($updateData['product_service_id'])) {
			$data['product_service_id'] = intval($updateData['product_service_id']);
		}
		if (!empty($updateData['product_service_pricing_id'])) {
			$data['product_service_pricing_id'] = intval($updateData['product_service_pricing_id']);
		}

		$this->db->where('id', intval($serviceId));
		$result = $this->db->update('order_services', $data);

		return array(
			'success' => $result,
			'message' => $result ? 'Service updated successfully' : 'Failed to update service',
			'old_service' => $service
		);
	}

	/**
	 * Get all active registrars
	 * @return array List of registrars
	 */
	function getActiveRegistrars()
	{
		return $this->db->where('status', 1)
						->order_by('name', 'ASC')
						->get('dom_registers')
						->result_array();
	}

	/**
	 * Get all active servers
	 * @return array List of servers
	 */
	function getActiveServers()
	{
		return $this->db->where('status', 1)
						->where('deleted_on IS NULL', null, false)
						->order_by('name', 'ASC')
						->get('servers')
						->result_array();
	}

	/**
	 * Get packages by server
	 * @param int $serverId Server ID (optional)
	 * @return array List of packages
	 */
	function getPackagesByServer($serverId = null)
	{
		$this->db->select('ps.*, psg.group_name');
		$this->db->from('product_services ps');
		$this->db->join('product_service_groups psg', 'ps.product_service_group_id = psg.id', 'left');
		$this->db->where('ps.status', 1);
		$this->db->where('ps.deleted_on IS NULL', null, false);

		if ($serverId) {
			$this->db->where('ps.server_id', intval($serverId));
		}

		$this->db->order_by('psg.group_name', 'ASC');
		$this->db->order_by('ps.product_name', 'ASC');

		return $this->db->get()->result_array();
	}

}
?>
