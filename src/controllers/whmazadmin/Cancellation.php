<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Domain Cancellation Requests
 *
 * Lists customer-submitted domain cancellation requests and lets an admin
 * process them (cancel the domain) or dismiss them.
 */
class Cancellation extends WHMAZADMIN_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Order_model');

        if (!$this->isLogin()) {
            redirect('/whmazadmin/authenticate/login', 'refresh');
        }
    }

    public function index()
    {
        $data['stats'] = $this->getStats();
        $this->load->view('whmazadmin/domain_cancellations', $data);
    }

    private function getStats()
    {
        $stats = array('total' => 0, 'pending' => 0, 'processed' => 0, 'dismissed' => 0);

        if (!$this->db->table_exists('domain_cancellation_requests')) {
            return $stats;
        }

        $stats['total'] = $this->db->count_all('domain_cancellation_requests');
        $this->db->where('status', 0);
        $stats['pending'] = $this->db->count_all_results('domain_cancellation_requests');
        $this->db->where('status', 1);
        $stats['processed'] = $this->db->count_all_results('domain_cancellation_requests');
        $this->db->where('status', 2);
        $stats['dismissed'] = $this->db->count_all_results('domain_cancellation_requests');

        return $stats;
    }

    /**
     * Server-side DataTable API
     */
    public function list_api()
    {
        header('Content-Type: application/json');

        try {
            if (!$this->db->table_exists('domain_cancellation_requests')) {
                echo json_encode(array(
                    'draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(),
                    'error' => 'Table domain_cancellation_requests does not exist. Please run the migration.'
                ));
                return;
            }

            $params = $this->input->get();
            $bindings = array();
            $where = "WHERE 1=1";

            if (isset($params['status']) && $params['status'] !== '') {
                $where .= " AND dcr.status = ?";
                $bindings[] = intval($params['status']);
            }

            if (!empty($params['search']['value'])) {
                $s = '%' . $params['search']['value'] . '%';
                $where .= " AND (dcr.domain LIKE ? OR c.name LIKE ? OR dcr.reason LIKE ?)";
                $bindings[] = $s;
                $bindings[] = $s;
                $bindings[] = $s;
            }

            $orderColumn = 'dcr.id';
            $orderDir = 'DESC';
            if (!empty($params['order'][0]['column'])) {
                $columns = array('dcr.id', 'dcr.domain', 'company_name', 'dcr.requested_on', 'dcr.status');
                $colIndex = intval($params['order'][0]['column']);
                if (isset($columns[$colIndex])) {
                    $orderColumn = $columns[$colIndex];
                }
                $orderDir = ($params['order'][0]['dir'] === 'asc') ? 'ASC' : 'DESC';
            }

            $start = isset($params['start']) ? intval($params['start']) : 0;
            $length = isset($params['length']) ? intval($params['length']) : 25;

            $sql = "SELECT dcr.*,
                           c.name as company_name,
                           CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                           od.status as domain_status
                    FROM domain_cancellation_requests dcr
                    LEFT JOIN companies c ON dcr.company_id = c.id
                    LEFT JOIN order_domains od ON dcr.domain_id = od.id
                    $where
                    ORDER BY $orderColumn $orderDir
                    LIMIT $start, $length";

            $data = $this->db->query($sql, $bindings)->result_array();

            $totalRecords = $this->db->count_all('domain_cancellation_requests');

            $countSql = "SELECT COUNT(*) as cnt
                         FROM domain_cancellation_requests dcr
                         LEFT JOIN companies c ON dcr.company_id = c.id
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
                'draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array(),
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Process a pending request: cancel the domain, mark request processed.
     * POST: id, cancel_type ('immediate'|'end_of_period'), note
     */
    public function process()
    {
        header('Content-Type: application/json');

        $id = $this->input->post('id');
        $cancelType = ($this->input->post('cancel_type') === 'end_of_period') ? 'end_of_period' : 'immediate';
        $note = trim(strval($this->input->post('note')));

        if (!is_numeric($id) || $id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Invalid request ID'));
            return;
        }

        $req = $this->db->query(
            "SELECT * FROM domain_cancellation_requests WHERE id = ? LIMIT 1",
            array(intval($id))
        )->row_array();

        if (empty($req)) {
            echo json_encode(array('success' => false, 'message' => 'Request not found'));
            return;
        }
        if (intval($req['status']) !== 0) {
            echo json_encode(array('success' => false, 'message' => 'This request has already been handled'));
            return;
        }

        $reasonSuffix = $note !== '' ? (': ' . $note) : (!empty($req['reason']) ? (': ' . $req['reason']) : '');
        $reason = 'Customer cancellation request' . $reasonSuffix;

        $ok = $this->Order_model->cancelDomain(intval($req['domain_id']), $cancelType, $reason);

        if (!$ok) {
            echo json_encode(array('success' => false, 'message' => 'Failed to cancel the domain'));
            return;
        }

        $this->db->query(
            "UPDATE domain_cancellation_requests SET status = 1, admin_note = ?, processed_on = ?, processed_by = ? WHERE id = ?",
            array(substr($note, 0, 255), date('Y-m-d H:i:s'), getAdminId(), intval($id))
        );

        echo json_encode(array('success' => true, 'message' => 'Domain cancelled and request marked as processed'));
    }

    /**
     * Dismiss a pending request without cancelling. POST: id, note
     */
    public function dismiss()
    {
        header('Content-Type: application/json');

        $id = $this->input->post('id');
        $note = trim(strval($this->input->post('note')));

        if (!is_numeric($id) || $id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Invalid request ID'));
            return;
        }

        $req = $this->db->query(
            "SELECT status FROM domain_cancellation_requests WHERE id = ? LIMIT 1",
            array(intval($id))
        )->row_array();

        if (empty($req)) {
            echo json_encode(array('success' => false, 'message' => 'Request not found'));
            return;
        }
        if (intval($req['status']) !== 0) {
            echo json_encode(array('success' => false, 'message' => 'This request has already been handled'));
            return;
        }

        $this->db->query(
            "UPDATE domain_cancellation_requests SET status = 2, admin_note = ?, processed_on = ?, processed_by = ? WHERE id = ?",
            array(substr($note, 0, 255), date('Y-m-d H:i:s'), getAdminId(), intval($id))
        );

        echo json_encode(array('success' => true, 'message' => 'Request dismissed'));
    }
}
