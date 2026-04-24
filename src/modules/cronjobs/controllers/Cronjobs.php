<?php
/**
 * Cronjobs Controller
 *
 * Handles automated tasks like renewal invoice generation, dunning, etc.
 * Following WHMCS best practices for billing automation.
 *
 * SECURITY: Protected by secret key and IP restriction
 *
 * Setup cron job (run daily):
 * 0 6 * * * curl -s "http://yoursite.com/cronjobs/run?key=YOUR_CRON_SECRET_KEY" > /dev/null 2>&1
 * or
 * 0 6 * * * php /path/to/index.php cronjobs run > /dev/null 2>&1
 */
class Cronjobs extends WHMAZ_Controller
{
	// Days before expiry to generate renewal invoice
	const RENEWAL_DAYS_BEFORE = 15;

	// Allowed IPs (localhost, server IPs) - add your server IP here
	private $allowedIps = array(
		'127.0.0.1',
		'::1',
		'localhost'
	);

	function __construct()
	{
		parent::__construct();
		$this->load->model('Cronjob_model');
		$this->load->model('Common_model');
		$this->load->model('Order_model');
	}

	/**
	 * Validate cronjob access - checks secret key and IP
	 * @return bool True if authorized
	 */
	private function validateCronAccess()
	{
		// Always allow CLI requests (php index.php cronjobs run)
		if ($this->input->is_cli_request()) {
			return true;
		}

		// Get cron secret key from sys_cnf table
		$cronSecretKey = $this->Cronjob_model->getSysConfig('cron_secret_key', '');

		// If no secret key is configured, deny all HTTP access
		if (empty($cronSecretKey)) {
			log_message('error', 'Cronjob access denied: No cron_secret_key configured in sys_cnf table');
			return false;
		}

		// Validate secret key from URL parameter
		$providedKey = $this->input->get('key');
		if (empty($providedKey) || $providedKey !== $cronSecretKey) {
			log_message('error', 'Cronjob access denied: Invalid or missing secret key from IP ' . $this->input->ip_address());
			return false;
		}

		// Optional: IP whitelist check (uncomment to enable)
		// $clientIp = $this->input->ip_address();
		// if (!in_array($clientIp, $this->allowedIps)) {
		//     log_message('error', 'Cronjob access denied: IP not whitelisted - ' . $clientIp);
		//     return false;
		// }

		return true;
	}

	/**
	 * Deny access with proper response
	 */
	private function denyAccess()
	{
		http_response_code(403);
		header('Content-Type: application/json');
		echo json_encode(array(
			'error' => 'Access denied',
			'message' => 'Invalid or missing authentication'
		));
		exit;
	}

	/**
	 * Main entry point - runs all cronjobs
	 * URL: /cronjobs/run?key=YOUR_SECRET_KEY
	 */
	function run()
	{
		// Security check
		if (!$this->validateCronAccess()) {
			$this->denyAccess();
		}

		// Set unlimited execution time for cronjobs
		set_time_limit(0);

		$output = array();
		$output['start_time'] = date('Y-m-d H:i:s');

		// 1. Generate Renewal Invoices
		$renewalResult = $this->generateRenewalInvoices();
		$output['renewal_invoices'] = $renewalResult;

		// 2. Suspend Overdue Hosting Services
		$suspensionResult = $this->suspendOverdueServices();
		$output['suspensions'] = $suspensionResult;

		// 3. Future: Process dunning rules
		// $dunningResult = $this->processDunning();
		// $output['dunning'] = $dunningResult;

		// 4. Future: Expire overdue services
		// $expiryResult = $this->expireOverdueServices();
		// $output['expiry'] = $expiryResult;

		$output['end_time'] = date('Y-m-d H:i:s');

		// Return JSON for API calls, or text for CLI
		if ($this->input->is_cli_request()) {
			echo "=== Cronjob Execution Report ===\n";
			echo "Start: {$output['start_time']}\n";
			echo "End: {$output['end_time']}\n";
			echo "\nRenewal Invoices:\n";
			echo "  Services processed: {$renewalResult['services_processed']}\n";
			echo "  Domains processed: {$renewalResult['domains_processed']}\n";
			echo "  Combined (domain+service): {$renewalResult['combined_processed']}\n";
			echo "  Invoices created: {$renewalResult['invoices_created']}\n";
			echo "  Emails sent: {$renewalResult['emails_sent']}\n";
			if (!empty($renewalResult['errors'])) {
				echo "  Errors: " . count($renewalResult['errors']) . "\n";
			}
			echo "\nSuspensions:\n";
			echo "  Services checked: {$suspensionResult['services_checked']}\n";
			echo "  Suspended: {$suspensionResult['suspended']}\n";
			echo "  Failed: {$suspensionResult['failed']}\n";
			echo "  Emails sent: {$suspensionResult['emails_sent']}\n";
			if (!empty($suspensionResult['errors'])) {
				echo "  Errors: " . count($suspensionResult['errors']) . "\n";
			}
		} else {
			header('Content-Type: application/json');
			echo json_encode($output, JSON_PRETTY_PRINT);
		}
	}

