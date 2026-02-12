<?php
/**
 * Cronjob Model
 *
 * Handles automated tasks like renewal invoice generation, dunning, etc.
 * Following WHMCS best practices for billing automation.
 */
class Cronjob_model extends CI_Model
{
	// Days before expiry to generate renewal invoice
	const RENEWAL_INVOICE_DAYS_BEFORE = 15;

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get services expiring within specified days that need renewal invoices
	 * Excludes: one-time services, already invoiced renewals, inactive services
	 *
	 * @param int $daysBeforeExpiry Days before expiry to look for
	 * @return array List of expiring services
	 */
	function getExpiringServices($daysBeforeExpiry = 15)
	{
		$targetDate = date('Y-m-d', strtotime("+{$daysBeforeExpiry} days"));
		$today = date('Y-m-d');

		$sql = "SELECT os.*,
					o.currency_id, o.currency_code,
					c.id as company_id, c.name as company_name, c.email as company_email,
					c.first_name, c.last_name,
					ps.product_name,
					bc.cycle_key, bc.cycle_name, bc.cycle_days
				FROM order_services os
				JOIN orders o ON os.order_id = o.id
				JOIN companies c ON os.company_id = c.id
				JOIN product_services ps ON os.product_service_id = ps.id
				JOIN billing_cycle bc ON os.billing_cycle_id = bc.id
				WHERE os.status = 1
				AND os.next_due_date <= ?
				AND os.next_due_date >= ?
				AND bc.cycle_days > 0
				AND os.auto_renew = 1
				AND os.deleted_on IS NULL
				AND NOT EXISTS (
					SELECT 1 FROM invoice_items ii
					JOIN invoices i ON ii.invoice_id = i.id
					WHERE ii.ref_id = os.id
					AND ii.item_type = 2
					AND i.status = 1
					AND ii.billing_period_start = os.next_due_date
				)
				ORDER BY os.next_due_date ASC";

		return $this->db->query($sql, array($targetDate, $today))->result_array();
	}

	/**
	 * Get domains expiring within specified days that need renewal invoices
	 * Excludes: already invoiced renewals, inactive domains
	 *
	 * @param int $daysBeforeExpiry Days before expiry to look for
	 * @return array List of expiring domains
	 */
	function getExpiringDomains($daysBeforeExpiry = 15)
	{
		$targetDate = date('Y-m-d', strtotime("+{$daysBeforeExpiry} days"));
		$today = date('Y-m-d');

		$sql = "SELECT od.*,
					o.currency_id, o.currency_code,
					c.id as company_id, c.name as company_name, c.email as company_email,
					c.first_name, c.last_name,
					dp.renewal as renewal_price, de.extension
				FROM order_domains od
				JOIN orders o ON od.order_id = o.id
				JOIN companies c ON od.company_id = c.id
				JOIN dom_pricing dp ON od.dom_pricing_id = dp.id AND dp.currency_id = o.currency_id
				JOIN dom_extensions de ON dp.dom_extension_id = de.id
				WHERE od.status = 1
				AND od.next_due_date <= ?
				AND od.next_due_date >= ?
				AND od.auto_renew = 1
				AND od.deleted_on IS NULL
				AND NOT EXISTS (
					SELECT 1 FROM invoice_items ii
					JOIN invoices i ON ii.invoice_id = i.id
					WHERE ii.ref_id = od.id
					AND ii.item_type = 1
					AND i.status = 1
					AND ii.billing_period_start = od.next_due_date
				)
				ORDER BY od.next_due_date ASC";

		return $this->db->query($sql, array($targetDate, $today))->result_array();
	}

