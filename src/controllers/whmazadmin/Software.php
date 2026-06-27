<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Software (admin)
 * -------------------------------------------------------------------------
 * Upload and manage the installable WHMAZ software ZIP. One build serves all
 * three plans, so a release is plan-agnostic — the newest flagged release is
 * what every licensed customer downloads. Files are stored privately in
 * uploadedfiles/software/ and served only via the license-gated endpoints.
 *
 *   GET  whmazadmin/software                 list + upload form
 *   POST whmazadmin/software/upload          upload a new release ZIP
 *   GET  whmazadmin/software/set_current/{id}
 *   GET  whmazadmin/software/delete_records/{id}
 *   GET  whmazadmin/software/download/{id}   admin download
 *
 * @see src/models/Software_model.php
 */
class Software extends WHMAZADMIN_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Software_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['releases'] = $this->Software_model->getReleases();
		$data['current']  = $this->Software_model->getCurrentRelease();
		$this->load->view('whmazadmin/software_manage', $data);
	}

	public function upload()
	{
		$this->form_validation->set_rules('version', 'Version', 'required|trim');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', 'Version is required.');
			redirect('/whmazadmin/software', 'refresh');
			return;
		}

		if (empty($_FILES['software_zip']['name']) || $_FILES['software_zip']['size'] <= 0) {
			$this->session->set_flashdata('error', 'Please choose a ZIP file to upload.');
			redirect('/whmazadmin/software', 'refresh');
			return;
		}

		// Defence in depth: only accept a .zip extension.
		$originalName = $_FILES['software_zip']['name'];
		$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
		if ($ext !== 'zip') {
			$this->session->set_flashdata('error', 'Only .zip files are allowed.');
			redirect('/whmazadmin/software', 'refresh');
			return;
		}

		$storedName = 'whmaz_' . bin2hex(random_bytes(16)) . '.zip';
		$config = array(
			'upload_path'   => $this->Software_model->storageDir(),
			'allowed_types' => 'zip',
			'max_size'      => 0, // KB; 0 = no CI limit (still bound by php.ini)
			'overwrite'     => false,
			'file_name'     => $storedName,
		);

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		if (!$this->upload->do_upload('software_zip')) {
			$this->session->set_flashdata('error', 'Upload failed: ' . $this->upload->display_errors('', ''));
			redirect('/whmazadmin/software', 'refresh');
			return;
		}

		$uploadData = $this->upload->data();

		$makeCurrent = $this->input->post('is_current') !== null; // checkbox
		$id = $this->Software_model->saveRelease(array(
			'version'       => trim($this->input->post('version')),
			'file_name'     => $uploadData['file_name'],
			'original_name' => $originalName,
			'file_size'     => (int) $uploadData['file_size'] * 1024, // CI reports KB
			'changelog'     => $this->input->post('changelog'),
			'uploaded_by'   => getAdminId(),
			'uploaded_on'   => date('Y-m-d H:i:s'),
		), $makeCurrent);

		$this->session->set_flashdata('success', $id > 0 ? 'Release uploaded successfully.' : 'Could not save the release.');
		redirect('/whmazadmin/software', 'refresh');
	}

	public function set_current($id)
	{
		$this->Software_model->setCurrent($id);
		$this->session->set_flashdata('success', 'Current release updated.');
		redirect('/whmazadmin/software', 'refresh');
	}

	public function delete_records($id)
	{
		$this->Software_model->softDelete($id);
		$this->session->set_flashdata('success', 'Release removed.');
		redirect('/whmazadmin/software', 'refresh');
	}

	public function download($id)
	{
		$release = $this->Software_model->getRelease($id);
		$path = $this->Software_model->filePath($release);
		if (empty($path)) {
			show_404();
			return;
		}
		stream_file_download($path, 'whmaz-' . $release['version'] . '.zip');
	}
}
