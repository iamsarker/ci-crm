<?php

class Common_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

	public function generate_dropdown($table, $id, $field, $field2=null, $field3=null) {
		$data[''] = '-- Select One --';
		if( !empty($field2) && !empty($field3) ){
			$this->db->select("$id, $field, $field2, $field3");
		} else if( !empty($field2) && empty($field3) ){
			$this->db->select("$id, $field, $field2");
		} else {
			$this->db->select("$id, $field");
		}

		$this->db->from($table);
		$this->db->order_by($id, 'DESC');
		$this->db->where("status", 1);
		$query = $this->db->get();

		foreach ($query->result_array() AS $rows) {
			$data[$rows[$id]] = $rows[$field] . (!empty( $field2 ) ? ' - '.$rows[$field2] : '') . (!empty( $field3 ) ? ' - '.$rows[$field3] : '');
		}

		return $data;
	}

    public function save($table, $data) {
        if ($this->db->insert($table, $data)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function update($table, $data, $id) {
        $this->db->where('id', $id);
        if ($this->db->update($table, $data)) {
            return true;
        } else {
            return false;
        }
    }

	public function get_sys_config($cnf_group) {
		$this->db->select('id, cnf_key, cnf_val, cnf_group');
		$this->db->from("sys_cnf");
		$this->db->where("cnf_group", $cnf_group);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$res = $query->result();

			$finalData = array();
			foreach($res as $row){
				$keyName = $row->cnf_key;
				$finalData[$keyName] = $row;
				unset($finalData[$keyName]->cnf_key);
				unset($finalData[$keyName]->cnf_group);
			}
			return $finalData;

		} else {
			return array();
		}
	}

    public function get_data($table) {
        $this->db->select('*');
        $this->db->from($table);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

	public function get_data_by_id($table, $id) {
		$this->db->select("*");
		$this->db->from($table);
		$this->db->where(array(
			'status'=>'1',
			'id'=> $id
		));
		$query = $this->db->get();

		if ($query) {
			$res = $query->result();
			return $res[0];
		} else {
			return array();
		}
	}

	public function get_data_by_field($table, $field, $val) {
		$this->db->select("*");
		$this->db->from($table);
		$this->db->where(array(
			'status'=>'1',
			$field => $val
		));
		$query = $this->db->get();

		if ($query) {
			$res = $query->result();
			return $res[0];
		} else {
			return array();
		}
	}

	public function getDomainPrices($currency_id, $reg_period, $extension) {
		$sql = "SELECT dp.id, de.extension, dp.price, dp.transfer, dp.renewal 
			FROM dom_pricing dp
			JOIN dom_extensions de on dp.dom_extension_id=de.id
			WHERE dp.currency_id=? and dp.reg_period=? and de.extension=? and dp.status=1 and de.status=1;";

		$data = $this->db->query($sql, array($currency_id, $reg_period, $extension) )->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function getHostingPrices($currency_id, $product_service_id, $billing_cycle_id) {
		$sql = "SELECT * FROM product_service_pricing WHERE currency_id=? and product_service_id=? and billing_cycle_id=? and status=1 ";
		$data = $this->db->query($sql, array($currency_id, $product_service_id, $billing_cycle_id))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	public function getServerInfoByOrderServiceId($orderServiceId, $companyId) {
		$sql = "SELECT s.* 
			FROM servers s
			JOIN product_services ps on s.id=ps.server_id
			JOIN product_service_pricing psp on psp.product_service_id=ps.id
			JOIN order_services os on psp.id=os.product_service_pricing_id
			WHERE os.id=$orderServiceId and os.company_id=$companyId";
		$data = $this->db->query($sql)->result_array();

		if ($data) {
			return $data[0];
		} else {
			return array();
		}
	}
    
    public function validateUserData($table, $rowName, $rowVal) {
        $this->db->select('count(*) as cnt');
        $this->db->from($table);
        $this->db->where(array(
			$rowName=>$rowVal,
			'status'=>'1',
			'company_id'=> getCompanyId()
		));
        $data = $this->db->get();
        if ($data) {
            $res = $data->result();
            return $res[0]->cnt;
        } else {
            return 0;
        }
    }

    public function upload_files($path, $title, $files) {
        $config = array(
            'upload_path' => $path,
            'allowed_types' => 'gif|jpg|jpeg|png|pdf|txt',
            'overwrite' => 1,
        );

        $this->load->library('upload', $config);

        $totalfilename = [];
        foreach ($files['name'] as $key => $image) {
            $_FILES['images[]']['name'] = $files['name'][$key];
            $_FILES['images[]']['type'] = $files['type'][$key];
            $_FILES['images[]']['tmp_name'] = $files['tmp_name'][$key];
            $_FILES['images[]']['error'] = $files['error'][$key];
            $_FILES['images[]']['size'] = $files['size'][$key];

            $fileName = $title . '_' . str_replace(',', '_', str_replace(' ', '_', $image));

            $totalfilename[] = $fileName;
            $config['file_name'] = $fileName;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('images[]')) {
                $this->upload->data();
            } else {
				//print_r($this->upload->display_errors());
                return false;
            }
        }
        return $totalfilename;
    }

}

?>
