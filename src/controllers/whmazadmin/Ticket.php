<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket extends WHMAZADMIN_Controller {

	var $img_path;
	function __construct(){
		parent::__construct();
		$this->load->model('Support_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/tickets/');
	}

	public function index()
	{
		$data['summary'] = $this->Support_model->ticketSummary(-1)[0];
		$this->load->view('whmazadmin/ticket_list', $data);
	}

	public function add(){

            $this->form_validation->set_rules('title', 'Subject', 'required|trim');
            $this->form_validation->set_message('title', 'Subject is required!');

            $this->form_validation->set_rules('ticket_dept_id', 'Dept', 'required|trim');
            $this->form_validation->set_message('ticket_dept_id', 'Dept is required');

            $this->form_validation->set_rules('priority', 'Priority', 'required|trim');
            $this->form_validation->set_message('priority', 'Priority is required');

            $this->form_validation->set_rules('message', 'Message', 'required|trim');
            $this->form_validation->set_message('message', 'Message is required');

            if ($this->form_validation->run() == true){
                    if($_FILES['attachment']['size'] > 0){
                            $image = $this->Common_model->upload_files($this->img_path, gen_uuid(), $_FILES['attachment']);
	                            if(is_array($image)){
                                $image_name = implode(",",$image);
                            } else {
                                $image_name = '';
                            }
                    }

                    $form_data = array(
                            'company_id'	=> $this->input->post('company_id'),
                            'title' 		=> $this->input->post('title'),
                            'ticket_dept_id'=> $this->input->post('ticket_dept_id'),
                            'priority'      => $this->input->post('priority'),
                            'message'      	=> $this->input->post('message'),
                            'attachment'    => $image_name,
                            'inserted_on'   	=> getDateTime(),
                            'inserted_by'   => getAdminId(),
                            'status'       	=> 1
                    );

                    if($this->Common_model->save('tickets', $form_data)){
                            $this->session->set_flashdata('alert_success', 'Support ticket has been placed successfully.');
                            redirect("tickets/index");
                    }else {
                            $this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
                    }
            }

            $data['results'] = $this->Common_model->generate_dropdown('ticket_depts','id','name');
            $data['subview'] = 'fileupload/index';
            $this->load->view('whmazadmin/newticket', $data);
	}

	public function get_ticket_attachment_row($sub_registry_form_inc,$registryNo){
            $data['registryNo'] = $registryNo;
            $data['sub_registry_form_inc'] = $sub_registry_form_inc +1;
            $data = $this->load->view('ticket_attachment_row',$data, TRUE);
            echo $data;
	}

	public function viewticket($tid)
	{
            $data['ticket'] = $this->Support_model->getTicketDetail($tid);
            $data['replies'] = $this->Support_model->viewTicketReplies($tid);
            $data['tid'] = $tid;
            $this->load->view('whmazadmin/ticket_manage', $data);
	}
        
        
	public function vtattachments($tid, $filename)
	{
            
	}
        
	public function likereplies($tid, $trid, $val)
	{
            $tdata = array();
            $tdata['updated_by'] = getCustomerId();
            $tdata['rating'] = floatval($val);
            $tdata['ticket_id'] = $tid;
            $res = $this->Common_model->update('ticket_replies', $tdata, $trid);
            
            if($res){
                $this->session->set_flashdata('alert_success', 'Like/dislike has been placed successfully on the reply.');
            } else {
                $this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
            }
            redirect("whmazadmin/ticket/viewticket/".$tid);
	}
        
        public function replyticket($ticket_id){

			$ticketDetail = $this->Support_model->getTicketDetail($ticket_id);

            $this->form_validation->set_rules('message', 'Message', 'required|trim');
            $this->form_validation->set_message('message', 'Message is required');

            if ($this->form_validation->run() == true){
					$image_name = "";
                    if($_FILES['attachment']['size'] > 0){
                            $image = $this->Common_model->upload_files($this->img_path, round(microtime(true) * 100), $_FILES['attachment']);
                            if(is_array($image)){
                                $image_name = implode(",",$image);
                            } else {
                                $image_name = '';
                            }
                    }

                    $form_data = array(
                            'company_id'	=> $ticketDetail["company_id"],
                            'ticket_id'		=> $ticket_id,
                            'message'      	=> $this->input->post('message'),
                            'attachment'    => $image_name,
                            'inserted_on'   => getDateTime(),
                            'inserted_by'   => getAdminId(),
                            'status'       	=> 1
                    );

                    if($this->Common_model->save('ticket_replies', $form_data)){
                        $tdata = array();
                        $tdata['updated_by'] = getAdminId();
                        $tdata['flag'] = 2;
                        $this->Common_model->update('tickets', $tdata, $ticket_id);
                                
                        $this->session->set_flashdata('alert_success', 'Ticket reply has been placed successfully.');
                        redirect("whmazadmin/ticket/viewticket/".$ticket_id);
                    } else {
                        $this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
                    }
            }

            redirect("whmazadmin/ticket/viewticket/".$ticket_id);
	}


	public function ssp_list_api()
	{
		$this->processRestCall();

		// Set proper JSON headers
		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "ticket_view", $bindings, $where);

			$data = $this->Support_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Support_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Support_model->countDataTableFilterRecords($where, $bindings) ),
				"data"            => $data
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			// Return error in DataTables format
			echo json_encode(array(
				"draw"            => 0,
				"recordsTotal"    => 0,
				"recordsFiltered" => 0,
				"data"            => array(),
				"error"           => $e->getMessage()
			));
			exit;
		}
	}


	public function recent_list_api()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode($this->Support_model->loadTicketList(-1, $rqData['limit']));
	}

}