	/**
	 * Generate renewal invoices for expiring services and domains
	 * URL: /cronjobs/generateRenewalInvoices?key=YOUR_SECRET_KEY
	 */
	function generateRenewalInvoices()
	{
		// Security check
		if (!$this->validateCronAccess()) {
			$this->denyAccess();
		}

		$result = array(
			'services_processed' => 0,
			'domains_processed' => 0,
			'combined_processed' => 0,
			'invoices_created' => 0,
			'emails_sent' => 0,
			'errors' => array()
		);

		// Get app settings for email
		$appSettings = $this->Cronjob_model->getAppSettings();

		// Track service IDs that were combined with a domain invoice
		$combinedServiceIds = array();

		// ========== PROCESS EXPIRING DOMAINS (first, to detect linked services) ==========
		$expiringDomains = $this->Cronjob_model->getExpiringDomains(self::RENEWAL_DAYS_BEFORE);

		foreach ($expiringDomains as $domain) {
			$result['domains_processed']++;

			// Check if domain has a linked service with the same renewal date
			$linkedService = null;
			if (!empty($domain['linked_service_id'])) {
				$linkedService = $this->Cronjob_model->getLinkedExpiringService(
					$domain['linked_service_id'],
					$domain['next_renewal_date']
				);
			}

			if ($linkedService) {
				// Create combined invoice (domain + service)
				$invoiceResult = $this->Cronjob_model->createCombinedRenewalInvoice($domain, $linkedService);

				if ($invoiceResult['success']) {
					$result['combined_processed']++;
					$result['invoices_created']++;
					$combinedServiceIds[] = $linkedService['id'];

					// Send email notification (combined)
					$emailSent = $this->sendRenewalInvoiceEmail(
						$domain,
						$invoiceResult['invoice'],
						'combined',
						$appSettings,
						$linkedService
					);

					if ($emailSent) {
						$result['emails_sent']++;
					}

					log_message('info', "Combined renewal invoice #{$invoiceResult['invoice']['invoice_no']} created for domain #{$domain['id']} ({$domain['domain']}) + service #{$linkedService['id']}");
				} else {
					$result['errors'][] = "Combined domain #{$domain['id']} + service #{$linkedService['id']}: {$invoiceResult['error']}";
					log_message('error', "Failed to create combined renewal invoice for domain #{$domain['id']}: {$invoiceResult['error']}");
				}
			} else {
				// Create domain-only invoice
				$invoiceResult = $this->Cronjob_model->createDomainRenewalInvoice($domain);

				if ($invoiceResult['success']) {
					$result['invoices_created']++;

					$emailSent = $this->sendRenewalInvoiceEmail(
						$domain,
						$invoiceResult['invoice'],
						'domain',
						$appSettings
					);

					if ($emailSent) {
						$result['emails_sent']++;
					}

					log_message('info', "Renewal invoice #{$invoiceResult['invoice']['invoice_no']} created for domain #{$domain['id']} ({$domain['domain']})");
				} else {
					$result['errors'][] = "Domain #{$domain['id']}: {$invoiceResult['error']}";
					log_message('error', "Failed to create renewal invoice for domain #{$domain['id']}: {$invoiceResult['error']}");
				}
			}
		}

		// ========== PROCESS EXPIRING SERVICES (skip those already combined) ==========
		$expiringServices = $this->Cronjob_model->getExpiringServices(self::RENEWAL_DAYS_BEFORE);

		foreach ($expiringServices as $service) {
			// Skip if this service was already included in a combined invoice
			if (in_array($service['id'], $combinedServiceIds)) {
				continue;
			}

			$result['services_processed']++;

			// Create renewal invoice
			$invoiceResult = $this->Cronjob_model->createServiceRenewalInvoice($service);

			if ($invoiceResult['success']) {
				$result['invoices_created']++;

				// Send email notification
				$emailSent = $this->sendRenewalInvoiceEmail(
					$service,
					$invoiceResult['invoice'],
					'service',
					$appSettings
				);

				if ($emailSent) {
					$result['emails_sent']++;
				}

				log_message('info', "Renewal invoice #{$invoiceResult['invoice']['invoice_no']} created for service #{$service['id']} ({$service['hosting_domain']})");
			} else {
				$result['errors'][] = "Service #{$service['id']}: {$invoiceResult['error']}";
				log_message('error', "Failed to create renewal invoice for service #{$service['id']}: {$invoiceResult['error']}");
			}
		}

		// Log cronjob execution (optional - if table exists)
		try {
			$this->Cronjob_model->logCronjobExecution(
				'renewal_invoices',
				empty($result['errors']) ? 'success' : 'partial',
				json_encode($result),
				$result['invoices_created']
			);
		} catch (Exception $e) {
			// Table might not exist, ignore
		}

		return $result;
	}

