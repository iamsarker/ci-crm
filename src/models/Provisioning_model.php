<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Provisioning Model
 *
 * Handles provisioning of services and domains after successful payment.
 * Supports:
 * - Domain registration (new domains)
 * - Domain transfer (with EPP code)
 * - Domain renewal
 * - Hosting account creation (cPanel)
 * - Hosting account unsuspend (for renewals)
 */
class Provisioning_model extends CI_Model
{
    private $logTable = 'provisioning_logs';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('domain_helper');
        $this->load->helper('cpanel_helper');
        $this->load->helper('plesk_helper');
        $this->load->helper('directadmin_helper');
    }

    // =========================================
    // MAIN ENTRY POINT
    // =========================================

    /**
     * Provision all items in an invoice after payment
     *
     * @param int $invoiceId Invoice ID
     * @return array Results array with status for each item
     */
    function provisionInvoiceItems($invoiceId)
    {
        $results = array(
            'success' => true,
            'items_processed' => 0,
            'items_success' => 0,
            'items_failed' => 0,
            'details' => array()
        );

        // Get all invoice items
        $items = $this->getInvoiceItemsForProvisioning($invoiceId);

        if (empty($items)) {
            log_message('info', 'Provisioning: No items to provision for invoice #' . $invoiceId);
            return $results;
        }

        foreach ($items as $item) {
            $results['items_processed']++;

            if ($item['item_type'] == 1) {
                // Domain
                $result = $this->provisionDomain($item);
            } elseif ($item['item_type'] == 2) {
                // Service/Hosting
                $result = $this->provisionService($item);
            } else {
                $result = array('success' => false, 'error' => 'Unknown item type: ' . $item['item_type']);
            }

            // Log the result
            $this->logProvisioning($invoiceId, $item, $result);

            $results['details'][] = array(
                'item_id' => $item['id'],
                'item_type' => $item['item_type'],
                'ref_id' => $item['ref_id'],
                'result' => $result
            );

            if ($result['success']) {
                $results['items_success']++;
            } else {
                $results['items_failed']++;
                $results['success'] = false;
            }
        }

        log_message('info', 'Provisioning completed for invoice #' . $invoiceId .
            ' - Total: ' . $results['items_processed'] .
            ', Success: ' . $results['items_success'] .
            ', Failed: ' . $results['items_failed']);

        return $results;
    }

    /**
     * Get invoice items that need provisioning
     *
     * @param int $invoiceId Invoice ID
     * @return array List of items
     */
    function getInvoiceItemsForProvisioning($invoiceId)
    {
        $sql = "SELECT ii.*,
                       inv.company_id
                FROM invoice_items ii
                JOIN invoices inv ON ii.invoice_id = inv.id
                WHERE ii.invoice_id = ?
                AND ii.ref_id IS NOT NULL
                AND ii.ref_id > 0";

        return $this->db->query($sql, array(intval($invoiceId)))->result_array();
    }

    // =========================================
    // DOMAIN PROVISIONING
    // =========================================

    /**
     * Provision a domain (register, transfer, or renew)
     *
     * @param array $item Invoice item data
     * @return array Result with 'success', 'action', 'error'
     */
    function provisionDomain($item)
    {
        // Get domain order details
        $domain = $this->db->where('id', $item['ref_id'])->get('order_domains')->row_array();

        if (empty($domain)) {
            return array('success' => false, 'action' => 'none', 'error' => 'Domain order not found: ' . $item['ref_id']);
        }

        // Get registrar config (falls back to default if not set)
        $registrar = $this->getRegistrarConfig($domain['dom_register_id']);
        if (empty($registrar)) {
            return array('success' => false, 'action' => 'none', 'error' => 'Registrar not configured');
        }

        // Update order_domains with registrar ID if it was missing (used default)
        if (empty($domain['dom_register_id']) && !empty($registrar['id'])) {
            $this->db->where('id', $domain['id']);
            $this->db->update('order_domains', array('dom_register_id' => $registrar['id']));
            $domain['dom_register_id'] = $registrar['id'];
        }

        // Determine if this is a renewal invoice.
        // A renewal invoice is any invoice paid AFTER an earlier paid invoice for the same
        // order_domains.id. This is the only unambiguous signal: the initial-order invoice
        // has no prior paid invoice for the same ref_id; renewal invoices do.
        // Relying on domain_order_id/status alone is unsafe — they can be missing (imports,
        // inconsistent data) or stale (status flipped to Expired/Grace before payment).
        $isRenewal = $this->isRenewalInvoiceItem($item, 1);

        // Renewals call the registrar renew API which requires domain_order_id.
        // If it's missing (e.g. imported domain, or lost), try to recover it.
        // DNS-only (3) and already-registered imports (4) don't use the registrar, so skip.
        if ($isRenewal
            && empty($domain['domain_order_id'])
            && !in_array((int)$domain['order_type'], array(3, 4), true)
        ) {
            $fetchedOrderId = registrar_get_domain_orderid($registrar, $domain['domain']);
            if (!empty($fetchedOrderId)) {
                $domain['domain_order_id'] = $fetchedOrderId;
                $this->db->where('id', $domain['id'])->update('order_domains', array('domain_order_id' => $fetchedOrderId));
                log_message('info', 'Fetched missing domain_order_id for ' . $domain['domain'] . ': ' . $fetchedOrderId);
            }
        }

        // Get company/customer info
        $company = $this->db->where('id', $domain['company_id'])->get('companies')->row_array();
        if (empty($company)) {
            return array('success' => false, 'action' => 'none', 'error' => 'Company not found');
        }

        // Determine action based on renewal status and order_type
        if ($isRenewal) {
            // DNS-only and imported-without-registrar renewals just extend local dates
            if ((int)$domain['order_type'] === 3
                || ((int)$domain['order_type'] === 4 && empty($domain['domain_order_id']))
            ) {
                return $this->activateDomainOnly($domain);
            }
            return $this->renewDomain($domain, $registrar, $company, $item);
        } else {
            // Initial order - dispatch by order_type
            switch ((int)$domain['order_type']) {
                case 1: // Registration
                    return $this->registerDomain($domain, $registrar, $company);
                case 2: // Transfer
                    return $this->transferDomain($domain, $registrar, $company);
                case 3: // DNS only - no API call needed
                    return $this->activateDomainOnly($domain);
                case 4: // Already registered (import) - no API call needed
                    return $this->activateDomainOnly($domain);
                default:
                    return array('success' => false, 'action' => 'none', 'error' => 'Unknown order type: ' . $domain['order_type']);
            }
        }
    }

    /**
     * Determine whether an invoice item represents a renewal (not the initial order).
     * True if any earlier PAID invoice already references the same order_domains/order_services row.
     *
     * @param array $item     Invoice item row (must contain invoice_id and ref_id)
     * @param int   $itemType 1 for domain, 2 for service
     * @return bool
     */
    private function isRenewalInvoiceItem($item, $itemType)
    {
        if (empty($item['ref_id']) || empty($item['invoice_id'])) {
            return false;
        }

        $earlier = $this->db
            ->select('ii.id')
            ->from('invoice_items ii')
            ->join('invoices inv', 'ii.invoice_id = inv.id')
            ->where('ii.ref_id', intval($item['ref_id']))
            ->where('ii.item_type', intval($itemType))
            ->where('ii.invoice_id <', intval($item['invoice_id']))
            ->where('inv.pay_status', 'PAID')
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($earlier);
    }

    /**
     * Validate domain format has a valid TLD
     *
     * @param string $domainName Full domain name (e.g., "example.com")
     * @return array Result with 'valid', 'domain_name', 'tld', 'error'
     */
    private function validateDomainFormat($domainName)
    {
        if (empty($domainName)) {
            return array('valid' => false, 'error' => 'Domain name is empty');
        }

        // Check if domain contains at least one dot
        if (strpos($domainName, '.') === false) {
            return array('valid' => false, 'error' => 'Domain must include TLD (e.g., .com, .net). Got: ' . $domainName);
        }

        // Parse domain
        $parts = explode('.', $domainName, 2);
        $name = $parts[0];
        $tld = isset($parts[1]) ? $parts[1] : '';

        if (empty($tld)) {
            return array('valid' => false, 'error' => 'TLD is empty after parsing domain: ' . $domainName);
        }

        // Basic TLD validation - should be alphabetic and reasonable length
        // Common TLDs: com, net, org, io, co, co.uk, etc.
        $tldParts = explode('.', $tld);
        foreach ($tldParts as $part) {
            if (!preg_match('/^[a-zA-Z]{2,}$/', $part)) {
                return array(
                    'valid' => false,
                    'error' => 'Invalid TLD format: ' . $tld . '. TLD parts must be 2+ alphabetic characters. Domain: ' . $domainName
                );
            }
        }

        return array(
            'valid' => true,
            'domain_name' => $name,
            'tld' => $tld,
            'error' => null
        );
    }

    /**
     * Register a new domain
     */
    private function registerDomain($domain, $registrar, $company)
    {
        log_message('info', 'Provisioning: Registering domain ' . $domain['domain']);

        // Validate domain format before attempting registration
        $validation = $this->validateDomainFormat($domain['domain']);
        if (!$validation['valid']) {
            log_message('error', 'Domain validation failed: ' . $validation['error']);
            return array('success' => false, 'action' => 'register', 'error' => $validation['error']);
        }

        // Get country info (ISO code and dial code) from countries table
        $countryName = $company['country'] ?? '';
        $countryData = $this->getCountryByName($countryName);
        $countryCode = $countryData['country_code'] ?? 'US';
        $dialCode = $countryData['dial_code'] ?? '1';

        // Clean phone number - remove all non-digit characters
        $rawPhone = $company['phone'] ?? $company['mobile'] ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

        // If phone starts with dial code, remove it
        $dialCodeClean = preg_replace('/[^0-9]/', '', $dialCode);
        if (!empty($dialCodeClean) && strpos($cleanPhone, $dialCodeClean) === 0) {
            $cleanPhone = substr($cleanPhone, strlen($dialCodeClean));
        }

        // Ensure phone has at least some digits
        if (empty($cleanPhone)) {
            $cleanPhone = '0000000000';
        }

        // Prepare customer info
        $customerInfo = array(
            'email' => $company['email'],
            'name' => trim(($company['first_name'] ?? '') . ' ' . ($company['last_name'] ?? '')),
            'company' => $company['name'] ?? '',
            'address1' => $company['address'] ?? '',
            'city' => $company['city'] ?? '',
            'state' => $company['state'] ?? '',
            'country' => $countryCode,
            'zipcode' => $company['zip_code'] ?? '00000',
            'phone_cc' => $dialCodeClean ?: '1',
            'phone' => $cleanPhone
        );

        // Check if company already has a registrar customer ID
        $customerId = !empty($company['registrar_customer_id']) ? $company['registrar_customer_id'] : null;

        if (empty($customerId)) {
            // Get or create customer at registrar
            $customerResult = registrar_get_or_create_customer($registrar, $customerInfo);
            if (!$customerResult['success']) {
                return array('success' => false, 'action' => 'register', 'error' => 'Failed to create customer: ' . $customerResult['error']);
            }

            $customerId = $customerResult['customer_id'];

            // Save customer ID to companies table for future use
            $this->db->where('id', $company['id'])->update('companies', array(
                'registrar_customer_id' => $customerId
            ));
            log_message('info', 'Saved registrar customer ID ' . $customerId . ' for company #' . $company['id']);
        } else {
            log_message('info', 'Using existing registrar customer ID ' . $customerId . ' for company #' . $company['id']);
        }

        // Create contact
        $contactResult = registrar_create_contact($registrar, $customerId, $customerInfo);
        if (!$contactResult['success']) {
            return array('success' => false, 'action' => 'register', 'error' => 'Failed to create contact: ' . $contactResult['error']);
        }

        $contactId = $contactResult['contact_id'];

        // Prepare contact IDs for registration
        $contact = array(
            'customer_id' => $customerId,
            'reg_contact_id' => $contactId,
            'admin_contact_id' => $contactId,
            'tech_contact_id' => $contactId,
            'billing_contact_id' => $contactId,
            'contact_info' => $customerInfo  // Include contact info for Namecheap
        );

        // Prepare nameservers - use domain's NS if set, otherwise use registrar defaults
        $nameservers = array();
        if (!empty($domain['ns1'])) {
            $nameservers[] = $domain['ns1'];
        } elseif (!empty($registrar['def_ns1'])) {
            $nameservers[] = $registrar['def_ns1'];
        }
        if (!empty($domain['ns2'])) {
            $nameservers[] = $domain['ns2'];
        } elseif (!empty($registrar['def_ns2'])) {
            $nameservers[] = $registrar['def_ns2'];
        }
        // Add ns3 and ns4 if available
        if (!empty($domain['ns3'])) {
            $nameservers[] = $domain['ns3'];
        } elseif (!empty($registrar['def_ns3'])) {
            $nameservers[] = $registrar['def_ns3'];
        }
        if (!empty($domain['ns4'])) {
            $nameservers[] = $domain['ns4'];
        } elseif (!empty($registrar['def_ns4'])) {
            $nameservers[] = $registrar['def_ns4'];
        }

        // Register domain
        $result = registrar_register_domain($registrar, $domain['domain'], $domain['reg_period'], $contact, $nameservers);

        if ($result['success']) {
            // Update order_domains with registrar order ID and activate
            $updateData = array(
                'domain_cust_id' => $customerId,
                'domain_order_id' => $result['order_id'],
                'is_synced' => 1,
                'last_sync_dt' => date('Y-m-d H:i:s'),
                'status' => 1 // Active
            );
            $this->db->where('id', $domain['id'])->update('order_domains', $updateData);

            // Update parent order status if needed
            $this->updateOrderStatus($domain['order_id']);

            log_message('info', 'Domain registered successfully: ' . $domain['domain'] . ', Order ID: ' . $result['order_id']);
            return array('success' => true, 'action' => 'register', 'order_id' => $result['order_id'], 'error' => null);
        } else {
            log_message('error', 'Domain registration failed: ' . $domain['domain'] . ' - ' . $result['error']);
            return array('success' => false, 'action' => 'register', 'error' => $result['error']);
        }
    }

    /**
     * Transfer a domain
     */
    private function transferDomain($domain, $registrar, $company)
    {
        log_message('info', 'Provisioning: Transferring domain ' . $domain['domain']);

        // Validate domain format before attempting transfer
        $validation = $this->validateDomainFormat($domain['domain']);
        if (!$validation['valid']) {
            log_message('error', 'Domain validation failed: ' . $validation['error']);
            return array('success' => false, 'action' => 'transfer', 'error' => $validation['error']);
        }

        if (empty($domain['epp_code'])) {
            return array('success' => false, 'action' => 'transfer', 'error' => 'EPP code is required for transfer');
        }

        // Get country info (ISO code and dial code) from countries table
        $countryName = $company['country'] ?? '';
        $countryData = $this->getCountryByName($countryName);
        $countryCode = $countryData['country_code'] ?? 'US';
        $dialCode = $countryData['dial_code'] ?? '1';

        // Clean phone number - remove all non-digit characters
        $rawPhone = $company['phone'] ?? $company['mobile'] ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

        // If phone starts with dial code, remove it
        $dialCodeClean = preg_replace('/[^0-9]/', '', $dialCode);
        if (!empty($dialCodeClean) && strpos($cleanPhone, $dialCodeClean) === 0) {
            $cleanPhone = substr($cleanPhone, strlen($dialCodeClean));
        }

        // Ensure phone has at least some digits
        if (empty($cleanPhone)) {
            $cleanPhone = '0000000000';
        }

        // Prepare customer info
        $customerInfo = array(
            'email' => $company['email'],
            'name' => trim(($company['first_name'] ?? '') . ' ' . ($company['last_name'] ?? '')),
            'company' => $company['name'] ?? '',
            'address1' => $company['address'] ?? '',
            'city' => $company['city'] ?? '',
            'state' => $company['state'] ?? '',
            'country' => $countryCode,
            'zipcode' => $company['zip_code'] ?? '00000',
            'phone_cc' => $dialCodeClean ?: '1',
            'phone' => $cleanPhone
        );

        // Get or create customer
        $customerResult = registrar_get_or_create_customer($registrar, $customerInfo);
        if (!$customerResult['success']) {
            return array('success' => false, 'action' => 'transfer', 'error' => 'Failed to create customer: ' . $customerResult['error']);
        }

        $customerId = $customerResult['customer_id'];

        // Create contact
        $contactResult = registrar_create_contact($registrar, $customerId, $customerInfo);
        if (!$contactResult['success']) {
            return array('success' => false, 'action' => 'transfer', 'error' => 'Failed to create contact: ' . $contactResult['error']);
        }

        $contactId = $contactResult['contact_id'];

        $contact = array(
            'customer_id' => $customerId,
            'reg_contact_id' => $contactId,
            'admin_contact_id' => $contactId,
            'tech_contact_id' => $contactId,
            'billing_contact_id' => $contactId,
            'contact_info' => $customerInfo  // Include contact info for Namecheap
        );

        // Transfer domain
        $result = registrar_transfer_domain($registrar, $domain['domain'], $domain['epp_code'], $contact);

        if ($result['success']) {
            // Update order_domains
            $updateData = array(
                'domain_cust_id' => $customerId,
                'domain_order_id' => $result['order_id'],
                'is_synced' => 1,
                'last_sync_dt' => date('Y-m-d H:i:s'),
                'status' => 1 // Active (transfer initiated)
            );
            $this->db->where('id', $domain['id'])->update('order_domains', $updateData);

            $this->updateOrderStatus($domain['order_id']);

            log_message('info', 'Domain transfer initiated: ' . $domain['domain'] . ', Order ID: ' . $result['order_id']);
            return array('success' => true, 'action' => 'transfer', 'order_id' => $result['order_id'], 'error' => null);
        } else {
            log_message('error', 'Domain transfer failed: ' . $domain['domain'] . ' - ' . $result['error']);
            return array('success' => false, 'action' => 'transfer', 'error' => $result['error']);
        }
    }

    /**
     * Renew a domain
     */
    private function renewDomain($domain, $registrar, $company, $item)
    {
        log_message('info', 'Provisioning: Renewing domain ' . $domain['domain']);

        // Validate domain format
        $validation = $this->validateDomainFormat($domain['domain']);
        if (!$validation['valid']) {
            log_message('error', 'Domain validation failed: ' . $validation['error']);
            return array('success' => false, 'action' => 'renew', 'error' => $validation['error']);
        }

        if (empty($domain['domain_order_id'])) {
            return array('success' => false, 'action' => 'renew', 'error' => 'Domain order ID not found - cannot renew unregistered domain');
        }

        // Calculate renewal period from billing period
        $periodStart = strtotime($item['billing_period_start']);
        $periodEnd = strtotime($item['billing_period_end']);
        $years = max(1, round(($periodEnd - $periodStart) / (365 * 24 * 60 * 60)));

        // Current expiry date in our system
        $currentExpDate = $domain['exp_date'];

        // Use billing_period_end as the target expiry after renewal (set by cronjob invoice)
        // This is more reliable than computing from exp_date which may be stale
        $expectedNewExpDate = $item['billing_period_end'];

        // Fetch actual expiry from registrar (returns array with 'date' and 'timestamp')
        $registrarExpiryData = registrar_get_domain_expiry($registrar, $domain['domain'], $domain['domain_order_id']);

        if (!empty($registrarExpiryData)) {
            // If registrar expiry >= billing_period_end, domain was already renewed manually — skip API call
            if (strtotime($registrarExpiryData['date']) >= strtotime($expectedNewExpDate)) {
                log_message('info', 'Domain ' . $domain['domain'] . ' already renewed at registrar (expiry: ' . $registrarExpiryData['date'] . ' >= ' . $expectedNewExpDate . '). Syncing local records only.');

                $updateData = array(
                    'exp_date' => $registrarExpiryData['date'],
                    'next_renewal_date' => $registrarExpiryData['date'],
                    'is_synced' => 1,
                    'last_sync_dt' => date('Y-m-d H:i:s'),
                    'status' => 1
                );
                $this->db->where('id', $domain['id'])->update('order_domains', $updateData);

                return array(
                    'success' => true,
                    'action' => 'renew_skipped',
                    'new_expiry' => $registrarExpiryData['date'],
                    'error' => null,
                    'message' => 'Already renewed at registrar, local records synced'
                );
            }

            // Use the raw timestamp from registrar as currentExpDate
            // ResellerClub requires the exact exp-date timestamp to match their records
            $currentExpDate = $registrarExpiryData['timestamp'];
        }

        // Renew domain via registrar API
        $result = registrar_renew_domain($registrar, $domain['domain'], $years, $currentExpDate, $domain['domain_order_id']);

        if ($result['success']) {
            $newExpDate = $expectedNewExpDate;
            $updateData = array(
                'exp_date' => $newExpDate,
                'next_renewal_date' => $newExpDate,
                'is_synced' => 1,
                'last_sync_dt' => date('Y-m-d H:i:s'),
                'status' => 1 // Active
            );
            $this->db->where('id', $domain['id'])->update('order_domains', $updateData);

            log_message('info', 'Domain renewed successfully: ' . $domain['domain'] . ', New expiry: ' . $newExpDate);
            return array('success' => true, 'action' => 'renew', 'new_expiry' => $newExpDate, 'error' => null);
        } else {
            // If registrar says "already renewed", a renewal order already exists
            // (pending processing at registrar). Treat as success to prevent futile retries.
            $errorMsg = strtolower($result['error'] ?? '');
            if (strpos($errorMsg, 'already renewed') !== false) {
                log_message('info', 'Domain ' . $domain['domain'] . ' - registrar confirms renewal order already exists. Marking active.');

                $updateData = array(
                    'status' => 1,
                    'is_synced' => 0, // needs sync once registrar processes the renewal
                    'updated_on' => date('Y-m-d H:i:s')
                );
                $this->db->where('id', $domain['id'])->update('order_domains', $updateData);

                return array(
                    'success' => true,
                    'action' => 'renew_pending',
                    'error' => null,
                    'message' => 'Renewal order already exists at registrar, pending processing'
                );
            }

            log_message('error', 'Domain renewal failed: ' . $domain['domain'] . ' - ' . $result['error']);
            return array('success' => false, 'action' => 'renew', 'error' => $result['error']);
        }
    }

    /**
     * Activate domain without API call (DNS only)
     */
    private function activateDomainOnly($domain)
    {
        log_message('info', 'Provisioning: Activating DNS-only domain ' . $domain['domain']);

        $updateData = array(
            'is_synced' => 1,
            'status' => 1 // Active
        );
        $this->db->where('id', $domain['id'])->update('order_domains', $updateData);

        $this->updateOrderStatus($domain['order_id']);

        return array('success' => true, 'action' => 'dns_only', 'error' => null);
    }

    // =========================================
    // SERVICE/HOSTING PROVISIONING
    // =========================================

    /**
     * Provision a service (create or unsuspend hosting account)
     *
     * @param array $item Invoice item data
     * @return array Result
     */
    function provisionService($item)
    {
        // Get service order details
        $service = $this->db->where('id', $item['ref_id'])->get('order_services')->row_array();

        if (empty($service)) {
            return array('success' => false, 'action' => 'none', 'error' => 'Service order not found: ' . $item['ref_id']);
        }

        // A renewal invoice is any one paid AFTER an earlier paid invoice for the same
        // order_services.id. billing_period_start/end can't be used here — they're set
        // on initial recurring-service invoices too, so that check would mis-route new
        // orders into renewService and skip account creation.
        $isRenewal = $this->isRenewalInvoiceItem($item, 2);

        if ($isRenewal) {
            // Renewal - unsuspend if suspended, update dates
            return $this->renewService($service, $item);
        } else {
            // New service - create hosting account
            return $this->createHostingAccount($service);
        }
    }

    /**
     * Create a new hosting account
     */
    private function createHostingAccount($service)
    {
        log_message('info', 'Provisioning: Creating hosting account for service #' . $service['id']);

        // Check if account already exists
        if (!empty($service['cp_username']) && $service['is_synced'] == 1) {
            return array('success' => true, 'action' => 'create', 'error' => null, 'message' => 'Account already exists');
        }

        // Check if domain is set
        if (empty($service['hosting_domain'])) {
            return array('success' => false, 'action' => 'create', 'error' => 'Hosting domain not set');
        }

        // Get server info (includes module_name)
        $serverInfo = $this->getServerInfoForService($service['product_service_id']);
        if (empty($serverInfo) || empty($serverInfo['hostname'])) {
            return array('success' => false, 'action' => 'create', 'error' => 'Server not configured for this product');
        }

        $moduleName = $this->getServerModuleName($serverInfo);

        // No module — just activate without provisioning
        if (empty($moduleName) || $moduleName === 'no module') {
            $this->db->where('id', $service['id'])->update('order_services', array('status' => 1));
            $this->updateOrderStatus($service['order_id']);
            return array('success' => true, 'action' => 'activate', 'error' => null, 'message' => 'No-module service activated');
        }

        // Get package name
        $package = $this->getServerPackage($service['product_service_id']);

        // Get company info
        $company = $this->db->where('id', $service['company_id'])->get('companies')->row_array();
        if (empty($company) || empty($company['email'])) {
            return array('success' => false, 'action' => 'create', 'error' => 'Company info not found');
        }

        $customerName = trim($company['first_name'] . ' ' . $company['last_name']);

        // Dispatch based on server module
        if ($moduleName === 'cpanel') {
            $username = generate_cpanel_username($service['hosting_domain'], $serverInfo);
            $password = generate_secure_password(16, true);

            $result = whm_create_account($serverInfo, $service['hosting_domain'], $username, $password, $package, $company['email']);

            if ($result['success']) {
                $this->db->where('id', $service['id'])->update('order_services', array(
                    'cp_username' => $username, 'is_synced' => 1, 'status' => 1
                ));
                $this->updateOrderStatus($service['order_id']);
                send_cpanel_welcome_email($company['email'], $customerName, $service['hosting_domain'], $username, $password, $serverInfo['hostname']);
                log_message('info', 'cPanel account created: ' . $username . '@' . $serverInfo['hostname']);
                return array('success' => true, 'action' => 'create', 'username' => $username, 'error' => null);
            }

        } elseif ($moduleName === 'plesk') {
            $username = generate_plesk_username($service['hosting_domain']);
            $password = generate_secure_password(16, true);

            $result = plesk_create_account($serverInfo, $service['hosting_domain'], $username, $password, $package, $company['email']);

            if ($result['success']) {
                $this->db->where('id', $service['id'])->update('order_services', array(
                    'cp_username' => $username, 'is_synced' => 1, 'status' => 1
                ));
                $this->updateOrderStatus($service['order_id']);
                send_plesk_welcome_email($company['email'], $customerName, $service['hosting_domain'], $username, $password, $serverInfo['hostname']);
                log_message('info', 'Plesk account created: ' . $username . '@' . $serverInfo['hostname']);
                return array('success' => true, 'action' => 'create', 'username' => $username, 'error' => null);
            }

        } elseif ($moduleName === 'directadmin') {
            $username = generate_da_username($service['hosting_domain']);
            $password = generate_secure_password(16, true);

            $result = da_create_account($serverInfo, $service['hosting_domain'], $username, $password, $package, $company['email']);

            if ($result['success']) {
                $this->db->where('id', $service['id'])->update('order_services', array(
                    'cp_username' => $username, 'is_synced' => 1, 'status' => 1
                ));
                $this->updateOrderStatus($service['order_id']);
                send_da_welcome_email($company['email'], $customerName, $service['hosting_domain'], $username, $password, $serverInfo['hostname']);
                log_message('info', 'DirectAdmin account created: ' . $username . '@' . $serverInfo['hostname']);
                return array('success' => true, 'action' => 'create', 'username' => $username, 'error' => null);
            }

        } else {
            return array('success' => false, 'action' => 'create', 'error' => 'Unsupported server module: ' . $moduleName);
        }

        log_message('error', 'Hosting account creation failed: ' . $result['error']);
        return array('success' => false, 'action' => 'create', 'error' => $result['error']);
    }

    /**
     * Renew a service (unsuspend if needed, update dates)
     */
    private function renewService($service, $item)
    {
        log_message('info', 'Provisioning: Renewing service #' . $service['id']);

        $actions = array();

        // If service is suspended (3), unsuspend via server API
        // If expired (2), also try unsuspend as server may have suspended the account
        if ($service['status'] == 3 || $service['status'] == 2) {
            $unsuspendResult = $this->unsuspendHostingAccount($service);
            $actions[] = 'unsuspend';

            if (!$unsuspendResult['success']) {
                return array(
                    'success' => false,
                    'action' => 'renew_unsuspend',
                    'error' => 'Failed to unsuspend: ' . $unsuspendResult['error']
                );
            }
        }

        // Update service dates
        $updateData = array(
            'status' => 1, // Active
            'suspension_date' => null,
            'suspension_reason' => null
        );

        // Update next_due_date if billing period is provided
        if (!empty($item['billing_period_end'])) {
            $updateData['next_renewal_date'] = $item['billing_period_end'];
            $updateData['exp_date'] = $item['billing_period_end'];
        }

        $this->db->where('id', $service['id'])->update('order_services', $updateData);

        log_message('info', 'Service renewed: #' . $service['id'] . ', Actions: ' . implode(', ', $actions));
        return array('success' => true, 'action' => 'renew', 'actions' => $actions, 'error' => null);
    }

    /**
     * Suspend a hosting account via the appropriate module API and flip local state.
     *
     * On success: order_services.status=3 (Suspended), suspension_date=today, suspension_reason=$reason.
     * On failure: local state is NOT changed, so the next cron run will retry.
     *
     * @param array  $service order_services row
     * @param string $reason  human-readable reason recorded locally and sent to the panel
     * @return array {success: bool, action: 'suspend', module: string, error: string|null}
     */
    function suspendService($service, $reason = 'Suspended for non-payment')
    {
        log_message('info', 'Provisioning: Suspending service #' . $service['id'] . ' — ' . $reason);

        $serverInfo = $this->getServerInfoForService($service['product_service_id']);
        if (empty($serverInfo) || empty($serverInfo['hostname'])) {
            return array('success' => false, 'action' => 'suspend', 'error' => 'Server not configured');
        }

        $moduleName = $this->getServerModuleName($serverInfo);

        if ($moduleName === 'cpanel') {
            if (empty($service['cp_username'])) {
                return array('success' => false, 'action' => 'suspend', 'module' => $moduleName, 'error' => 'No cPanel username');
            }
            $result = whm_suspend_account($serverInfo, $service['cp_username'], $reason);
        } elseif ($moduleName === 'plesk') {
            if (empty($service['hosting_domain'])) {
                return array('success' => false, 'action' => 'suspend', 'module' => $moduleName, 'error' => 'No hosting domain for Plesk');
            }
            $result = plesk_suspend_account($serverInfo, $service['hosting_domain'], $reason);
        } elseif ($moduleName === 'directadmin') {
            if (empty($service['cp_username'])) {
                return array('success' => false, 'action' => 'suspend', 'module' => $moduleName, 'error' => 'No DirectAdmin username');
            }
            $result = da_suspend_account($serverInfo, $service['cp_username'], $reason);
        } else {
            // No-module services have no remote account — flip local state only
            $this->db->where('id', $service['id'])->update('order_services', array(
                'status' => 3,
                'suspension_date' => date('Y-m-d'),
                'suspension_reason' => $reason
            ));
            return array('success' => true, 'action' => 'suspend', 'module' => 'none', 'error' => null);
        }

        if (!empty($result['success'])) {
            $this->db->where('id', $service['id'])->update('order_services', array(
                'status' => 3,
                'suspension_date' => date('Y-m-d'),
                'suspension_reason' => $reason
            ));
            log_message('info', 'Service #' . $service['id'] . ' suspended via ' . $moduleName);
            return array('success' => true, 'action' => 'suspend', 'module' => $moduleName, 'error' => null);
        }

        log_message('error', 'Suspend failed for service #' . $service['id'] . ' via ' . $moduleName . ': ' . ($result['error'] ?? 'unknown'));
        return array('success' => false, 'action' => 'suspend', 'module' => $moduleName, 'error' => $result['error'] ?? 'unknown');
    }

    /**
     * Unsuspend a hosting account via the appropriate module API
     */
    private function unsuspendHostingAccount($service)
    {
        // Get server info (includes module_name)
        $serverInfo = $this->getServerInfoForService($service['product_service_id']);
        if (empty($serverInfo) || empty($serverInfo['hostname'])) {
            return array('success' => false, 'error' => 'Server not configured');
        }

        $moduleName = $this->getServerModuleName($serverInfo);

        if ($moduleName === 'cpanel') {
            if (empty($service['cp_username'])) {
                return array('success' => false, 'error' => 'No cPanel username found');
            }
            $result = whm_unsuspend_account($serverInfo, $service['cp_username']);
        } elseif ($moduleName === 'plesk') {
            if (empty($service['hosting_domain'])) {
                return array('success' => false, 'error' => 'No hosting domain found for Plesk');
            }
            $result = plesk_unsuspend_account($serverInfo, $service['hosting_domain']);
        } elseif ($moduleName === 'directadmin') {
            if (empty($service['cp_username'])) {
                return array('success' => false, 'error' => 'No DirectAdmin username found');
            }
            $result = da_unsuspend_account($serverInfo, $service['cp_username']);
        } else {
            // No module — just return success
            return array('success' => true, 'error' => null);
        }

        if ($result['success']) {
            log_message('info', 'Account unsuspended via ' . $moduleName . ': ' . ($service['cp_username'] ?? $service['hosting_domain']));
            return array('success' => true, 'error' => null);
        } else {
            log_message('error', 'Unsuspend failed via ' . $moduleName . ': ' . $result['error']);
            return array('success' => false, 'error' => $result['error']);
        }
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Get registrar configuration
     * Falls back to default registrar if ID is empty or not found
     */
    private function getRegistrarConfig($registrarId)
    {
        $result = array();

        // Try to get by specific ID first
        if (!empty($registrarId) && is_numeric($registrarId) && $registrarId > 0) {
            $sql = "SELECT * FROM dom_registers WHERE id = ? AND status = 1";
            $result = $this->db->query($sql, array(intval($registrarId)))->row_array();
        }

        // Fall back to default registrar if not found
        if (empty($result)) {
            $sql = "SELECT * FROM dom_registers WHERE status = 1 AND is_selected = 1 LIMIT 1";
            $result = $this->db->query($sql)->row_array();
        }

        return $result;
    }

    /**
     * Get server info for a product service (includes module name for dispatching)
     */
    private function getServerInfoForService($productServiceId)
    {
        $sql = "SELECT s.*, psm.module_name
                FROM product_services ps
                JOIN servers s ON ps.server_id = s.id
                LEFT JOIN server_modules psm ON s.product_service_module_id = psm.id
                WHERE ps.id = ? AND s.status = 1";
        return $this->db->query($sql, array(intval($productServiceId)))->row_array();
    }

    /**
     * Get the server module name (lowercase): 'cpanel', 'plesk', or empty
     */
    private function getServerModuleName($serverInfo)
    {
        return strtolower(trim($serverInfo['module_name'] ?? ''));
    }

    /**
     * Get server package/plan name for a product
     */
    private function getServerPackage($productServiceId)
    {
        $sql = "SELECT cp_package FROM product_services WHERE id = ?";
        $result = $this->db->query($sql, array(intval($productServiceId)))->row();
        return $result ? $result->cp_package : 'default';
    }

    /**
     * Update parent order status if all items are provisioned
     */
    private function updateOrderStatus($orderId)
    {
        // Check if all services and domains for this order are active
        $pendingServices = $this->db->where('order_id', $orderId)
            ->where('status', 0)
            ->count_all_results('order_services');

        $pendingDomains = $this->db->where('order_id', $orderId)
            ->where('status', 0)
            ->count_all_results('order_domains');

        if ($pendingServices == 0 && $pendingDomains == 0) {
            // All items provisioned, update order to Active
            $this->db->where('id', $orderId)->update('orders', array(
                'status' => 1,  // 1 = Active
                'updated_on' => date('Y-m-d H:i:s')
            ));
            log_message('info', 'Order #' . $orderId . ' status updated to ACTIVE');
        }
    }

    /**
     * Log provisioning activity
     */
    private function logProvisioning($invoiceId, $item, $result)
    {
        // Check if table exists (may not if migration hasn't run)
        if (!$this->db->table_exists($this->logTable)) {
            return;
        }

        $logData = array(
            'invoice_id' => $invoiceId,
            'invoice_item_id' => $item['id'],
            'item_type' => $item['item_type'],
            'ref_id' => $item['ref_id'],
            'action' => $result['action'] ?? 'unknown',
            'success' => $result['success'] ? 1 : 0,
            'error_message' => $result['error'] ?? null,
            'response_data' => json_encode($result),
            'inserted_on' => date('Y-m-d H:i:s')
        );

        $this->db->insert($this->logTable, $logData);
    }

    /**
     * Get provisioning logs for an invoice
     */
    function getProvisioningLogs($invoiceId)
    {
        if (!$this->db->table_exists($this->logTable)) {
            return array();
        }

        return $this->db->where('invoice_id', $invoiceId)
            ->order_by('inserted_on', 'DESC')
            ->get($this->logTable)
            ->result_array();
    }

    /**
     * Retry failed provisioning for an invoice
     */
    function retryProvisioning($invoiceId)
    {
        return $this->provisionInvoiceItems($invoiceId);
    }

    /**
     * Get country data by country name
     *
     * @param string $countryName Country name (e.g., "Bangladesh")
     * @return array Country data with country_code and dial_code
     */
    private function getCountryByName($countryName)
    {
        if (empty($countryName)) {
            return array('country_code' => 'US', 'dial_code' => '1');
        }

        $result = $this->db->select('country_code, dial_code')
            ->where('country_name', $countryName)
            ->or_where('country_code', strtoupper($countryName))
            ->get('countries')
            ->row_array();

        if (!empty($result)) {
            return $result;
        }

        // Try partial match
        $result = $this->db->select('country_code, dial_code')
            ->like('country_name', $countryName, 'both')
            ->get('countries')
            ->row_array();

        return !empty($result) ? $result : array('country_code' => 'US', 'dial_code' => '1');
    }
}
