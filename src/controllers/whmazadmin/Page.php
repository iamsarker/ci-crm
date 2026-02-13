<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends WHMAZADMIN_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Page_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index() {
		$data['results'] = array();
		$data['stats'] = $this->Page_model->getStats();
		$this->load->view('whmazadmin/page_list', $data);
	}

	public function manage($id_val = null) {
		if ($this->input->post()) {
			$this->form_validation->set_rules('page_title', 'Page Title', 'required|trim');
			$this->form_validation->set_rules('page_slug', 'Page Slug', 'required|trim|alpha_dash');
			$this->form_validation->set_rules('page_content', 'Page Content', 'required');

			if ($this->form_validation->run() == true) {
				$pageId = !empty($id_val) ? safe_decode($id_val) : 0;
				$isNew = (intval($pageId) <= 0);
				$slug = strtolower($this->input->post('page_slug'));

				// Check if slug already exists (only for new pages)
				if ($isNew && $this->Page_model->slugExists($slug, null)) {
					$this->session->set_flashdata('admin_error', 'This slug is already in use. Please choose a different one.');
				} else {
					$form_data = array(
						'id' => $pageId,
						'page_title' => $this->input->post('page_title'),
						'page_slug' => $slug,
						'page_content' => $this->input->post('page_content'),
						'meta_title' => $this->input->post('meta_title'),
						'meta_description' => $this->input->post('meta_description'),
						'meta_keywords' => $this->input->post('meta_keywords'),
						'is_published' => $this->input->post('is_published') ? 1 : 0,
						'sort_order' => intval($this->input->post('sort_order')),
						'status' => 1
					);

					if (!$isNew) {
						$oldEntity = $this->Page_model->getDetail($pageId);
						$form_data['updated_on'] = getDateTime();
						$form_data['updated_by'] = getAdminId();
						$form_data['inserted_on'] = $oldEntity['inserted_on'];
						$form_data['inserted_by'] = $oldEntity['inserted_by'];
					} else {
						$form_data['inserted_on'] = getDateTime();
						$form_data['inserted_by'] = getAdminId();
					}

					$result = $this->Page_model->saveData($form_data);

					if ($result['success']) {
						// Save history
						$changeType = $isNew ? 'created' : 'updated';
						$this->Page_model->saveHistory($result['id'], $form_data, $changeType);

						$this->session->set_flashdata('admin_success', 'Page has been saved successfully.');
						redirect("whmazadmin/page/index");
					} else {
						$this->session->set_flashdata('admin_error', 'Something went wrong. Please try again.');
					}
				}
			} else {
				$this->session->set_flashdata('admin_error', validation_errors());
			}
		}

		if (!empty($id_val)) {
			$data['detail'] = $this->Page_model->getDetail(safe_decode($id_val));
			$data['history'] = $this->Page_model->getHistory(safe_decode($id_val));
		} else {
			$data['detail'] = array();
			$data['history'] = array();
		}

		$this->load->view('whmazadmin/page_manage', $data);
	}

	public function history($id_val = null) {
		if (empty($id_val)) {
			redirect('whmazadmin/page/index');
		}

		$data['page'] = $this->Page_model->getDetail(safe_decode($id_val));
		$data['history'] = $this->Page_model->getHistory(safe_decode($id_val), 50);

		$this->load->view('whmazadmin/page_history', $data);
	}

	public function view_history($history_id = null) {
		if (empty($history_id)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid history ID'));
			return;
		}

		$history = $this->Page_model->getHistoryDetail(safe_decode($history_id));

		if (empty($history)) {
			echo json_encode(array('success' => false, 'message' => 'History not found'));
			return;
		}

		echo json_encode(array('success' => true, 'data' => $history));
	}

	public function restore_history($history_id = null) {
		if (empty($history_id)) {
			$this->session->set_flashdata('admin_error', 'Invalid history ID');
			redirect('whmazadmin/page/index');
		}

		$history = $this->Page_model->getHistoryDetail(safe_decode($history_id));

		if (empty($history)) {
			$this->session->set_flashdata('admin_error', 'History version not found');
			redirect('whmazadmin/page/index');
		}

		$page = $this->Page_model->getDetail($history['page_id']);

		if (empty($page)) {
			$this->session->set_flashdata('admin_error', 'Page not found');
			redirect('whmazadmin/page/index');
		}

		// Restore content from history
		$form_data = array(
			'id' => $page['id'],
			'page_title' => $history['page_title'],
			'page_content' => $history['page_content'],
			'meta_title' => $history['meta_title'],
			'meta_description' => $history['meta_description'],
			'updated_on' => getDateTime(),
			'updated_by' => getAdminId()
		);

		$result = $this->Page_model->saveData($form_data);

		if ($result['success']) {
			// Save restoration as new history entry
			$this->Page_model->saveHistory(
				$page['id'],
				$form_data,
				'restored',
				'Restored from version dated ' . $history['changed_at']
			);

			$this->session->set_flashdata('admin_success', 'Page has been restored to the selected version.');
		} else {
			$this->session->set_flashdata('admin_error', 'Failed to restore page.');
		}

		redirect('whmazadmin/page/manage/' . safe_encode($page['id']));
	}

	public function delete_records($id_val) {
		$entity = $this->Page_model->getDetail(safe_decode($id_val));

		if (empty($entity)) {
			$this->session->set_flashdata('admin_error', 'Page not found.');
			redirect('whmazadmin/page/index');
		}

		// Prevent deletion of system pages
		if (!empty($entity['is_system']) && $entity['is_system'] == 1) {
			$this->session->set_flashdata('admin_error', 'System pages cannot be deleted.');
			redirect('whmazadmin/page/index');
		}

		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Page_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Page has been deleted successfully.');

		redirect('whmazadmin/page/index');
	}

	public function ssp_list_api() {
		$this->processRestCall();

		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "pages", $bindings, $where);

			$data = $this->Page_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw" => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal" => intval($this->Page_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Page_model->countDataTableFilterRecords($where, $bindings)),
				"data" => $data,
				"stats" => $this->Page_model->getStats()
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			echo json_encode(array(
				"draw" => 0,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => array(),
				"error" => $e->getMessage()
			));
			exit;
		}
	}

	public function toggle_publish($id_val) {
		$this->processRestCall();

		header('Content-Type: application/json');

		$entity = $this->Page_model->getDetail(safe_decode($id_val));

		if (empty($entity)) {
			echo json_encode(array('success' => false, 'message' => 'Page not found'));
			return;
		}

		$entity['is_published'] = $entity['is_published'] == 1 ? 0 : 1;
		$entity['updated_on'] = getDateTime();
		$entity['updated_by'] = getAdminId();

		$result = $this->Page_model->saveData($entity);

		if ($result['success']) {
			echo json_encode(array(
				'success' => true,
				'is_published' => $entity['is_published'],
				'message' => $entity['is_published'] ? 'Page published' : 'Page unpublished'
			));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Failed to update'));
		}
	}
}
