<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class General_setting extends WHMAZADMIN_Controller {

	var $img_path;

	function __construct() {
		parent::__construct();
		$this->load->model('Appsetting_model');
		$this->load->model('Dunningrule_model');
		$this->load->model('Emailtemplate_model');
		$this->load->model('Syscnf_model');
		$this->load->model('Cronschedule_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/mics/');
	}

	public function index() {
		redirect('/whmazadmin/general_setting/manage', 'refresh');
	}

	public function manage() {
		if ($this->input->post()) {
			// Form validation
			$this->form_validation->set_rules('site_name', 'Site Name', 'required|trim');
			$this->form_validation->set_rules('company_name', 'Company Name', 'required|trim');
			$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');

			if ($this->form_validation->run() == true) {

				// Get existing settings to preserve old file paths if no new upload
				$existing = $this->Appsetting_model->getSettings();

				// Handle Logo upload
				$logo_name = !empty($existing['logo']) ? $existing['logo'] : '';
				if (!empty($_FILES['logo']['name']) && $_FILES['logo']['size'] > 0) {
					$logo_upload = $this->upload_single_file('logo', 'logo_');
					if ($logo_upload['success']) {
						$logo_name = $logo_upload['file_name'];
					} else {
						$this->session->set_flashdata('admin_error', 'Logo upload failed: ' . $logo_upload['error']);
						redirect('whmazadmin/general_setting/manage');
						return;
					}
				}

				// Handle Favicon upload
				$favicon_name = !empty($existing['favicon']) ? $existing['favicon'] : '';
				if (!empty($_FILES['favicon']['name']) && $_FILES['favicon']['size'] > 0) {
					$favicon_upload = $this->upload_single_file('favicon', 'favicon_');
					if ($favicon_upload['success']) {
						$favicon_name = $favicon_upload['file_name'];
					} else {
						$this->session->set_flashdata('admin_error', 'Favicon upload failed: ' . $favicon_upload['error']);
						redirect('whmazadmin/general_setting/manage');
						return;
					}
				}

				$form_data = array(
					'site_name'          => $this->input->post('site_name'),
					'site_desc'          => $this->input->post('site_desc'),
					'admin_url'          => $this->input->post('admin_url'),
					'favicon'            => $favicon_name,
					'logo'               => $logo_name,
					'bin_tax'            => $this->input->post('bin_tax'),
					'company_name'       => $this->input->post('company_name'),
					'company_address'    => $this->input->post('company_address'),
					'zip_code'           => $this->input->post('zip_code'),
					'city'               => $this->input->post('city'),
					'state'              => $this->input->post('state'),
					'country'            => $this->input->post('country'),
					'email'              => $this->input->post('email'),
					'fax'                => $this->input->post('fax'),
					'phone'              => $this->input->post('phone'),
					'smtp_host'          => $this->input->post('smtp_host'),
					'smtp_port'          => $this->input->post('smtp_port'),
					'smtp_username'      => $this->input->post('smtp_username'),
					'smtp_authkey'       => $this->input->post('smtp_authkey'),
					'captcha_site_key'   => $this->input->post('captcha_site_key'),
					'captcha_secret_key' => $this->input->post('captcha_secret_key'),
					'updated_on'         => getDateTime(),
					'updated_by'         => getAdminId()
				);

				$resp = $this->Appsetting_model->saveData($form_data);
				if ($resp['success'] == 1) {
					$this->session->set_flashdata('admin_success', 'General settings have been saved successfully.');
				} else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Please try again.');
				}

				redirect('whmazadmin/general_setting/manage');
			}
		}

		// Load existing settings
		$data['detail'] = $this->Appsetting_model->getSettings();
		$data['dunning_rules'] = $this->Dunningrule_model->loadAllData();
		$data['dunning_email_templates'] = $this->Emailtemplate_model->loadByCategory('DUNNING');
		$data['sys_configs'] = $this->Syscnf_model->getAllGrouped();
		$data['cron_schedules'] = $this->Cronschedule_model->getAll();
		$data['cron_secret_key'] = $this->Syscnf_model->getValue('cron_secret_key');
		$data['countries'] = $this->Appsetting_model->getCountries();

		// Determine active tab
		$tab = $this->input->get('tab');
		if ($tab === 'dunning') {
			$data['active_tab'] = 'dunning';
		} elseif ($tab === 'sysconfig') {
			$data['active_tab'] = 'sysconfig';
		} elseif ($tab === 'cronjobs') {
			$data['active_tab'] = 'cronjobs';
		} else {
			$data['active_tab'] = 'general';
		}

		$this->load->view('whmazadmin/general_setting_manage', $data);
	}

	/**
	 * Save dunning rule (AJAX)
	 */
	public function save_dunning_rule() {
		if (!$this->input->is_ajax_request() || !$this->input->post()) {
			show_404();
		}

		$this->form_validation->set_rules('step_number', 'Step Number', 'required|integer|greater_than[0]');
		$this->form_validation->set_rules('days_after_due', 'Days After Due', 'required|integer|greater_than_equal_to[0]');
		$this->form_validation->set_rules('action_type', 'Action Type', 'required|in_list[EMAIL,SUSPEND,TERMINATE]');

		if ($this->form_validation->run() == false) {
			echo json_encode(array('success' => 0, 'message' => strip_tags(validation_errors())));
			return;
		}

		$id = intval($this->input->post('id'));
		$step_number = intval($this->input->post('step_number'));

		// Check duplicate step number
		if ($this->Dunningrule_model->isStepExists($step_number, $id)) {
			echo json_encode(array('success' => 0, 'message' => 'Step number ' . $step_number . ' already exists.'));
			return;
		}

		$data = array(
			'id'             => $id,
			'step_number'    => $step_number,
			'days_after_due' => intval($this->input->post('days_after_due')),
			'action_type'    => $this->input->post('action_type'),
			'email_template' => $this->input->post('email_template'),
			'is_active'      => $this->input->post('is_active') ? 1 : 0,
		);

		$resp = $this->Dunningrule_model->saveData($data);
		if ($resp['success'] == 1) {
			echo json_encode(array('success' => 1, 'message' => 'Dunning rule saved successfully.'));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Failed to save dunning rule.'));
		}
	}

	/**
	 * Get dunning rule detail (AJAX)
	 */
	public function get_dunning_rule($id = 0) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$id = intval($id);
		$detail = $this->Dunningrule_model->getDetail($id);

		if (!empty($detail)) {
			echo json_encode(array('success' => 1, 'data' => $detail));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Rule not found.'));
		}
	}

	/**
	 * Delete dunning rule (AJAX)
	 */
	public function delete_dunning_rule($id = 0) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$id = intval($id);
		$resp = $this->Dunningrule_model->deleteData($id);

		if ($resp['success'] == 1) {
			echo json_encode(array('success' => 1, 'message' => 'Dunning rule deleted successfully.'));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Failed to delete dunning rule.'));
		}
	}

	/**
	 * Get all dunning rules as JSON (AJAX)
	 */
	public function get_dunning_rules() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$rules = $this->Dunningrule_model->loadAllData();
		echo json_encode(array('success' => 1, 'data' => $rules));
	}

	/**
	 * Update system config value (AJAX)
	 */
	public function update_sysconfig() {
		if (!$this->input->is_ajax_request() || !$this->input->post()) {
			show_404();
		}

		$id = intval($this->input->post('id'));
		$value = $this->input->post('value');

		if ($id <= 0) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid configuration ID.'));
			return;
		}

		// Verify the config exists
		$config = $this->Syscnf_model->getById($id);
		if (empty($config)) {
			echo json_encode(array('success' => 0, 'message' => 'Configuration not found.'));
			return;
		}

		$resp = $this->Syscnf_model->updateValue($id, $value);
		if ($resp['success'] == 1) {
			echo json_encode(array('success' => 1, 'message' => 'Configuration updated successfully.'));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Failed to update configuration.'));
		}
	}

	/**
	 * Get system config detail (AJAX)
	 */
	public function get_sysconfig($id = 0) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$id = intval($id);
		$detail = $this->Syscnf_model->getById($id);

		if (!empty($detail)) {
			echo json_encode(array('success' => 1, 'data' => $detail));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Configuration not found.'));
		}
	}

	/**
	 * Update cronjob schedule (AJAX)
	 */
	public function update_cronjob() {
		if (!$this->input->is_ajax_request() || !$this->input->post()) {
			show_404();
		}

		$id = intval($this->input->post('id'));

		if ($id <= 0) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid schedule ID.'));
			return;
		}

		// Validate schedule parts
		$minute = trim($this->input->post('schedule_minute'));
		$hour = trim($this->input->post('schedule_hour'));
		$day = trim($this->input->post('schedule_day'));
		$month = trim($this->input->post('schedule_month'));
		$weekday = trim($this->input->post('schedule_weekday'));

		// Basic validation for cron fields
		$cronPattern = '/^(\*|(\*\/)?[0-9]+(-[0-9]+)?(,[0-9]+(-[0-9]+)?)*)$/';

		if (!preg_match($cronPattern, $minute) || intval($minute) > 59) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid minute value (0-59 or *)'));
			return;
		}
		if (!preg_match($cronPattern, $hour) || (is_numeric($hour) && intval($hour) > 23)) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid hour value (0-23 or *)'));
			return;
		}
		if (!preg_match($cronPattern, $day) || (is_numeric($day) && intval($day) > 31)) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid day value (1-31 or *)'));
			return;
		}
		if (!preg_match($cronPattern, $month) || (is_numeric($month) && intval($month) > 12)) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid month value (1-12 or *)'));
			return;
		}
		if (!preg_match($cronPattern, $weekday) || (is_numeric($weekday) && intval($weekday) > 6)) {
			echo json_encode(array('success' => 0, 'message' => 'Invalid weekday value (0-6 or *)'));
			return;
		}

		$data = array(
			'schedule_minute' => $minute,
			'schedule_hour' => $hour,
			'schedule_day' => $day,
			'schedule_month' => $month,
			'schedule_weekday' => $weekday
		);

		$resp = $this->Cronschedule_model->updateSchedule($id, $data);
		if ($resp['success'] == 1) {
			echo json_encode(array('success' => 1, 'message' => 'Schedule updated successfully.'));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Failed to update schedule.'));
		}
	}

	/**
	 * Toggle cronjob active status (AJAX)
	 */
	public function toggle_cronjob($id = 0) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$id = intval($id);
		$resp = $this->Cronschedule_model->toggleActive($id);

		if ($resp['success'] == 1) {
			$status = $resp['is_active'] == 1 ? 'enabled' : 'disabled';
			echo json_encode(array('success' => 1, 'message' => 'Cronjob ' . $status . '.', 'is_active' => $resp['is_active']));
		} else {
			echo json_encode(array('success' => 0, 'message' => $resp['message'] ?? 'Failed to toggle cronjob.'));
		}
	}

	/**
	 * Get cronjob schedule detail (AJAX)
	 */
	public function get_cronjob($id = 0) {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$id = intval($id);
		$detail = $this->Cronschedule_model->getById($id);

		if (!empty($detail)) {
			$detail['cron_expression'] = $this->Cronschedule_model->buildCronExpression($detail);
			$detail['schedule_description'] = $this->Cronschedule_model->getScheduleDescription($detail);
			echo json_encode(array('success' => 1, 'data' => $detail));
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Schedule not found.'));
		}
	}

	/**
	 * Generate crontab content (AJAX)
	 */
	public function generate_crontab() {
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		$cronKey = $this->Syscnf_model->getValue('cron_secret_key');

		if (empty($cronKey)) {
			echo json_encode(array(
				'success' => 0,
				'message' => 'Cron secret key not configured. Please add "cron_secret_key" in System Config first.'
			));
			return;
		}

		$baseUrl = base_url();
		$crontab = $this->Cronschedule_model->generateCrontab($baseUrl, $cronKey);

		echo json_encode(array(
			'success' => 1,
			'crontab' => $crontab
		));
	}

	/**
	 * Install crontab to system (AJAX)
	 */
	public function install_crontab() {
		if (!$this->input->is_ajax_request() || !$this->input->post()) {
			show_404();
		}

		$cronKey = $this->Syscnf_model->getValue('cron_secret_key');

		if (empty($cronKey)) {
			echo json_encode(array(
				'success' => 0,
				'message' => 'Cron secret key not configured. Please add "cron_secret_key" in System Config first.'
			));
			return;
		}

		$baseUrl = base_url();
		$resp = $this->Cronschedule_model->installCrontab($baseUrl, $cronKey);

		echo json_encode($resp);
	}

	/**
	 * Upload single file with security checks
	 */
	private function upload_single_file($field_name, $prefix = '') {
		$result = array('success' => false, 'file_name' => '', 'error' => '');

		// Security: Maximum file size (2MB for images)
		$max_file_size = 2048; // 2MB in KB

		// Security: Allowed MIME types for images
		$allowed_mimes = array(
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/gif',
			'image/x-icon',
			'image/vnd.microsoft.icon'
		);

		// Check file size
		if ($_FILES[$field_name]['size'] > ($max_file_size * 1024)) {
			$result['error'] = 'File size exceeds 2MB limit.';
			return $result;
		}

		// Verify MIME type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $_FILES[$field_name]['tmp_name']);
		finfo_close($finfo);

		if (!in_array($mime_type, $allowed_mimes)) {
			$result['error'] = 'Invalid file type. Only JPG, PNG, GIF, and ICO files are allowed.';
			return $result;
		}

		// Get safe file extension
		$original_ext = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
		$allowed_exts = array('gif', 'jpg', 'jpeg', 'png', 'ico');

		if (!in_array($original_ext, $allowed_exts)) {
			$result['error'] = 'Invalid file extension.';
			return $result;
		}

		// Generate secure random filename
		$random_name = $prefix . bin2hex(random_bytes(16));
		$file_name = $random_name . '.' . $original_ext;

		$config = array(
			'upload_path'   => $this->img_path,
			'allowed_types' => 'gif|jpg|jpeg|png|ico',
			'max_size'      => $max_file_size,
			'overwrite'     => false,
			'file_name'     => $file_name
		);

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		if ($this->upload->do_upload($field_name)) {
			$upload_data = $this->upload->data();
			$result['success'] = true;
			$result['file_name'] = $upload_data['file_name'];
		} else {
			$result['error'] = $this->upload->display_errors('', '');
		}

		return $result;
	}
}
