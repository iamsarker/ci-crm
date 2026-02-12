<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Schedule Model
 * Manages cronjob schedules and Linux crontab integration
 */
class Cronschedule_model extends CI_Model
{
	private $table = 'cron_schedules';

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get all cronjob schedules
	 *
	 * @return array All schedules
	 */
	function getAll()
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->order_by('job_name', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Get active cronjob schedules
	 *
	 * @return array Active schedules
	 */
	function getActive()
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where('is_active', 1);
		$this->db->order_by('job_name', 'ASC');
		return $this->db->get()->result_array();
	}

	/**
	 * Get schedule by ID
	 *
	 * @param int $id Schedule ID
	 * @return array|null Schedule row
	 */
	function getById($id)
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where('id', intval($id));
		return $this->db->get()->row_array();
	}

	/**
	 * Update schedule
	 *
	 * @param int $id Schedule ID
	 * @param array $data Schedule data
	 * @return array Response with success status
	 */
	function updateSchedule($id, $data)
	{
		$data['updated_on'] = date('Y-m-d H:i:s');

		$this->db->where('id', intval($id));
		$result = $this->db->update($this->table, $data);

		return array('success' => $result ? 1 : 0);
	}

	/**
	 * Toggle active status
	 *
	 * @param int $id Schedule ID
	 * @return array Response with success status and new status
	 */
	function toggleActive($id)
	{
		$schedule = $this->getById($id);
		if (empty($schedule)) {
			return array('success' => 0, 'message' => 'Schedule not found');
		}

		$newStatus = $schedule['is_active'] == 1 ? 0 : 1;

		$this->db->where('id', intval($id));
		$result = $this->db->update($this->table, array(
			'is_active' => $newStatus,
			'updated_on' => date('Y-m-d H:i:s')
		));

		return array(
			'success' => $result ? 1 : 0,
			'is_active' => $newStatus
		);
	}

	/**
	 * Update last run time
	 *
	 * @param int $id Schedule ID
	 */
	function updateLastRun($id)
	{
		$this->db->where('id', intval($id));
		$this->db->update($this->table, array(
			'last_run' => date('Y-m-d H:i:s')
		));
	}

	/**
	 * Build cron expression from schedule parts
	 *
	 * @param array $schedule Schedule row
	 * @return string Cron expression (e.g., "0 6 * * *")
	 */
	function buildCronExpression($schedule)
	{
		return sprintf('%s %s %s %s %s',
			$schedule['schedule_minute'],
			$schedule['schedule_hour'],
			$schedule['schedule_day'],
			$schedule['schedule_month'],
			$schedule['schedule_weekday']
		);
	}

	/**
	 * Build full crontab line for a schedule
	 *
	 * @param array $schedule Schedule row
	 * @param string $baseUrl Base URL of the application
	 * @param string $cronKey Cron secret key
	 * @return string Full crontab line
	 */
	function buildCrontabLine($schedule, $baseUrl, $cronKey)
	{
		$cronExpr = $this->buildCronExpression($schedule);
		$url = rtrim($baseUrl, '/') . $schedule['job_command'] . '?key=' . $cronKey;

		return sprintf('%s curl -s "%s" > /dev/null 2>&1', $cronExpr, $url);
	}

	/**
	 * Generate complete crontab content for all active jobs
	 *
	 * @param string $baseUrl Base URL of the application
	 * @param string $cronKey Cron secret key
	 * @return string Crontab content
	 */
	function generateCrontab($baseUrl, $cronKey)
	{
		$schedules = $this->getActive();
		$lines = array();

		$lines[] = '# WHMAZ Cronjobs - Auto-generated';
		$lines[] = '# Generated on: ' . date('Y-m-d H:i:s');
		$lines[] = '# Do not edit manually - changes will be overwritten';
		$lines[] = '';

		foreach ($schedules as $schedule) {
			$lines[] = '# ' . $schedule['job_description'];
			$lines[] = $this->buildCrontabLine($schedule, $baseUrl, $cronKey);
			$lines[] = '';
		}

		return implode("\n", $lines);
	}

	/**
	 * Install crontab to system
	 * WARNING: Requires proper permissions
	 *
	 * @param string $baseUrl Base URL of the application
	 * @param string $cronKey Cron secret key
	 * @return array Response with success status and message
	 */
	function installCrontab($baseUrl, $cronKey)
	{
		// Generate crontab content
		$crontabContent = $this->generateCrontab($baseUrl, $cronKey);

		// Create temp file
		$tempFile = tempnam(sys_get_temp_dir(), 'whmaz_cron_');
		if ($tempFile === false) {
			return array('success' => 0, 'message' => 'Failed to create temporary file');
		}

		// Get existing crontab (excluding WHMAZ entries)
		$existingCrontab = '';
		exec('crontab -l 2>/dev/null', $output, $returnCode);
		if ($returnCode === 0) {
			$lines = array();
			$skipUntilEmpty = false;
			foreach ($output as $line) {
				if (strpos($line, '# WHMAZ Cronjobs') !== false) {
					$skipUntilEmpty = true;
					continue;
				}
				if ($skipUntilEmpty && trim($line) === '') {
					$skipUntilEmpty = false;
					continue;
				}
				if (!$skipUntilEmpty) {
					$lines[] = $line;
				}
			}
			$existingCrontab = implode("\n", $lines);
			if (!empty($existingCrontab)) {
				$existingCrontab .= "\n\n";
			}
		}

		// Write combined crontab
		$fullCrontab = $existingCrontab . $crontabContent;
		if (file_put_contents($tempFile, $fullCrontab) === false) {
			unlink($tempFile);
			return array('success' => 0, 'message' => 'Failed to write crontab file');
		}

		// Install crontab
		exec('crontab ' . escapeshellarg($tempFile) . ' 2>&1', $output, $returnCode);
		unlink($tempFile);

		if ($returnCode !== 0) {
			return array(
				'success' => 0,
				'message' => 'Failed to install crontab: ' . implode(' ', $output)
			);
		}

		return array('success' => 1, 'message' => 'Crontab installed successfully');
	}

	/**
	 * Get human-readable schedule description
	 *
	 * @param array $schedule Schedule row
	 * @return string Human-readable description
	 */
	function getScheduleDescription($schedule)
	{
		$minute = $schedule['schedule_minute'];
		$hour = $schedule['schedule_hour'];
		$day = $schedule['schedule_day'];
		$month = $schedule['schedule_month'];
		$weekday = $schedule['schedule_weekday'];

		// Common patterns
		if ($minute === '*' && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
			return 'Every minute';
		}

		if ($minute === '0' && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
			return 'Every hour';
		}

		if ($day === '*' && $month === '*' && $weekday === '*') {
			if ($minute !== '*' && $hour !== '*') {
				return 'Daily at ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
			}
		}

		// Build custom description
		$parts = array();

		if ($minute !== '*') {
			$parts[] = 'minute ' . $minute;
		}
		if ($hour !== '*') {
			$parts[] = 'hour ' . $hour;
		}
		if ($day !== '*') {
			$parts[] = 'day ' . $day;
		}
		if ($month !== '*') {
			$parts[] = 'month ' . $month;
		}
		if ($weekday !== '*') {
			$weekdays = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
			$parts[] = 'on ' . ($weekdays[$weekday] ?? $weekday);
		}

		return !empty($parts) ? 'At ' . implode(', ', $parts) : 'Custom schedule';
	}
}
