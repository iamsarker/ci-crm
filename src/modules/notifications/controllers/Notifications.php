<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer in-app notifications (bell + unread badge + mark-as-read).
 * Recipient = the logged-in customer's account (companies.id), so every
 * user on the same account shares the notification feed.
 */
class Notifications extends WHMAZ_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model('Notification_model');
        if (!$this->isLogin()) {
            redirect('/auth/login', 'refresh');
        }
    }

    /**
     * Recent notifications + unread count (for the dropdown).
     */
    public function list_api()
    {
        header('Content-Type: application/json');

        $companyId = getCompanyId();
        $rows = $this->Notification_model->getRecent(Notification_model::RECIPIENT_CUSTOMER, $companyId, 15);

        foreach ($rows as &$row) {
            $row['time_ago'] = time_ago($row['inserted_on']);
            $row['url'] = !empty($row['url']) ? $row['url'] : '';
        }

        echo json_encode(array(
            'success'      => true,
            'notifications'=> $rows,
            'unread_count' => $this->Notification_model->countUnread(Notification_model::RECIPIENT_CUSTOMER, $companyId)
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
            'count'   => $this->Notification_model->countUnread(Notification_model::RECIPIENT_CUSTOMER, getCompanyId())
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
        $this->Notification_model->markRead($id, Notification_model::RECIPIENT_CUSTOMER, getCompanyId());
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * Mark all read.
     */
    public function mark_all_read()
    {
        header('Content-Type: application/json');
        $this->Notification_model->markAllRead(Notification_model::RECIPIENT_CUSTOMER, getCompanyId());
        echo json_encode(array('success' => true));
        exit;
    }
}
