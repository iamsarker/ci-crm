<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Softwareproduct (admin)
 * -------------------------------------------------------------------------
 * Manage the software product catalog (`plans` rows). A product behaves like a
 * hosting package: it has details, an entitlement feature map, per-currency x
 * per-billing-cycle pricing (`software_pricing`), and an optional linked
 * download release. Customers browse and buy these through the cart
 * (invoice_items.item_type = 3 = license/software).
 *
 *   GET  whmazadmin/softwareproduct                 list
 *   GET/POST whmazadmin/softwareproduct/manage/{id} create/edit
 *   GET  whmazadmin/softwareproduct/delete_records/{id}
 *   GET  whmazadmin/softwareproduct/toggle_active/{id}
 *
 * @see src/models/Plan_model.php
 * @see plans_subscription_schema.sql
 */
class Softwareproduct extends WHMAZADMIN_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Plan_model');
		$this->load->model('Software_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$products = $this->Plan_model->getAllForList();

		// Attach a compact price summary + currency/cycle counts for the list.
		foreach ($products as &$p) {
			$p['pricing_count'] = 0;
			$matrix = $this->Plan_model->getPricingMatrix($p['id']);
			foreach ($matrix as $cycles) {
				$p['pricing_count'] += count($cycles);
			}
		}
		unset($p);

		$data['products'] = $products;
		$this->load->view('whmazadmin/softwareproduct_list', $data);
	}

	public function manage($id_val = null)
	{
		if ($this->input->post()) {
			$this->form_validation->set_rules('name', 'Product Name', 'required|trim');
			$this->form_validation->set_message('name', 'Product name is required');

			if ($this->form_validation->run() == true) {

				$id = (int) safe_decode($this->input->post('id'));

				// Slug: use provided key or derive from name; ensure uniqueness.
				$planKey = trim($this->input->post('plan_key'));
				if ($planKey === '') {
					$planKey = url_title($this->input->post('name'), 'dash', true);
				}
				$planKey = strtolower(preg_replace('/[^a-zA-Z0-9\-_]/', '', $planKey));
				if ($planKey === '') {
					$planKey = 'product-' . time();
				}
				if ($this->Plan_model->keyExists($planKey, $id)) {
					$planKey .= '-' . substr(md5(uniqid('', true)), 0, 4);
				}

				$releaseId = (int) $this->input->post('current_release_id');

				$familyGroup = trim($this->input->post('family_group'));

				$form_data = array(
					'id'                 => $id,
					'plan_key'           => $planKey,
					'family_group'       => $familyGroup !== '' ? $familyGroup : null,
					'name'               => $this->input->post('name'),
					'tagline'            => $this->input->post('tagline'),
					'description'        => $this->input->post('description'),
					'current_release_id' => $releaseId > 0 ? $releaseId : null,
					'is_popular'         => $this->input->post('is_popular') ? 1 : 0,
					'sort_order'         => (int) $this->input->post('sort_order'),
					'is_active'          => $this->input->post('is_active') ? 1 : 0,
				);

				$result = $this->Plan_model->saveProduct($form_data);

				if (!empty($result['success'])) {
					$productId = (int) $result['id'];

					// Pricing matrix (currency x cycle).
					$pricingData = $this->input->post('pricing');
					if (!empty($pricingData) && is_array($pricingData)) {
						$this->Plan_model->savePricingMatrix($productId, $pricingData);
					}

					// Features: parallel feature_key[] / feature_value[] arrays.
					$keys   = (array) $this->input->post('feature_key');
					$values = (array) $this->input->post('feature_value');
					$features = array();
					foreach ($keys as $i => $k) {
						$k = trim($k);
						if ($k === '') {
							continue;
						}
						$features[$k] = isset($values[$i]) ? trim($values[$i]) : '';
					}
					$this->Plan_model->saveFeatures($productId, $features);

					$this->session->set_flashdata('admin_success', 'Software product has been saved successfully.');
					redirect('whmazadmin/softwareproduct/index');
					return;
				}

				$this->session->set_flashdata('admin_error', 'Something went wrong. Try again.');
			}
		}

		$data['detail'] = array();
		$productId = null;
		if (!empty($id_val)) {
			$productId = (int) safe_decode($id_val);
			$data['detail'] = $this->Plan_model->getDetail($productId);
		}

		$data['billing_cycles'] = $this->Plan_model->getBillingCycles();
		$data['currencies']     = $this->Plan_model->getCurrencies();
		$data['families']       = $this->Plan_model->getAllFamilies();
		$data['pricing_matrix'] = $productId ? $this->Plan_model->getPricingMatrix($productId) : array();
		$data['features']       = $productId ? $this->Plan_model->getStoredFeatures($productId) : array();
		$data['releases']       = $this->Software_model->getReleases($productId);

		$this->load->view('whmazadmin/softwareproduct_manage', $data);
	}

	public function toggle_active($id_val = null)
	{
		$id = (int) safe_decode($id_val);
		$new = $this->Plan_model->toggleActive($id);
		if ($this->input->is_ajax_request()) {
			header('Content-Type: application/json');
			echo json_encode(array('success' => $new !== null, 'is_active' => $new));
			return;
		}
		$this->session->set_flashdata('admin_success', 'Product status updated.');
		redirect('whmazadmin/softwareproduct/index');
	}

	public function delete_records($id_val = null)
	{
		$id = (int) safe_decode($id_val);
		$ok = $this->Plan_model->deleteProduct($id);
		if ($ok) {
			$this->session->set_flashdata('admin_success', 'Software product has been deleted.');
		} else {
			$this->session->set_flashdata('admin_error', 'Cannot delete: this product has active customer subscriptions.');
		}
		redirect('whmazadmin/softwareproduct/index');
	}
}