	/**
	 * Send renewal invoice email to customer
	 *
	 * @param array $item Service or Domain data
	 * @param array $invoice Invoice data
	 * @param string $type 'service' or 'domain'
	 * @param array $appSettings App settings
	 * @return bool Success status
	 */
	private function sendRenewalInvoiceEmail($item, $invoice, $type, $appSettings, $linkedService = null)
	{
		try {
			// Get email template
			$template = $this->Cronjob_model->getEmailTemplate('invoice_created');

			if (empty($template)) {
				log_message('error', 'Email template "invoice_created" not found');
				return false;
			}

			// Build customer name
			$customerName = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));
			if (empty($customerName)) {
				$customerName = $item['company_name'] ?? 'Customer';
			}

			// Build item description
			if ($type === 'combined' && $linkedService) {
				$itemDescription = "Domain: " . ($item['domain'] ?? '');
				$itemDescription .= " + " . ($linkedService['product_name'] ?? 'Hosting');
				if (!empty($linkedService['hosting_domain'])) {
					$itemDescription .= " ({$linkedService['hosting_domain']})";
				}
			} elseif ($type === 'service') {
				$itemDescription = ($item['product_name'] ?? 'Service');
				if (!empty($item['hosting_domain'])) {
					$itemDescription .= " ({$item['hosting_domain']})";
				}
			} else {
				$itemDescription = "Domain: " . ($item['domain'] ?? '');
			}

			// Build invoice URL
			$invoiceUrl = base_url() . "billing/view_invoice/{$invoice['invoice_uuid']}";

			// Replace placeholders in template
			$subject = $template['subject'];
			$body = $template['body'];

			$placeholders = array(
				'{client_name}' => $customerName,
				'{invoice_no}' => $invoice['invoice_no'],
				'{amount_due}' => number_format($invoice['total'], 2),
				'{due_date}' => date('F j, Y', strtotime($invoice['due_date'])),
				'{currency}' => $invoice['currency_code'] ?? 'USD',
				'{invoice_url}' => $invoiceUrl,
				'{item_description}' => $itemDescription,
				'{site_name}' => $appSettings['company_name'] ?? 'Our Company',
				'{site_url}' => base_url()
			);

			foreach ($placeholders as $key => $value) {
				$subject = str_replace($key, $value, $subject);
				$body = str_replace($key, $value, $body);
			}

			// Send email using the helper function
			$fromEmail = $appSettings['smtp_user'] ?? $appSettings['site_email'] ?? 'noreply@example.com';
			$fromName = $appSettings['company_name'] ?? 'Billing System';

			$sent = sendHtmlEmail(
				$item['company_email'],
				$subject,
				$body,
				$fromEmail,
				$fromName
			);

			if ($sent) {
				log_message('info', "Renewal invoice email sent to {$item['company_email']} for invoice #{$invoice['invoice_no']}");
			} else {
				log_message('error', "Failed to send renewal invoice email to {$item['company_email']}");
			}

