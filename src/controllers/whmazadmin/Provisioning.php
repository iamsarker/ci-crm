<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Provisioning Controller
 *
 * Manages provisioning logs and retry functionality
 */
class Provisioning extends WHMAZADMIN_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Provisioning_model');
        $this->load->model('Invoice_model');

        if (!$this->isLogin()) {
            redirect('/whmazadmin/authenticate/login', 'refresh');
        }
    }

    /**
     * Provisioning logs list page
     */
    public function index()
    {
        // Get summary stats
        $data['stats'] = $this->getProvisioningStats();
        $this->load->view('whmazadmin/provisioning_logs', $data);
    }

    /**
     * Get provisioning stats for dashboard
     */
    private function getProvisioningStats()
    {
        $stats = array(
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'today' => 0
        );

        // Check if table exists
        if (!$this->db->table_exists('provisioning_logs')) {
            return $stats;
        }

        // Total logs
        $stats['total'] = $this->db->count_all_results('provisioning_logs');

        // Success count
        $this->db->where('success', 1);
        $stats['success'] = $this->db->count_all_results('provisioning_logs');

        // Failed count
        $this->db->where('success', 0);
        $stats['failed'] = $this->db->count_all_results('provisioning_logs');

        // Today's count
        $this->db->where('DATE(inserted_on)', date('Y-m-d'));
        $stats['today'] = $this->db->count_all_results('provisioning_logs');

        return $stats;
    }

    /**
     * Server-side DataTable API for provisioning logs
     */
    public function logs_list_api()
    {
        header('Content-Type: application/json');

        try {
            // Check if table exists
            if (!$this->db->table_exists('provisioning_logs')) {
                echo json_encode(array(
                    'draw' => 0,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => array(),
                    'error' => 'Table provisioning_logs does not exist. Please run the migration.'
                ));
                return;
            }

            $params = $this->input->get();

            // Build query
            $bindings = array();
            $where = "WHERE 1=1";

            // Filter by success status
            if (isset($params['success']) && $params['success'] !== '') {
                $where .= " AND pl.success = ?";
                $bindings[] = intval($params['success']);
            }

            // Filter by item type
            if (!empty($params['item_type'])) {
                $where .= " AND pl.item_type = ?";
                $bindings[] = intval($params['item_type']);
            }

            // Filter by action
            if (!empty($params['action'])) {
                $where .= " AND pl.action = ?";
                $bindings[] = $params['action'];
            }

            // Filter by invoice ID
            if (!empty($params['invoice_id'])) {
                $where .= " AND pl.invoice_id = ?";
                $bindings[] = intval($params['invoice_id']);
            }

            // Search
            if (!empty($params['search']['value'])) {
                $searchValue = '%' . $params['search']['value'] . '%';
                $where .= " AND (inv.invoice_no LIKE ? OR pl.action LIKE ? OR pl.error_message LIKE ?)";
                $bindings[] = $searchValue;
                $bindings[] = $searchValue;
                $bindings[] = $searchValue;
            }

            // Order
            $orderColumn = 'pl.id';
            $orderDir = 'DESC';
            if (!empty($params['order'][0]['column'])) {
                $columns = array('pl.id', 'inv.invoice_no', 'pl.item_type', 'pl.action', 'pl.success', 'pl.inserted_on');
                $colIndex = intval($params['order'][0]['column']);
                if (isset($columns[$colIndex])) {
                    $orderColumn = $columns[$colIndex];
                }
                $orderDir = ($params['order'][0]['dir'] === 'asc') ? 'ASC' : 'DESC';
            }

            // Pagination
            $start = isset($params['start']) ? intval($params['start']) : 0;
            $length = isset($params['length']) ? intval($params['length']) : 25;

            // Main query
            $sql = "SELECT pl.*,
                           inv.invoice_no,
                           inv.company_id,
                           c.name as company_name,
                           CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                           CASE
                               WHEN pl.item_type = 1 THEN od.domain
                               WHEN pl.item_type = 2 THEN os.hosting_domain
                               ELSE NULL
                           END as item_name
                    FROM provisioning_logs pl
                    LEFT JOIN invoices inv ON pl.invoice_id = inv.id
                    LEFT JOIN companies c ON inv.company_id = c.id
                    LEFT JOIN order_domains od ON pl.item_type = 1 AND pl.ref_id = od.id
                    LEFT JOIN order_services os ON pl.item_type = 2 AND pl.ref_id = os.id
                    $where
                    ORDER BY $orderColumn $orderDir
                    LIMIT $start, $length";

            $data = $this->db->query($sql, $bindings)->result_array();

            // Count total records
            $totalRecords = $this->db->count_all('provisioning_logs');

            // Count filtered records
            $countSql = "SELECT COUNT(*) as cnt
                         FROM provisioning_logs pl
                         LEFT JOIN invoices inv ON pl.invoice_id = inv.id
                         $where";
            $filteredRecords = $this->db->query($countSql, $bindings)->row()->cnt;

            echo json_encode(array(
                'draw' => isset($params['draw']) ? intval($params['draw']) : 0,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ));
        } catch (Exception $e) {
            echo json_encode(array(
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Get provisioning log detail
     */
    public function log_detail($id)
    {
        header('Content-Type: application/json');

        $sql = "SELECT pl.*,
                       inv.invoice_no,
                       inv.company_id,
                       c.name as company_name,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       CASE
                           WHEN pl.item_type = 1 THEN od.domain
                           WHEN pl.item_type = 2 THEN os.hosting_domain
                           ELSE NULL
                       END as item_name,
                       CASE
                           WHEN pl.item_type = 1 THEN od.order_type
                           ELSE NULL
                       END as domain_order_type,
                       CASE
                           WHEN pl.item_type = 2 THEN os.product_service_type_key
                           ELSE NULL
                       END as service_type
                FROM provisioning_logs pl
                LEFT JOIN invoices inv ON pl.invoice_id = inv.id
                LEFT JOIN companies c ON inv.company_id = c.id
                LEFT JOIN order_domains od ON pl.item_type = 1 AND pl.ref_id = od.id
                LEFT JOIN order_services os ON pl.item_type = 2 AND pl.ref_id = os.id
                WHERE pl.id = ?";

        $log = $this->db->query($sql, array(intval($id)))->row_array();

        if ($log) {
            echo json_encode(array('success' => true, 'data' => $log));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Log not found'));
        }
    }

    /**
     * Retry provisioning for an invoice
     */
    public function retry($invoiceId)
    {
        header('Content-Type: application/json');

        if (empty($invoiceId) || !is_numeric($invoiceId)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid invoice ID'));
            return;
        }

        // Check if invoice exists
        $invoice = $this->db->where('id', $invoiceId)->get('invoices')->row();
        if (!$invoice) {
            echo json_encode(array('success' => false, 'message' => 'Invoice not found'));
            return;
        }

        // Run provisioning
        $results = $this->Invoice_model->retryProvisioning($invoiceId);

        if ($results['items_processed'] == 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'No items to provision for this invoice'
            ));
        } elseif ($results['items_failed'] == 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'All ' . $results['items_success'] . ' items provisioned successfully'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => $results['items_failed'] . ' of ' . $results['items_processed'] . ' items failed to provision',
                'details' => $results['details']
            ));
        }
    }

    /**
     * Retry single item provisioning
     */
    public function retry_item($logId)
    {
        header('Content-Type: application/json');

        if (empty($logId) || !is_numeric($logId)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid log ID'));
            return;
        }

        // Get the log entry
        $log = $this->db->where('id', $logId)->get('provisioning_logs')->row_array();
        if (!$log) {
            echo json_encode(array('success' => false, 'message' => 'Log not found'));
            return;
        }

        // Get the invoice item
        $invoiceItem = $this->db->where('id', $log['invoice_item_id'])->get('invoice_items')->row_array();
        if (!$invoiceItem) {
            echo json_encode(array('success' => false, 'message' => 'Invoice item not found'));
            return;
        }

        // Add company_id to item
        $invoice = $this->db->where('id', $invoiceItem['invoice_id'])->get('invoices')->row_array();
        $invoiceItem['company_id'] = $invoice['company_id'];

        // Provision the item
        if ($invoiceItem['item_type'] == 1) {
            $result = $this->Provisioning_model->provisionDomain($invoiceItem);
        } else {
            $result = $this->Provisioning_model->provisionService($invoiceItem);
        }

        // Update retry count
        $this->db->where('id', $logId);
        $this->db->set('retry_count', 'retry_count + 1', FALSE);
        $this->db->update('provisioning_logs');

        if ($result['success']) {
            // Log new successful attempt
            $this->db->insert('provisioning_logs', array(
                'invoice_id' => $log['invoice_id'],
                'invoice_item_id' => $log['invoice_item_id'],
                'item_type' => $log['item_type'],
                'ref_id' => $log['ref_id'],
                'action' => $result['action'] ?? 'retry',
                'success' => 1,
                'error_message' => null,
                'response_data' => json_encode($result),
                'retry_count' => 0,
                'inserted_on' => date('Y-m-d H:i:s')
            ));

            echo json_encode(array(
                'success' => true,
                'message' => 'Item provisioned successfully',
                'result' => $result
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Provisioning failed: ' . $result['error'],
                'result' => $result
            ));
        }
    }

    /**
     * Get failed provisioning count (for dashboard widget)
     */
    public function failed_count_api()
    {
        header('Content-Type: application/json');

        $this->db->where('success', 0);
        $count = $this->db->count_all_results('provisioning_logs');

        echo json_encode(array('count' => $count));
    }
}
