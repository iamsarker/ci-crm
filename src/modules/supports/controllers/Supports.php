<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supports extends WHMAZ_Controller {

	private $per_page = 10;

	function __construct(){
		parent::__construct();
		$this->load->model('Support_model');
		$this->load->model('Common_model');
		$this->load->model('Appsetting_model');
	}

	public function KB($page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['results'] = $this->Support_model->loadKBList($this->per_page, $offset);
		$data['total'] = $this->Support_model->countKBList();
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = ceil($data['total'] / $this->per_page);
		$data['base_url'] = base_url() . 'supports/KB';

		$this->load->view('support_kb_list', $data);
	}


	public function view_kb($id, $slug)
	{
		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['details'] = $this->Support_model->loadKbDetails($id, $slug);
		$this->load->view('support_kb_details', $data);
	}

	public function kb_category($catId, $slug, $page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['results'] = $this->Support_model->loadKBListByCategory($catId, $this->per_page, $offset);
		$data['category'] = $this->Support_model->getKBCategoryById($catId);
		$data['total'] = $this->Support_model->countKBListByCategory($catId);
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = ceil($data['total'] / $this->per_page);
		$data['base_url'] = base_url() . 'supports/kb_category/' . $catId . '/' . $slug;

		$this->load->view('support_kb_category', $data);
	}


	public function announcements($page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['archive'] = $this->Support_model->getAnnouncementArchive();
		$data['results'] = $this->Support_model->loadAnnouncements($this->per_page, $offset);
		$data['total'] = $this->Support_model->countAnnouncements();
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = ceil($data['total'] / $this->per_page);
		$data['base_url'] = base_url() . 'supports/announcements';

		$this->load->view('support_announcement_list', $data);
	}


	public function view_announcement($id, $slug)
	{
		$data['archive'] = $this->Support_model->getAnnouncementArchive();
		$data['details'] = $this->Support_model->loadAnnouncementDetail($id, $slug);
		$this->load->view('support_announcement_detail', $data);
	}

	public function announcements_archive($year, $month, $page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['archive'] = $this->Support_model->getAnnouncementArchive();
		$data['results'] = $this->Support_model->loadAnnouncementsByMonth($year, $month, $this->per_page, $offset);
		$data['total'] = $this->Support_model->countAnnouncementsByMonth($year, $month);
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = $data['total'] > 0 ? ceil($data['total'] / $this->per_page) : 1;
		$data['base_url'] = base_url() . 'supports/announcements_archive/' . $year . '/' . $month;
		$data['year'] = $year;
		$data['month'] = $month;
		$data['month_name'] = date('F Y', mktime(0, 0, 0, $month, 1, $year));

		$this->load->view('support_announcement_archive', $data);
	}

	public function contactus()
	{
		$app_settings = $this->Appsetting_model->getSettings();
		$captcha_site_key = !empty($app_settings['captcha_site_key']) ? $app_settings['captcha_site_key'] : '';
		$captcha_secret_key = !empty($app_settings['captcha_secret_key']) ? $app_settings['captcha_secret_key'] : '';

		if ($this->input->post()) {
			// Verify reCAPTCHA if configured
			if (!empty($captcha_site_key) && !empty($captcha_secret_key)) {
				$recaptcha_response = $this->input->post('g-recaptcha-response');
				if (empty($recaptcha_response)) {
					$this->session->set_flashdata('alert_error', 'Please complete the reCAPTCHA verification.');
					$data['captcha_site_key'] = $captcha_site_key;
					$this->load->view('support_contactus', $data);
					return;
				}

				$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
				$post_data = array(
					'secret' => $captcha_secret_key,
					'response' => $recaptcha_response,
					'remoteip' => $this->input->ip_address()
				);

				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($post_data)
					)
				);

				$context = stream_context_create($options);
				$result = file_get_contents($verify_url, false, $context);
				$recaptcha_result = json_decode($result, true);

				if (!$recaptcha_result['success']) {
					$this->session->set_flashdata('alert_error', 'reCAPTCHA verification failed. Please try again.');
					$data['captcha_site_key'] = $captcha_site_key;
					$this->load->view('support_contactus', $data);
					return;
				}
			}

			// Validate form inputs
			$name = trim($this->input->post('name'));
			$email = trim($this->input->post('email'));
			$subject = trim($this->input->post('subject'));
			$message = trim($this->input->post('message'));

			if (empty($name) || empty($email) || empty($subject) || empty($message)) {
				$this->session->set_flashdata('alert_error', 'Please fill in all required fields.');
				$data['captcha_site_key'] = $captcha_site_key;
				$this->load->view('support_contactus', $data);
				return;
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->session->set_flashdata('alert_error', 'Please enter a valid email address.');
				$data['captcha_site_key'] = $captcha_site_key;
				$this->load->view('support_contactus', $data);
				return;
			}

			// Get admin emails
			$admin_emails = $this->Support_model->getAdminEmails();

			if (empty($admin_emails)) {
				$this->session->set_flashdata('alert_error', 'Unable to send message. Please try again later.');
				$data['captcha_site_key'] = $captcha_site_key;
				$this->load->view('support_contactus', $data);
				return;
			}

			// Send email to all admins
			$appSettings = getAppSettings();
			$emailSubject = "Contact Form: " . htmlspecialchars($subject);

			$emailBody = '<h2>New Contact Form Submission</h2>';
			$emailBody .= '<table style="width:100%; border-collapse:collapse; margin-top:20px;">';
			$emailBody .= '<tr><td style="padding:10px; border:1px solid #ddd; background:#f5f5f5; width:150px;"><strong>Name:</strong></td>';
			$emailBody .= '<td style="padding:10px; border:1px solid #ddd;">' . htmlspecialchars($name) . '</td></tr>';
			$emailBody .= '<tr><td style="padding:10px; border:1px solid #ddd; background:#f5f5f5;"><strong>Email:</strong></td>';
			$emailBody .= '<td style="padding:10px; border:1px solid #ddd;"><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></td></tr>';
			$emailBody .= '<tr><td style="padding:10px; border:1px solid #ddd; background:#f5f5f5;"><strong>Subject:</strong></td>';
			$emailBody .= '<td style="padding:10px; border:1px solid #ddd;">' . htmlspecialchars($subject) . '</td></tr>';
			$emailBody .= '<tr><td style="padding:10px; border:1px solid #ddd; background:#f5f5f5; vertical-align:top;"><strong>Message:</strong></td>';
			$emailBody .= '<td style="padding:10px; border:1px solid #ddd;">' . nl2br(htmlspecialchars($message)) . '</td></tr>';
			$emailBody .= '<tr><td style="padding:10px; border:1px solid #ddd; background:#f5f5f5;"><strong>Submitted:</strong></td>';
			$emailBody .= '<td style="padding:10px; border:1px solid #ddd;">' . date('F d, Y h:i A') . '</td></tr>';
			$emailBody .= '<tr><td style="padding:10px; border:1px solid #ddd; background:#f5f5f5;"><strong>IP Address:</strong></td>';
			$emailBody .= '<td style="padding:10px; border:1px solid #ddd;">' . $this->input->ip_address() . '</td></tr>';
			$emailBody .= '</table>';
			$emailBody .= '<br><p style="color:#666; font-size:12px;">This message was sent from the contact form on ' . $appSettings->company_name . '</p>';

			$emailSent = false;
			foreach ($admin_emails as $adminEmail) {
				if (sendHtmlEmail($adminEmail, $emailSubject, $emailBody)) {
					$emailSent = true;
				}
			}

			if ($emailSent) {
				$this->session->set_flashdata('alert_success', 'Thank you for contacting us. We will get back to you shortly.');
				redirect('supports/contactus');
				return;
			} else {
				$this->session->set_flashdata('alert_error', 'Failed to send message. Please try again later.');
			}
		}

		$data['captcha_site_key'] = $captcha_site_key;
		$this->load->view('support_contactus', $data);
	}

}
