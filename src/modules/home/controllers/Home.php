<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Home
 * -------------------------------------------------------------------------
 * Public landing page served at the site root (`/`). Set as
 * `default_controller` in src/config/routes.php so `my.whmaz.com/` renders a
 * real, crawlable page instead of bouncing straight to /auth/login — the old
 * empty-body `Refresh` redirect that external crawlers (e.g. Paddle's website
 * verifier) could not follow.
 */
class Home extends WHMAZ_Controller
{
	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->load->view('home_view');
	}
}
