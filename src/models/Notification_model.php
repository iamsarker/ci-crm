<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notification_model
 *
 * In-app notifications with per-recipient read state, shared by the
 * admin portal (recipient_type=1, recipient_id=admin_users.id) and the
 * customer portal (recipient_type=2, recipient_id=companies.id).
 */
class Notification_model extends CI_Model
{
    private $table = 'app_notifications';

    const RECIPIENT_ADMIN    = 1;
    const RECIPIENT_CUSTOMER = 2;

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Insert a single notification.
     *
     * @param array $data recipient_type, recipient_id, type, title, message, url, icon, inserted_by
     * @return int inserted id (0 on failure)
     */
    function add($data)
    {
        if (empty($data['recipient_type']) || empty($data['recipient_id']) || empty($data['title'])) {
            return 0;
        }

        $row = array(
            'recipient_type' => (int) $data['recipient_type'],
            'recipient_id'   => (int) $data['recipient_id'],
            'type'           => $data['type'] ?? 'system',
            'title'          => $data['title'],
            'message'        => $data['message'] ?? null,
            'url'            => $data['url'] ?? null,
            'icon'           => $data['icon'] ?? null,
            'is_read'        => 0,
            'inserted_on'    => date('Y-m-d H:i:s'),
            'inserted_by'    => $data['inserted_by'] ?? null
        );

        $this->db->insert($this->table, $row);
        return $this->db->insert_id();
    }

    /**
     * Notify every active admin (one row each, so read-state is per-admin).
     */
    function notifyAdmins($type, $title, $message = null, $url = null, $icon = null, $insertedBy = null)
    {
        $admins = $this->db->query("SELECT id FROM admin_users WHERE status = 1")->result_array();
        $count = 0;
        foreach ($admins as $admin) {
            $count += $this->add(array(
                'recipient_type' => self::RECIPIENT_ADMIN,
                'recipient_id'   => $admin['id'],
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'url'            => $url,
                'icon'           => $icon,
                'inserted_by'    => $insertedBy
            )) ? 1 : 0;
        }
        return $count;
    }

    /**
     * Notify a customer account (company-scoped).
     */
    function notifyCompany($companyId, $type, $title, $message = null, $url = null, $icon = null, $insertedBy = null)
    {
        if ((int) $companyId <= 0) {
            return 0;
        }
        return $this->add(array(
            'recipient_type' => self::RECIPIENT_CUSTOMER,
            'recipient_id'   => (int) $companyId,
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'url'            => $url,
            'icon'           => $icon,
            'inserted_by'    => $insertedBy
        ));
    }

    /**
     * Recent notifications for a recipient (newest first).
     */
    function getRecent($recipientType, $recipientId, $limit = 15)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('recipient_type', (int) $recipientType);
        $this->db->where('recipient_id', (int) $recipientId);
        $this->db->order_by('id', 'DESC');
        $this->db->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    /**
     * Count unread notifications for a recipient.
     */
    function countUnread($recipientType, $recipientId)
    {
        $this->db->from($this->table);
        $this->db->where('recipient_type', (int) $recipientType);
        $this->db->where('recipient_id', (int) $recipientId);
        $this->db->where('is_read', 0);
        return $this->db->count_all_results();
    }

    /**
     * Mark a single notification read (ownership-checked so a recipient
     * cannot mark another recipient's notification).
     */
    function markRead($id, $recipientType, $recipientId)
    {
        $this->db->where('id', (int) $id);
        $this->db->where('recipient_type', (int) $recipientType);
        $this->db->where('recipient_id', (int) $recipientId);
        $this->db->where('is_read', 0);
        return $this->db->update($this->table, array(
            'is_read' => 1,
            'read_on' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Mark all of a recipient's notifications read.
     */
    function markAllRead($recipientType, $recipientId)
    {
        $this->db->where('recipient_type', (int) $recipientType);
        $this->db->where('recipient_id', (int) $recipientId);
        $this->db->where('is_read', 0);
        return $this->db->update($this->table, array(
            'is_read' => 1,
            'read_on' => date('Y-m-d H:i:s')
        ));
    }
}
