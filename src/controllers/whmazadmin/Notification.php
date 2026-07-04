<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin in-app notifications (bell + unread badge + mark-as-read).
 * Recipient = the logged-in admin (admin_users.id).
 */
class Notification extends WHMAZADMIN_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model('Notification_model');
        if (!$this->isLogin()) {
            redirect('/whmazadmin/authenticate/login', 'refresh');
        }
    }

    /**
     * Recent notifications + unread count (for the dropdown).
     */
    public function list_api()
    {
        header('Content-Type: application/json');

        $adminId = getAdminId();
        $rows = $this->Notification_model->getRecent(Notification_model::RECIPIENT_ADMIN, $adminId, 15);

        foreach ($rows as &$row) {
            $row['time_ago'] = time_ago($row['inserted_on']);
            $row['url'] = !empty($row['url']) ? $row['url'] : '';
        }

        echo json_encode(array(
            'success'      => true,
            'notifications'=> $rows,
            'unread_count' => $this->Notification_model->countUnread(Notification_model::RECIPIENT_ADMIN, $adminId)
        ));
        exit;
    }

    /**
     * Unread count only (lightweight badge poll).
     */
    public function unread_count()
    {
        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'count'   => $this->Notification_model->countUnread(Notification_model::RECIPIENT_ADMIN, getAdminId())
        ));
        exit;
    }

    /**
     * Mark one notification read.
     */
    public function mark_read()
    {
        header('Content-Type: application/json');
        $id = (int) $this->input->post('id');
        $this->Notification_model->markRead($id, Notification_model::RECIPIENT_ADMIN, getAdminId());
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * Mark all read.
     */
    public function mark_all_read()
    {
        header('Content-Type: application/json');
        $this->Notification_model->markAllRead(Notification_model::RECIPIENT_ADMIN, getAdminId());
        echo json_encode(array('success' => true));
        exit;
    }
}
