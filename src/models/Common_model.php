<?php

class Common_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

	public function generate_dropdown($table, $id, $field, $field2=null, $field3=null) {
		// SECURITY NOTE: This method accepts table/field names as parameters
		// Ensure these are only called from trusted code paths, not user input
		// Whitelist validation would be needed if called with user input

		$data[''] = '-- Select One --';

		try {
			// Sanitize field names to prevent SQL injection
			$id = $this->db->protect_identifiers($id, false);
			$field = $this->db->protect_identifiers($field, false);
			if (!empty($field2)) {
				$field2 = $this->db->protect_identifiers($field2, false);
			}
			if (!empty($field3)) {
				$field3 = $this->db->protect_identifiers($field3, false);
			}

			if( !empty($field2) && !empty($field3) ){
				$this->db->select("$id, $field, $field2, $field3", false);
			} else if( !empty($field2) && empty($field3) ){
				$this->db->select("$id, $field, $field2", false);
			} else {
				$this->db->select("$id, $field", false);
			}

			$this->db->from($table);
			$this->db->order_by($id, 'DESC');
			$this->db->where("status", 1);
			$query = $this->db->get();

			foreach ($query->result_array() AS $rows) {
				// Remove protect_identifiers wrapping for array key access
				$idKey = str_replace('`', '', $id);
				$fieldKey = str_replace('`', '', $field);
				$field2Key = !empty($field2) ? str_replace('`', '', $field2) : null;
				$field3Key = !empty($field3) ? str_replace('`', '', $field3) : null;

				$data[$rows[$idKey]] = $rows[$fieldKey] .
					(!empty($field2Key) && isset($rows[$field2Key]) ? ' - '.$rows[$field2Key] : '') .
					(!empty($field3Key) && isset($rows[$field3Key]) ? ' - '.$rows[$field3Key] : '');
			}

			return $data;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('generate_dropdown - ' . $table, $this->db->last_query(), $e->getMessage());
			return $data; // Return empty dropdown on error
		}
	}

    public function save($table, $data) {
        try {
            if ($this->db->insert($table, $data)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            // SECURITY: Log database error
            ErrorHandler::log_database_error('save - INSERT into ' . $table, $this->db->last_query(), $e->getMessage());
            return false;
        }
    }
    
    public function update($table, $data, $id) {
        try {
            $this->db->where('id', $id);
            if ($this->db->update($table, $data)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            // SECURITY: Log database error
            ErrorHandler::log_database_error('update - UPDATE ' . $table, $this->db->last_query(), $e->getMessage());
            return false;
        }
    }

	public function get_sys_config($cnf_group) {
		try {
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
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('get_sys_config', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

    public function get_data($table) {
        try {
            $this->db->select('*');
            $this->db->from($table);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return $query->result();
            } else {
                return FALSE;
            }
        } catch (Exception $e) {
            // SECURITY: Log database error
            ErrorHandler::log_database_error('get_data - ' . $table, $this->db->last_query(), $e->getMessage());
            return FALSE;
        }
    }

	public function get_data_by_id($table, $id) {
		try {
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
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('get_data_by_id - ' . $table, $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	public function get_data_by_field($table, $field, $val) {
		try {
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
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('get_data_by_field - ' . $table, $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	public function getDomainPrices($currency_id, $reg_period, $extension) {
		try {
			$sql = "SELECT dp.id, de.extension, dp.price, dp.transfer, dp.renewal
				FROM dom_pricing dp
				JOIN dom_extensions de on dp.dom_extension_id=de.id
				WHERE dp.currency_id=? and dp.reg_period=? and de.extension=? and dp.status=1 and de.status=1;";

			$data = $this->db->query($sql, array($currency_id, $reg_period, $extension) )->result_array();
			return !empty($data) ? $data[0] : array();
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('getDomainPrices', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function getHostingPrices($currency_id, $product_service_id, $billing_cycle_id) {
		try {
			$sql = "SELECT * FROM product_service_pricing WHERE currency_id=? and product_service_id=? and billing_cycle_id=? and status=1 ";
			$data = $this->db->query($sql, array($currency_id, $product_service_id, $billing_cycle_id))->result_array();

			return !empty($data) ? $data[0] : array();
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('getHostingPrices', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	public function getServerInfoByOrderServiceId($orderServiceId, $companyId) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate inputs
		if (!is_numeric($orderServiceId) || !is_numeric($companyId) || $orderServiceId <= 0 || $companyId <= 0) {
			return array();
		}

		try {
			$sql = "SELECT s.*
				FROM servers s
				JOIN product_services ps on s.id=ps.server_id
				JOIN product_service_pricing psp on psp.product_service_id=ps.id
				JOIN order_services os on psp.id=os.product_service_pricing_id
				WHERE os.id=? and os.company_id=?";
			$data = $this->db->query($sql, array(intval($orderServiceId), intval($companyId)))->result_array();

			if ($data && count($data) > 0) {
				return $data[0];
			} else {
				return array();
			}
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('getServerInfoByOrderServiceId', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}
    
    public function validateUserData($table, $rowName, $rowVal) {
        try {
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
        } catch (Exception $e) {
            // SECURITY: Log database error
            ErrorHandler::log_database_error('validateUserData - ' . $table, $this->db->last_query(), $e->getMessage());
            return 0;
        }
    }

    public function upload_files($path, $title, $files) {
        // SECURITY ENHANCEMENT: Comprehensive file upload security

        // Security: Enforce maximum file size (5MB default)
        $max_file_size = 5120; // 5MB in KB

        // Security: Whitelist of allowed MIME types
        $allowed_mimes = array(
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain'
        );

        $config = array(
            'upload_path' => $path,
            'allowed_types' => 'gif|jpg|jpeg|png|pdf|txt',
            'max_size' => $max_file_size, // SECURITY: Enforce file size limit
            'overwrite' => 0, // SECURITY: Prevent overwriting existing files
            'encrypt_name' => TRUE, // SECURITY: Use encrypted random file names
            'remove_spaces' => TRUE
        );

        $this->load->library('upload', $config);

        $totalfilename = [];
        foreach ($files['name'] as $key => $image) {
            // SECURITY: Validate file exists and no upload errors
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue; // Skip files with errors
            }

            // SECURITY: Double check file size
            if ($files['size'][$key] > ($max_file_size * 1024)) {
                log_message('error', 'File upload rejected: Size exceeds limit - ' . $files['name'][$key]);
                continue;
            }

            // SECURITY: Verify MIME type using finfo (server-side validation)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $files['tmp_name'][$key]);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_mimes)) {
                log_message('error', 'File upload rejected: Invalid MIME type - ' . $mime_type . ' for file ' . $files['name'][$key]);
                continue;
            }

            // SECURITY: Get safe file extension
            $original_ext = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));
            $allowed_exts = array('gif', 'jpg', 'jpeg', 'png', 'pdf', 'txt');

            if (!in_array($original_ext, $allowed_exts)) {
                log_message('error', 'File upload rejected: Invalid extension - ' . $original_ext);
                continue;
            }

            // SECURITY: Generate cryptographically secure random filename
            $random_name = bin2hex(random_bytes(16));
            $fileName = $random_name . '.' . $original_ext;

            $_FILES['images[]']['name'] = $fileName;
            $_FILES['images[]']['type'] = $files['type'][$key];
            $_FILES['images[]']['tmp_name'] = $files['tmp_name'][$key];
            $_FILES['images[]']['error'] = $files['error'][$key];
            $_FILES['images[]']['size'] = $files['size'][$key];

            $config['file_name'] = $fileName;
            $this->upload->initialize($config);

            if ($this->upload->do_upload('images[]')) {
                $upload_data = $this->upload->data();

                // SECURITY: Additional post-upload validation
                // Verify the uploaded file still has correct MIME type
                $uploaded_file = $upload_data['full_path'];
                $finfo_check = finfo_open(FILEINFO_MIME_TYPE);
                $uploaded_mime = finfo_file($finfo_check, $uploaded_file);
                finfo_close($finfo_check);

                if (!in_array($uploaded_mime, $allowed_mimes)) {
                    // SECURITY: Delete the file if MIME type changed
                    @unlink($uploaded_file);
                    log_message('error', 'File upload rejected after upload: MIME type mismatch');
                    continue;
                }

                $totalfilename[] = $fileName;
            } else {
                log_message('error', 'File upload failed: ' . $this->upload->display_errors());
                // Continue to next file instead of returning false
                continue;
            }
        }

        // Return uploaded filenames or false if none succeeded
        return !empty($totalfilename) ? $totalfilename : false;
    }

    /**
     * Get product service type key by pricing ID
     * @param int $pricingId - product_service_pricing.id
     * @return string|null - key_name from product_service_types or null if not found
     */
    public function getProductServiceTypeKeyByPricingId($pricingId) {
        if (!is_numeric($pricingId) || $pricingId <= 0) {
            return "OTHER";
        }

        try {
            $sql = "SELECT pst.key_name
                    FROM product_service_pricing psp
                    JOIN product_services ps ON psp.product_service_id = ps.id
                    JOIN product_service_types pst ON ps.product_service_type_id = pst.id
                    WHERE psp.id = ? AND psp.status = 1";

            $result = $this->db->query($sql, array(intval($pricingId)))->row();

            return !empty($result) ? $result->key_name : "OTHER";
        } catch (Exception $e) {
            ErrorHandler::log_database_error('getProductServiceTypeKeyByPricingId', $this->db->last_query(), $e->getMessage());
            return "OTHER";
        }
    }

}

?>
