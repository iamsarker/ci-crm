<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * License
 * -------------------------------------------------------------------------
 * Public phone-home endpoint for self-hosted WHMAZ installs. The installed
 * software calls this periodically with its license key (and domain) to learn
 * whether it may run. This is what gives "soft" suspension/termination teeth:
 * the CRM only flips order_licenses.status; enforcement happens here.
 *
 *   POST|GET  license/verify   license_key[, domain]
 *
 * Response (JSON):
 *   { valid: bool, status: 'active|suspended|terminated|expired|pending|invalid',
 *     plan_key, expires, features: {..}, message }
 *
 * Unauthenticated (the key is the credential) and CSRF-excluded — see
 * config.php csrf_exclude_uris.
 *
 * @see src/models/Orderlicense_model.php (validateLicense)
 */
class License extends WHMAZ_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Orderlicense_model');
		$this->load->model('Software_model');
	}

	public function verify()
	{
		$result = $this->_validateFromRequest();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

	/**
	 * Latest release info for an install's updater (JSON). Reports the current
	 * version and whether the supplied license may download it.
	 *
	 *   POST|GET license/latest   license_key[, domain]
	 */
	public function latest()
	{
		$check = $this->_validateFromRequest();
		$release = $this->Software_model->getCurrentRelease();

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'valid'        => $check['valid'],
			'status'       => $check['status'],
			'version'      => !empty($release['version']) ? $release['version'] : null,
			'file_size'    => !empty($release['file_size']) ? (int) $release['file_size'] : null,
			'changelog'    => !empty($release['changelog']) ? $release['changelog'] : null,
			'download_url' => $check['valid'] ? base_url('license/download') : null,
			'message'      => $check['message'],
		)));
	}

	/**
	 * Key-authenticated download for a self-hosted install's updater. Streams the
	 * current release only if the license validates as active.
	 *
	 *   POST|GET license/download   license_key[, domain]
	 */
	public function download()
	{
		$check = $this->_validateFromRequest();

		if (empty($check['valid'])) {
			$this->output
				->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode($check));
			return;
		}

		$release = $this->Software_model->getCurrentRelease();
		$path = $this->Software_model->filePath($release);
		if (empty($path)) {
			$this->output
				->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(array('valid' => true, 'status' => 'no_release', 'message' => 'No release available.')));
			return;
		}

		stream_file_download($path, 'whmaz-' . $release['version'] . '.zip');
	}

	/** Validate the license from the request (POST first, GET fallback). */
	private function _validateFromRequest()
	{
		$key    = $this->input->post('license_key') ?: $this->input->get('license_key');
		$domain = $this->input->post('domain') ?: $this->input->get('domain');

		return $this->Orderlicense_model->validateLicense(
			(string) $key,
			(string) $domain,
			(string) $this->input->ip_address()
		);
	}
}