	/**
	 * Create renewal invoice for a service
	 *
	 * @param array $service Service data from getExpiringServices()
	 * @return array Invoice data with success status
	 */
	function createServiceRenewalInvoice($service)
	{
		$this->db->trans_start();

		try {
			// Calculate new billing period
			$billingPeriodStart = $service['next_due_date'];
			$billingPeriodEnd = date('Y-m-d', strtotime($billingPeriodStart . " +{$service['cycle_days']} days"));
			$renewalAmount = floatval($service['recurring_amount']);

			// Create invoice
			$invoice = array(
				'invoice_uuid' => gen_uuid(),
				'company_id' => $service['company_id'],
				'order_id' => $service['order_id'],
				'currency_id' => $service['currency_id'],
				'currency_code' => $service['currency_code'],
				'invoice_no' => $this->generateNumber('INVOICE'),
				'sub_total' => $renewalAmount,
				'tax' => 0.00,
				'vat' => 0.00,
				'total' => $renewalAmount,
				'order_date' => date('Y-m-d'),
				'due_date' => $service['next_due_date'],
				'status' => 1,
				'pay_status' => 'DUE',
				'remarks' => 'Auto-generated renewal invoice',
				'inserted_on' => date('Y-m-d H:i:s'),
				'inserted_by' => 0 // System generated
			);

			$this->db->insert('invoices', $invoice);
			$invoiceId = $this->db->insert_id();

			// Create invoice item
			$itemDesc = "Renewal - {$service['product_name']}";
			if (!empty($service['hosting_domain'])) {
				$itemDesc .= " ({$service['hosting_domain']})";
			}
			$itemDesc .= " - {$service['cycle_name']}";
			$itemDesc .= " ({$billingPeriodStart} to {$billingPeriodEnd})";

			$invoiceItem = array(
				'invoice_id' => $invoiceId,
				'item' => 'Service Renewal',
				'item_desc' => $itemDesc,
				'item_type' => 2, // 2 = service
				'ref_id' => $service['id'],
				'billing_cycle_id' => $service['billing_cycle_id'],
				'quantity' => 1,
				'unit_price' => $renewalAmount,
				'discount' => 0.00,
				'sub_total' => $renewalAmount,
				'tax' => 0.00,
				'vat' => 0.00,
				'total' => $renewalAmount,
				'billing_period_start' => $billingPeriodStart,
				'billing_period_end' => $billingPeriodEnd,
				'inserted_on' => date('Y-m-d H:i:s'),
				'inserted_by' => 0
			);

			$this->db->insert('invoice_items', $invoiceItem);

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return array('success' => false, 'error' => 'Database transaction failed');
			}

			$invoice['id'] = $invoiceId;
			return array(
				'success' => true,
				'invoice' => $invoice,
				'service' => $service
			);

		} catch (Exception $e) {
			$this->db->trans_rollback();
			log_message('error', 'createServiceRenewalInvoice error: ' . $e->getMessage());
			return array('success' => false, 'error' => $e->getMessage());
		}
	}

	/**
	 * Create renewal invoice for a domain
	 *
	 * @param array $domain Domain data from getExpiringDomains()
	 * @return array Invoice data with success status
	 */
	function createDomainRenewalInvoice($domain)
	{
		$this->db->trans_start();

		try {
			// Calculate new billing period (domains are always yearly based on reg_period)
			$billingPeriodStart = $domain['next_due_date'];
			$yearsToRenew = !empty($domain['reg_period']) ? intval($domain['reg_period']) : 1;
			$billingPeriodEnd = date('Y-m-d', strtotime($billingPeriodStart . " +{$yearsToRenew} years"));
			$renewalAmount = floatval($domain['renewal_price']) * $yearsToRenew;

			// Create invoice
			$invoice = array(
				'invoice_uuid' => gen_uuid(),
				'company_id' => $domain['company_id'],
				'order_id' => $domain['order_id'],
				'currency_id' => $domain['currency_id'],
				'currency_code' => $domain['currency_code'],
				'invoice_no' => $this->generateNumber('INVOICE'),
				'sub_total' => $renewalAmount,
				'tax' => 0.00,
				'vat' => 0.00,
				'total' => $renewalAmount,
				'order_date' => date('Y-m-d'),
				'due_date' => $domain['next_due_date'],
				'status' => 1,
				'pay_status' => 'DUE',
				'remarks' => 'Auto-generated domain renewal invoice',
				'inserted_on' => date('Y-m-d H:i:s'),
				'inserted_by' => 0
			);

			$this->db->insert('invoices', $invoice);
			$invoiceId = $this->db->insert_id();

			// Create invoice item
			$itemDesc = "Domain Renewal - {$domain['domain']} - {$yearsToRenew} Year(s)";
			$itemDesc .= " ({$billingPeriodStart} to {$billingPeriodEnd})";

			$invoiceItem = array(
				'invoice_id' => $invoiceId,
				'item' => 'Domain Renewal',
				'item_desc' => $itemDesc,
				'item_type' => 1, // 1 = domain
				'ref_id' => $domain['id'],
				'billing_cycle_id' => null, // Domains don't use billing_cycle
				'quantity' => $yearsToRenew,
				'unit_price' => floatval($domain['renewal_price']),
				'discount' => 0.00,
				'sub_total' => $renewalAmount,
				'tax' => 0.00,
				'vat' => 0.00,
				'total' => $renewalAmount,
				'billing_period_start' => $billingPeriodStart,
				'billing_period_end' => $billingPeriodEnd,
				'inserted_on' => date('Y-m-d H:i:s'),
				'inserted_by' => 0
			);

			$this->db->insert('invoice_items', $invoiceItem);

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				return array('success' => false, 'error' => 'Database transaction failed');
			}

			$invoice['id'] = $invoiceId;
			return array(
				'success' => true,
				'invoice' => $invoice,
				'domain' => $domain
			);

		} catch (Exception $e) {
			$this->db->trans_rollback();
			log_message('error', 'createDomainRenewalInvoice error: ' . $e->getMessage());
			return array('success' => false, 'error' => $e->getMessage());
		}
	}

	/**
	 * Generate sequential number for invoices/orders
	 *
	 * @param string $noType Type: ORDER, INVOICE
	 * @return int Generated number
	 */
	function generateNumber($noType)
	{
		$this->db->select('id, last_no');
		$this->db->from('gen_numbers');
		$this->db->where('no_type', strtoupper($noType));
		$data = $this->db->get();

		$lastNo = 100;
		$id = 0;

		if ($data && $data->num_rows() > 0) {
			$res = $data->row();
			$id = $res->id;
			$lastNo = $res->last_no + 1;
		} else {
			$lastNo = 101;
		}

		$this->db->where('id', $id);
		$this->db->update('gen_numbers', array(
			'no_type' => strtoupper($noType),
			'last_no' => $lastNo
		));

		return $lastNo;
	}

	/**
	 * Get email template by key
	 *
	 * @param string $templateKey Template key
	 * @return array Template data
	 */
	function getEmailTemplate($templateKey)
	{
		$this->db->select('*');
		$this->db->from('email_templates');
		$this->db->where('template_key', $templateKey);
		$this->db->where('status', 1);
		$data = $this->db->get();

		if ($data && $data->num_rows() > 0) {
			return $data->row_array();
		}
		return array();
	}

	/**
	 * Get app settings from app_settings table
	 *
	 * @return array Settings row as associative array
	 */
	function getAppSettings()
	{
		$this->db->select('*');
		$this->db->from('app_settings');
		$this->db->limit(1);
		$data = $this->db->get()->row_array();

		return !empty($data) ? $data : array();
	}

	/**
	 * Get a single system configuration value from sys_cnf table
	 *
	 * @param string $key Configuration key
	 * @param mixed $default Default value if not found
	 * @return mixed Configuration value
	 */
	function getSysConfig($key, $default = null)
	{
		$this->db->select('cnf_val');
		$this->db->from('sys_cnf');
		$this->db->where('cnf_key', $key);
		$data = $this->db->get()->row();

		return $data ? $data->cnf_val : $default;
	}

	/**
	 * Log cronjob execution
	 *
	 * @param string $jobType Type of job
	 * @param string $status Status (success/failed)
	 * @param string $details Details/message
	 * @param int $itemsProcessed Number of items processed
	 */
	function logCronjobExecution($jobType, $status, $details = '', $itemsProcessed = 0)
	{
		// Check if cron_jobs table exists and has the right structure
		$this->db->insert('cron_jobs', array(
			'job_type' => $jobType,
			'status' => $status,
			'details' => $details,
			'items_processed' => $itemsProcessed,
			'executed_on' => date('Y-m-d H:i:s')
		));
	}

	/**
	 * Update service/domain next_due_date after payment
	 * This should be called when invoice is marked as paid
	 *
	 * @param int $invoiceId Invoice ID
	 * @return bool Success status
	 */
	function updateNextDueDateAfterPayment($invoiceId)
	{
		// Get invoice items
		$this->db->select('*');
		$this->db->from('invoice_items');
		$this->db->where('invoice_id', $invoiceId);
		$items = $this->db->get()->result_array();

		foreach ($items as $item) {
			if (empty($item['ref_id']) || empty($item['billing_period_end'])) {
				continue;
			}

			if ($item['item_type'] == 1) {
				// Domain
				$this->db->where('id', $item['ref_id']);
				$this->db->update('order_domains', array(
					'next_due_date' => $item['billing_period_end'],
					'exp_date' => $item['billing_period_end'],
					'updated_on' => date('Y-m-d H:i:s')
				));
			} elseif ($item['item_type'] == 2) {
				// Service
				$this->db->where('id', $item['ref_id']);
				$this->db->update('order_services', array(
					'next_due_date' => $item['billing_period_end'],
					'exp_date' => $item['billing_period_end'],
					'updated_on' => date('Y-m-d H:i:s')
				));
			}
		}

		return true;
	}
}
?>
