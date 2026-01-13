<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends WHMAZ_Controller {

	var $img_path;
	function __construct(){
		parent::__construct();
		$this->load->model('Support_model');
		$this->load->model('Common_model');
		if( !$this->isLogin() ){
			redirect('/auth/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/tickets/');
	}

	public function index()
	{
		$data['summary'] = $this->Support_model->ticketSummary(getCompanyId())[0];
		$data['results'] = $this->Support_model->loadTicketList(getCompanyId(), -1);
		$this->load->view('tickets', $data);
	}

	public function newticket(){
            $user = $this->session->userdata("CUSTOMER");

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
							pr($image);
                            $image_name = implode(",",$image);
                    }

                    $form_data = array(
                            'company_id'	=> getCompanyId(),
                            'title' 		=> $this->input->post('title'),
                            'ticket_dept_id'=> $this->input->post('ticket_dept_id'),
                            'priority'      => $this->input->post('priority'),
                            'message'      	=> $this->input->post('message'),
                            'attachment'    => $image_name,
                            'inserted_on'   	=> date('Y-m-d H:i'),
                            'inserted_by'   => getCustomerId(),
                            'status'       	=> 1
                    );

                    if($this->Common_model->save('tickets', $form_data)){
                            $this->session->set_flashdata('alert', successAlert('Support ticket has been placed successfully.'));
                            redirect("tickets/index");
                    }else {
                            $this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));
                    }
            }

            $data['user'] = $user;
            $data['recent'] = $this->Support_model->loadTicketList(getCompanyId(), 3);
            $data['results'] = $this->Common_model->generate_dropdown('ticket_depts','id','name');
            $data['subview'] = 'fileupload/index';
            $this->load->view('newticket', $data);
	}

	public function get_ticket_attachment_row($sub_registry_form_inc, $registryNo){
            $data['registryNo'] = $registryNo;
            $data['sub_registry_form_inc'] = $sub_registry_form_inc +1;
            $data = $this->load->view('ticket_attachment_row',$data, TRUE);
            echo $data;
	}

	public function viewticket($tid)
	{
            $data['ticket'] = $this->Support_model->viewTicket($tid, getCompanyId());
            $data['replies'] = $this->Support_model->viewTicketReplies($tid);
            $data['tid'] = $tid;
            $this->load->view('viewticket', $data);
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
                $this->session->set_flashdata('alert', successAlert('Like/dislike has been placed successfully on the reply.'));
            } else {
                $this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));
            }
            redirect("tickets/viewticket/".$tid);
	}
        
        public function replyticket($ticket_id){
            
            $isValid = $this->Common_model->validateUserData("tickets", "id", $ticket_id);
            if( $isValid < 1 ){
                $this->session->set_flashdata('alert', errorAlert('You are trying operation over wrong data!!'));
                redirect("tickets/viewticket/".$ticket_id);
            }

            $this->form_validation->set_rules('message', 'Message', 'required|trim');
            $this->form_validation->set_message('message', 'Message is required');

            if ($this->form_validation->run() == true){
                    if($_FILES['attachment']['size'] > 0){
                            $image = $this->Common_model->upload_files($this->img_path, round(microtime(true) * 100), $_FILES['attachment']);
                            $image_name = implode(",",$image);
                    }

                    $form_data = array(
                            'company_id'	=> getCompanyId(),
                            'ticket_id'		=> $ticket_id,
                            'message'      	=> $this->input->post('message'),
                            'attachment'    => $image_name,
                            'inserted_on'   => date('Y-m-d H:i'),
                            'inserted_by'   => getCustomerId(),
                            'status'       	=> 1
                    );

                    if($this->Common_model->save('ticket_replies', $form_data)){
                        $tdata = array();
                        $tdata['updated_by'] = getCustomerId();
                        $tdata['flag'] = 3;
                        $this->Common_model->update('tickets', $tdata, $ticket_id);
                                
                        $this->session->set_flashdata('alert', successAlert('Ticket reply has been placed successfully.'));
                        redirect("tickets/viewticket/".$ticket_id);
                    } else {
                        $this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));
                    }
            }

            redirect("tickets/viewticket/".$ticket_id);
	}


	public function ticket_list_api()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode($this->Support_model->loadTicketList(getCompanyId(), $rqData['limit']));
	}

}
