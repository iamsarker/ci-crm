<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create `software_releases` — the installable WHMAZ ZIP customers download.
 *
 * One build serves all plans (plan-agnostic); exactly one row is is_current=1.
 * Files are stored privately in uploadedfiles/software/ and served only through
 * the license-gated download endpoints.
 *
 * Mirrors software_releases_schema.sql.
 */
class Migration_Create_software_releases extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_field(array(
			'id'            => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'version'       => array('type' => 'VARCHAR', 'constraint' => 40),
			'file_name'     => array('type' => 'VARCHAR', 'constraint' => 255),
			'original_name' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
			'file_size'     => array('type' => 'BIGINT', 'constraint' => 20, 'null' => TRUE),
			'changelog'     => array('type' => 'TEXT', 'null' => TRUE),
			'is_current'    => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
			'status'        => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 1),
			'uploaded_by'   => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'uploaded_on'   => array('type' => 'DATETIME', 'null' => TRUE),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('software_releases', TRUE, array(
			'ENGINE'  => 'InnoDB',
			'CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_general_ci',
		));

		$this->db->query('ALTER TABLE `software_releases`
			ADD COLUMN `updated_on` timestamp NOT NULL DEFAULT current_timestamp()
				ON UPDATE current_timestamp() AFTER `uploaded_on`,
			ADD KEY `idx_software_releases_current` (`is_current`, `status`)');
	}

	public function down()
	{
		$this->dbforge->drop_table('software_releases', TRUE);
	}
}
