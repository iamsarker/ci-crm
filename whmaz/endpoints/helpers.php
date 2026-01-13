<?php
	function getRequest($url){
		$ch = curl_init();

		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
		);

		// Return Page contents.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		//grab URL and pass it to the variable.
		curl_setopt($ch, CURLOPT_URL, $url);

		$result = curl_exec($ch);

		return $result;
	}

	function generateNumberNo($no_type, $conn){
		$sql="SELECT id, last_no FROM gen_numbers WHERE no_type = '$no_type'";

		$last_no = 0;

		if ($result=mysqli_query($conn, $sql)) {
			// Fetch row
			while($row=mysqli_fetch_assoc($result)){
				$last_no = $row['last_no'];
				break;
			}
			// Free result set
			mysqli_free_result($result);
		}

		$last_no = $last_no + 1;

		$updateSql="UPDATE gen_numbers SET last_no=$last_no WHERE no_type = '$no_type'";
		if ($conn->query($updateSql) === TRUE) {
			echo "Invoice number created successfully\n";
		}

		return $last_no;
	}

	function saveInvoiceTable($data, $conn){
		$logSql = "INSERT INTO invoices (invoice_uuid, company_id, order_id, currency_id, currency_code, invoice_no, sub_total, tax, vat, total, due_date, order_date, pay_status, inserted_on, inserted_by) VALUES ( ";

		$logSql .= "'".$data['invoice_uuid']."', ";
		$logSql .= "'".$data['company_id']."', ";
		$logSql .= "'".$data['order_id']."', ";
		$logSql .= "'".$data['currency_id']."', ";
		$logSql .= "'".$data['currency_code']."', ";
		$logSql .= "'".$data['invoice_no']."', ";
		$logSql .= "'".$data['sub_total']."', ";
		$logSql .= "'".$data['tax']."', ";
		$logSql .= "'".$data['vat']."', ";
		$logSql .= "'".$data['total']."', ";
		$logSql .= "'".$data['due_date']."', ";
		$logSql .= "'".$data['order_date']."', ";
		$logSql .= "'".$data['pay_status']."', ";

		$logSql .= " now(), 1 )";

		$invoiceId = 0;

		if (mysqli_query($conn, $logSql)) {
			$invoiceId = mysqli_insert_id($conn);
		} else {
			print_r(mysqli_error($conn));
		}
		return $invoiceId;
	}

	function saveInvoiceItemTable($data, $conn){
		$logSql = "INSERT INTO invoice_items (invoice_id, item, item_desc, item_type, note, sub_total, tax, vat, total, inserted_on, inserted_by) VALUES ( ";

		$logSql .= "'".$data['invoice_id']."', ";
		$logSql .= "'".$data['item']."', ";
		$logSql .= "'".$data['item_desc']."', ";
		$logSql .= "'".$data['item_type']."', ";
		$logSql .= "'".$data['note']."', ";
		$logSql .= "'".$data['sub_total']."', ";
		$logSql .= "'".$data['tax']."', ";
		$logSql .= "'".$data['vat']."', ";
		$logSql .= "'".$data['total']."', ";

		$logSql .= " now(), 1 )";

		$lastId = 0;
		if (mysqli_query($conn, $logSql)) {
			$lastId = mysqli_insert_id($conn);
		} else {
			print_r(mysqli_error($conn));
		}

		return $lastId;
	}

	function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
?>
