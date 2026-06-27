<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create the SaaS subscription plan catalog + entitlement schema.
 *
 *   * `plans`          - Basic / Pro / Max catalog tiers
 *   * `plan_features`  - differentiated entitlement flags per plan
 *   * `order_licenses` - a WHMAZ SaaS subscription line (own product line,
 *                        separate from order_services / order_domains)
 *
 * Mirrors plans_subscription_schema.sql. Seeding is idempotent (upsert on
 * plan_key / (plan_id, feature_key)) so re-running the seed never duplicates.
 *
 * Note: CI3's dbforge cannot express UNIQUE or FOREIGN KEY constraints, so a
 * few targeted ALTER statements are used purely for those. All data access
 * (seed/read) uses the query builder.
 */
class Migration_Create_plans_subscription extends CI_Migration {

	public function up()
	{
		// ── plans ──────────────────────────────────────────────────────────
		$this->dbforge->add_field(array(
			'id'                      => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'plan_key'                => array('type' => 'VARCHAR', 'constraint' => 32),
			'name'                    => array('type' => 'VARCHAR', 'constraint' => 60),
			'tagline'                 => array('type' => 'VARCHAR', 'constraint' => 150, 'null' => TRUE),
			'price_monthly'           => array('type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0.00),
			'price_annual'            => array('type' => 'DECIMAL', 'constraint' => '8,2', 'default' => 0.00),
			'currency'                => array('type' => 'CHAR', 'constraint' => 3, 'default' => 'USD'),
			'is_popular'              => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 0),
			'sort_order'              => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
			'is_active'               => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 1),
			'paddle_product_id'       => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
			'paddle_price_monthly_id' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
			'paddle_price_annual_id'  => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
			'created_at'              => array('type' => 'DATETIME', 'null' => TRUE),
			'updated_at'              => array('type' => 'DATETIME', 'null' => TRUE),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('plans', TRUE, array(
			'ENGINE'  => 'InnoDB',
			'CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_general_ci',
		));

		// ── plan_features ──────────────────────────────────────────────────
		$this->dbforge->add_field(array(
			'id'            => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'plan_id'       => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE),
			'feature_key'   => array('type' => 'VARCHAR', 'constraint' => 64),
			'feature_value' => array('type' => 'VARCHAR', 'constraint' => 255),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('plan_features', TRUE, array(
			'ENGINE'  => 'InnoDB',
			'CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_general_ci',
		));

		// Constraints dbforge can't express (UNIQUE / FK).
		$this->db->query('ALTER TABLE `plans` ADD UNIQUE KEY `uq_plans_plan_key` (`plan_key`)');
		$this->db->query('ALTER TABLE `plan_features`
			ADD UNIQUE KEY `uq_plan_features_plan_feature` (`plan_id`, `feature_key`),
			ADD CONSTRAINT `fk_plan_features_plan`
				FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE');

		// ── order_licenses (a WHMAZ SaaS subscription line) ────────────────
		$this->dbforge->add_field(array(
			'id'                     => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'order_id'               => array('type' => 'BIGINT', 'constraint' => 20),
			'company_id'             => array('type' => 'BIGINT', 'constraint' => 20),
			'plan_id'                => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE),
			'billing_cycle_id'       => array('type' => 'INT', 'constraint' => 11),
			'currency_id'            => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
			'currency_code'          => array('type' => 'VARCHAR', 'constraint' => 3, 'null' => TRUE),
			'first_pay_amount'       => array('type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00),
			'recurring_amount'       => array('type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00),
			'license_key'            => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
			'paddle_subscription_id' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
			'auto_renew'             => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
			'reg_date'               => array('type' => 'DATE', 'null' => TRUE),
			'exp_date'               => array('type' => 'DATE', 'null' => TRUE),
			'due_date'               => array('type' => 'DATE', 'null' => TRUE),
			'next_renewal_date'      => array('type' => 'DATE', 'null' => TRUE),
			'suspension_date'        => array('type' => 'DATE', 'null' => TRUE),
			'suspension_reason'      => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
			'termination_date'       => array('type' => 'DATE', 'null' => TRUE),
			'is_synced'              => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 1),
			'last_sync_dt'           => array('type' => 'DATETIME', 'null' => TRUE),
			'license_domain'         => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
			'last_check_in'          => array('type' => 'DATETIME', 'null' => TRUE),
			'last_check_ip'          => array('type' => 'VARCHAR', 'constraint' => 45, 'null' => TRUE),
			'status'                 => array('type' => 'TINYINT', 'constraint' => 4, 'default' => 0),
			'remarks'                => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
			'inserted_on'            => array('type' => 'DATETIME', 'null' => TRUE),
			'inserted_by'            => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'updated_by'             => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
			'deleted_on'             => array('type' => 'DATETIME', 'null' => TRUE),
			'deleted_by'             => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('order_licenses', TRUE, array(
			'ENGINE'  => 'InnoDB',
			'CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_general_ci',
		));
		$this->db->query('ALTER TABLE `order_licenses`
			ADD COLUMN `updated_on` timestamp NOT NULL DEFAULT current_timestamp()
				ON UPDATE current_timestamp() AFTER `inserted_by`,
			ADD KEY `idx_order_licenses_company` (`company_id`),
			ADD KEY `idx_order_licenses_order` (`order_id`),
			ADD KEY `idx_order_licenses_plan` (`plan_id`),
			ADD KEY `idx_order_licenses_status` (`status`),
			ADD CONSTRAINT `fk_order_licenses_plan`
				FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)');

		// ── seed ───────────────────────────────────────────────────────────
		$this->_seed();
	}

	public function down()
	{
		$this->dbforge->drop_table('order_licenses', TRUE);
		$this->dbforge->drop_table('plan_features', TRUE);
		$this->dbforge->drop_table('plans', TRUE);
	}

	// -------------------------------------------------------------------------

	/**
	 * Idempotent seed of the 3 plans and their differentiated feature flags.
	 * Universal features are intentionally not stored (see config/plans.php).
	 */
	private function _seed()
	{
		$now = date('Y-m-d H:i:s');

		$plans = array(
			'basic' => array(
				'name' => 'Basic', 'tagline' => 'For new & small hosts',
				'price_monthly' => 10.95, 'price_annual' => 111.72,
				'is_popular' => 0, 'sort_order' => 1,
				'features' => array(
					'support_response_hours' => '72', 'priority_support' => '0',
					'advanced_modules' => '0', 'automatic_updates' => '0',
					'branding_removal' => '0', 'dedicated_account_manager' => '0',
					'sla_guarantee' => '0',
				),
			),
			'pro' => array(
				'name' => 'Pro', 'tagline' => 'For growing hosts',
				'price_monthly' => 15.95, 'price_annual' => 162.69,
				'is_popular' => 1, 'sort_order' => 2,
				'features' => array(
					'support_response_hours' => '48', 'priority_support' => '1',
					'advanced_modules' => '1', 'automatic_updates' => '1',
					'branding_removal' => '0', 'dedicated_account_manager' => '0',
					'sla_guarantee' => '0',
				),
			),
			'max' => array(
				'name' => 'Max', 'tagline' => 'For established hosts',
				'price_monthly' => 24.95, 'price_annual' => 254.49,
				'is_popular' => 0, 'sort_order' => 3,
				'features' => array(
					'support_response_hours' => '24', 'priority_support' => '1',
					'advanced_modules' => '1', 'automatic_updates' => '1',
					'branding_removal' => '1', 'dedicated_account_manager' => '1',
					'sla_guarantee' => '1',
				),
			),
		);

		foreach ($plans as $key => $plan)
		{
			$row = array(
				'name'          => $plan['name'],
				'tagline'       => $plan['tagline'],
				'price_monthly' => $plan['price_monthly'],
				'price_annual'  => $plan['price_annual'],
				'currency'      => 'USD',
				'is_popular'    => $plan['is_popular'],
				'sort_order'    => $plan['sort_order'],
				'is_active'     => 1,
				'updated_at'    => $now,
			);

			$existing = $this->db->select('id')->get_where('plans', array('plan_key' => $key))->row();
			if ($existing)
			{
				$planId = (int) $existing->id;
				$this->db->where('id', $planId)->update('plans', $row);
			}
			else
			{
				$row['plan_key']   = $key;
				$row['created_at'] = $now;
				$this->db->insert('plans', $row);
				$planId = (int) $this->db->insert_id();
			}

			foreach ($plan['features'] as $fkey => $fval)
			{
				$feat = $this->db->select('id')
					->get_where('plan_features', array('plan_id' => $planId, 'feature_key' => $fkey))
					->row();
				if ($feat)
				{
					$this->db->where('id', (int) $feat->id)->update('plan_features', array('feature_value' => $fval));
				}
				else
				{
					$this->db->insert('plan_features', array(
						'plan_id'       => $planId,
						'feature_key'   => $fkey,
						'feature_value' => $fval,
					));
				}
			}
		}

		// Dedicated license suspension grace period (sys_cnf, AUTOMATION group).
		$cnfKey = 'license_suspension_days_after_due';
		$exists = $this->db->get_where('sys_cnf', array('cnf_key' => $cnfKey))->row();
		if ( ! $exists)
		{
			$this->db->insert('sys_cnf', array(
				'cnf_key'    => $cnfKey,
				'cnf_val'    => '7',
				'cnf_group'  => 'AUTOMATION',
				'created_on' => $now,
				'updated_on' => $now,
			));
		}
	}
}
