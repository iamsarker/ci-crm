<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reseller Management + Third-Party REST API schema.
 *
 * Adds the first account-hierarchy to `companies` (parent_company_id +
 * is_reseller) and creates the tables backing the key-authenticated REST API:
 * reseller_profiles, api_keys, api_request_logs.
 *
 * CI3 dbforge can't express UNIQUE keys inline, so uniques/indexes are added
 * via raw ALTER — same approach as the plans/subscription migration.
 *
 * Mirrors reseller_api_migration.sql (canonical) and crm_db.sql.
 */
class Migration_Create_reseller_api extends CI_Migration {

	public function up()
	{
		// 1. companies: hierarchy columns
		$this->db->query('ALTER TABLE `companies`
			ADD COLUMN `parent_company_id` bigint(20) NOT NULL DEFAULT 0 AFTER `id`,
			ADD COLUMN `is_reseller` tinyint(4) NOT NULL DEFAULT 0 AFTER `parent_company_id`,
			ADD KEY `idx_companies_parent` (`parent_company_id`),
			ADD KEY `idx_companies_reseller` (`is_reseller`)');

		// 2. reseller_profiles
		$this->dbforge->add_field(array(
			'id'             => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'company_id'     => array('type' => 'BIGINT', 'constraint' => 20),
			'status'         => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 1),
			'discount_type'  => array('type' => 'VARCHAR', 'constraint' => 20, 'default' => 'percent'),
			'discount_value' => array('type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00),
			'credit_balance' => array('type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0.00),
			'currency_id'    => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'allow_api'      => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 1),
			'notes'          => array('type' => 'TEXT', 'null' => TRUE),
			'inserted_on'    => array('type' => 'DATETIME', 'null' => TRUE),
			'inserted_by'    => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'updated_on'     => array('type' => 'DATETIME', 'null' => TRUE),
			'updated_by'     => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'deleted_on'     => array('type' => 'DATETIME', 'null' => TRUE),
			'deleted_by'     => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('reseller_profiles', TRUE, array(
			'ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci',
		));
		$this->db->query('ALTER TABLE `reseller_profiles`
			ADD UNIQUE KEY `uniq_reseller_company` (`company_id`),
			ADD KEY `idx_reseller_status` (`status`)');

		// 3. api_keys
		$this->dbforge->add_field(array(
			'id'             => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'company_id'     => array('type' => 'BIGINT', 'constraint' => 20),
			'name'           => array('type' => 'VARCHAR', 'constraint' => 150),
			'key_id'         => array('type' => 'VARCHAR', 'constraint' => 48),
			'secret_hash'    => array('type' => 'VARCHAR', 'constraint' => 255),
			'secret_preview' => array('type' => 'VARCHAR', 'constraint' => 16, 'null' => TRUE),
			'scopes'         => array('type' => 'TEXT', 'null' => TRUE),
			'ip_whitelist'   => array('type' => 'TEXT', 'null' => TRUE),
			'rate_limit'     => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
			'status'         => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 1),
			'expires_at'     => array('type' => 'DATETIME', 'null' => TRUE),
			'last_used_at'   => array('type' => 'DATETIME', 'null' => TRUE),
			'last_used_ip'   => array('type' => 'VARCHAR', 'constraint' => 45, 'null' => TRUE),
			'request_count'  => array('type' => 'BIGINT', 'constraint' => 20, 'default' => 0),
			'inserted_on'    => array('type' => 'DATETIME', 'null' => TRUE),
			'inserted_by'    => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'updated_on'     => array('type' => 'DATETIME', 'null' => TRUE),
			'updated_by'     => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'deleted_on'     => array('type' => 'DATETIME', 'null' => TRUE),
			'deleted_by'     => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('api_keys', TRUE, array(
			'ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci',
		));
		$this->db->query('ALTER TABLE `api_keys`
			ADD UNIQUE KEY `uniq_api_key_id` (`key_id`),
			ADD KEY `idx_api_keys_company` (`company_id`),
			ADD KEY `idx_api_keys_status` (`status`)');

		// 4. api_request_logs
		$this->dbforge->add_field(array(
			'id'               => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'api_key_id'       => array('type' => 'BIGINT', 'constraint' => 20),
			'company_id'       => array('type' => 'BIGINT', 'constraint' => 20),
			'method'           => array('type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE),
			'endpoint'         => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
			'ip'               => array('type' => 'VARCHAR', 'constraint' => 45, 'null' => TRUE),
			'status_code'      => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'response_time_ms' => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'created_on'       => array('type' => 'DATETIME', 'null' => TRUE),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('api_request_logs', TRUE, array(
			'ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci',
		));
		$this->db->query('ALTER TABLE `api_request_logs`
			ADD KEY `idx_api_logs_key` (`api_key_id`),
			ADD KEY `idx_api_logs_company` (`company_id`),
			ADD KEY `idx_api_logs_created` (`created_on`)');
	}

	public function down()
	{
		$this->dbforge->drop_table('api_request_logs', TRUE);
		$this->dbforge->drop_table('api_keys', TRUE);
		$this->dbforge->drop_table('reseller_profiles', TRUE);
		$this->db->query('ALTER TABLE `companies`
			DROP KEY `idx_companies_parent`,
			DROP KEY `idx_companies_reseller`,
			DROP COLUMN `parent_company_id`,
			DROP COLUMN `is_reseller`');
	}
}
