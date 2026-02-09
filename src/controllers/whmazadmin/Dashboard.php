<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends WHMAZADMIN_Controller
{
	function __construct()
	{
		parent::__construct();
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}

		$this->load->model('Dashboard_model');
	}

	public function index()
	{
		$this->load->view('whmazadmin/dashboard_index');
	}

	public function summary_api() {
		// Send CSRF headers for Angular to update token
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		echo json_encode($this->Dashboard_model->loadSummaryData());
	}

	/**
	 * API endpoint for domain selling prices
	 */
	public function domain_prices_api() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		$limit = $this->input->post('limit') ? intval($this->input->post('limit')) : 10;
		$data = $this->Dashboard_model->getDomainPrices($limit);

		echo json_encode($data);
	}

	/**
	 * API endpoint for last 12 months expenses chart data
	 */
	public function expenses_chart_api() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		$data = $this->Dashboard_model->getLast12MonthsExpenses();

		echo json_encode($data);
	}

	public function changePassword()
	{
		if (!$this->input->post()) {
			$this->load->view('whmazadmin/dashboard_changepassword');
			return;
		}

		$currentPassword = $this->input->post('current_password');
		$newPassword     = $this->input->post('new_password');
		$confirmPassword = $this->input->post('confirm_password');

		if (strlen($newPassword) < 8) {
			$this->session->set_flashdata('admin_error', 'New password must be at least 8 characters.');
			redirect('/whmazadmin/dashboard/changePassword', 'refresh');
			return;
		}

		if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
			$this->session->set_flashdata('admin_error', 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
			redirect('/whmazadmin/dashboard/changePassword', 'refresh');
			return;
		}

		if ($newPassword !== $confirmPassword) {
			$this->session->set_flashdata('admin_error', 'New passwords do not match.');
			redirect('/whmazadmin/dashboard/changePassword', 'refresh');
			return;
		}

		$result = $this->Adminauth_model->changePassword(getAdminId(), $currentPassword, $newPassword);

		if ($result['success']) {
			$appSettings = getAppSettings();
			$userName = !empty($result['first_name']) ? htmlspecialchars($result['first_name']) : 'Admin';

			$body = 'Dear ' . $userName . ',<br><br>';
			$body .= 'Your admin password has been changed successfully.<br><br>';
			$body .= 'If you did not make this change, please contact us immediately.<br><br>';
			$body .= 'Thanks & Regards<br>';
			$body .= $appSettings->company_name . ' Support';

			$subject = "Admin Password Changed - " . $appSettings->company_name;
			sendHtmlEmail($result['email'], $subject, $body);

			$this->session->set_flashdata('admin_success', 'Password changed successfully.');
			redirect('/whmazadmin/dashboard/changePassword', 'refresh');
		} else {
			$this->session->set_flashdata('admin_error', $result['msg']);
			redirect('/whmazadmin/dashboard/changePassword', 'refresh');
		}
	}

}