			return $sent;

		} catch (Exception $e) {
			log_message('error', 'sendRenewalInvoiceEmail error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Suspend hosting services whose DUE invoice is overdue by at least
	 * sys_cnf.suspension_days_after_due days. Sends a notification email
	 * on successful suspension using the dunning_suspended template.
	 *
	 * URL: /cronjobs/suspendOverdueServices?key=YOUR_SECRET_KEY
	 *
	 * @return array result counters
	 */
	function suspendOverdueServices()
	{
		// Security check
		if (!$this->validateCronAccess()) {
			$this->denyAccess();
		}

		$result = array(
			'services_checked' => 0,
			'suspended' => 0,
			'failed' => 0,
			'emails_sent' => 0,
			'errors' => array()
		);

		// Honor the global cron toggle
		if ((int)$this->Cronjob_model->getSysConfig('cron_enabled', 1) !== 1) {
			log_message('info', 'suspendOverdueServices skipped: cron_enabled=0');
			return $result;
		}

		$daysAfterDue = intval($this->Cronjob_model->getSysConfig('suspension_days_after_due', 7));
		if ($daysAfterDue < 1) {
			$daysAfterDue = 7;
		}

		$this->load->model('Provisioning_model');

		$appSettings = $this->Cronjob_model->getAppSettings();
		$candidates = $this->Cronjob_model->getServicesOverdueForSuspension($daysAfterDue);

		// Dedupe by service_id — one suspension per service per run, using the oldest
		// invoice (first row per service_id, since the query sorts by due_date ASC) as
		// the triggering invoice referenced in logs and the customer email.
		$processedServiceIds = array();

		foreach ($candidates as $row) {
			$serviceId = (int)$row['service_id'];
			if (isset($processedServiceIds[$serviceId])) {
				continue;
			}
			$processedServiceIds[$serviceId] = true;
			$result['services_checked']++;

			// Load the full order_services row for the helper
			$service = $this->db->where('id', $serviceId)->get('order_services')->row_array();
			if (empty($service)) {
				$result['errors'][] = "Service #{$serviceId}: not found during suspension";
				continue;
			}

			$reason = 'Invoice #' . $row['invoice_no'] . ' overdue by ' . $row['days_overdue'] . ' days';

			$suspendResult = $this->Provisioning_model->suspendService($service, $reason);

			if (!empty($suspendResult['success'])) {
				$result['suspended']++;
				log_message('info', "Service #{$serviceId} suspended ({$suspendResult['module']}) — invoice #{$row['invoice_no']}");

				if ($this->sendServiceSuspendedEmail($row, $appSettings)) {
					$result['emails_sent']++;
				}
			} else {
				$result['failed']++;
				$result['errors'][] = "Service #{$serviceId}: " . ($suspendResult['error'] ?? 'unknown error');
			}
		}

		// Optional run log; table may not exist on older installs
		try {
			$this->Cronjob_model->logCronjobExecution(
				'service_suspensions',
				empty($result['errors']) ? 'success' : 'partial',
				json_encode($result),
				$result['suspended']
			);
		} catch (Exception $e) {
			// ignore
		}

		return $result;
	}

	/**
	 * Send the dunning_suspended email to a customer whose service was just suspended.
	 *
	 * @param array $row         row from getServicesOverdueForSuspension (customer + invoice + service)
	 * @param array $appSettings app_settings row
	 * @return bool
	 */
	private function sendServiceSuspendedEmail($row, $appSettings)
	{
		try {
			$template = $this->Cronjob_model->getEmailTemplate('dunning_suspended');
			if (empty($template)) {
				log_message('error', 'Email template "dunning_suspended" not found');
				return false;
			}

			if (empty($row['customer_email'])) {
				return false;
			}

			$customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
			if (empty($customerName)) {
				$customerName = $row['company_name_customer'] ?? 'Customer';
			}

			$invoiceUrl = base_url() . "billing/view_invoice/{$row['invoice_uuid']}";
			$currencySymbol = !empty($row['currency_symbol']) ? $row['currency_symbol'] : ($row['currency_code'] ?? '');

			$placeholders = array(
				'{client_name}'   => $customerName,
				'{invoice_no}'    => $row['invoice_no'],
				'{amount_due}'    => number_format((float)$row['total'], 2),
				'{currency}'      => $row['currency_code'] ?? '',
				'{currency_symbol}' => $currencySymbol,
				'{due_date}'      => date('F j, Y', strtotime($row['due_date'])),
				'{days_overdue}'  => (int)$row['days_overdue'],
				'{invoice_url}'   => $invoiceUrl,
				'{service_name}'  => $row['product_name'] ?? 'Hosting Service',
				'{hosting_domain}' => $row['hosting_domain'] ?? '',
				'{site_name}'     => $appSettings['company_name'] ?? 'Our Company',
				'{site_url}'      => base_url(),
				'{company_name}'  => $appSettings['company_name'] ?? 'Our Company'
			);

			$subject = strtr($template['subject'], $placeholders);
			$body = strtr($template['body'], $placeholders);

			$fromEmail = $appSettings['smtp_user'] ?? $appSettings['site_email'] ?? 'noreply@example.com';
			$fromName = $appSettings['company_name'] ?? 'Billing System';

			$sent = sendHtmlEmail($row['customer_email'], $subject, $body, $fromEmail, $fromName);

			if ($sent) {
				log_message('info', "Suspension email sent to {$row['customer_email']} for invoice #{$row['invoice_no']}");
			} else {
				log_message('error', "Failed to send suspension email to {$row['customer_email']}");
			}

			return (bool)$sent;
		} catch (Exception $e) {
			log_message('error', 'sendServiceSuspendedEmail error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Manual test endpoint - generate invoices for items expiring in X days
	 * URL: /cronjobs/testRenewal/30?key=YOUR_SECRET_KEY
	 *
	 * @param int $days Days to look ahead (default: 15)
	 */
	function testRenewal($days = 15)
	{
		// Security check
		if (!$this->validateCronAccess()) {
			$this->denyAccess();
		}

		$days = intval($days);
		if ($days < 1) $days = 15;
		if ($days > 90) $days = 90;

		$expiringServices = $this->Cronjob_model->getExpiringServices($days);
		$expiringDomains = $this->Cronjob_model->getExpiringDomains($days);

		$output = array(
			'days_ahead' => $days,
			'target_date' => date('Y-m-d', strtotime("+{$days} days")),
			'services' => array(
				'count' => count($expiringServices),
				'items' => array()
			),
			'domains' => array(
				'count' => count($expiringDomains),
				'items' => array()
			)
		);

		foreach ($expiringServices as $s) {
			$output['services']['items'][] = array(
				'id' => $s['id'],
				'product' => $s['product_name'],
				'domain' => $s['hosting_domain'],
				'company' => $s['company_name'],
				'next_renewal_date' => $s['next_renewal_date'],
				'recurring_amount' => $s['recurring_amount'],
				'cycle' => $s['cycle_name']
			);
		}

		foreach ($expiringDomains as $d) {
			$output['domains']['items'][] = array(
				'id' => $d['id'],
				'domain' => $d['domain'],
				'company' => $d['company_name'],
				'next_renewal_date' => $d['next_renewal_date'],
				'renewal_price' => $d['renewal_price']
			);
		}

		header('Content-Type: application/json');
		echo json_encode($output, JSON_PRETTY_PRINT);
	}

	/**
	 * Index - show cronjob status/info (public info only)
	 * URL: /cronjobs
	 */
	function index()
	{
		$info = array(
			'name' => 'WHMAZ Cronjob System',
			'version' => '1.0',
			'status' => 'All endpoints require authentication',
			'security' => array(
				'method' => 'Secret key via URL parameter',
				'setup' => 'Add cron_secret_key to app_settings table'
			),
			'endpoints' => array(
				'/cronjobs/run?key=YOUR_KEY' => 'Run all cronjobs',
				'/cronjobs/generateRenewalInvoices?key=YOUR_KEY' => 'Generate renewal invoices only',
				'/cronjobs/testRenewal/{days}?key=YOUR_KEY' => 'Test: preview expiring items'
			),
			'cron_setup' => array(
				'command' => '0 6 * * * curl -s "' . base_url() . 'cronjobs/run?key=YOUR_CRON_SECRET_KEY" > /dev/null 2>&1',
				'cli_alternative' => '0 6 * * * php ' . FCPATH . 'index.php cronjobs run > /dev/null 2>&1',
				'description' => 'Run daily at 6:00 AM'
			),
			'settings' => array(
				'renewal_days_before' => self::RENEWAL_DAYS_BEFORE
			)
		);

		header('Content-Type: application/json');
		echo json_encode($info, JSON_PRETTY_PRINT);
	}
}
