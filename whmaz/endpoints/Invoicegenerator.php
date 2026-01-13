<?php
	defined('BASEPATH') OR define('BASEPATH', '');
	defined('ENVIRONMENT') OR define('ENVIRONMENT', 'production');

	require_once('../../src/config/database.php');
	require_once('./helpers.php');
	$dbinfo = $db['default'];
	// print_r($dbinfo);

	$conn = mysqli_connect($dbinfo['hostname'],$dbinfo['username'],$dbinfo['password'],$dbinfo['database']);

	// Check connection
	if ($conn -> connect_errno) {
		echo "Failed to connect to MySQL: " . $conn -> connect_error;
		exit();
	}

	$logSql = "INSERT INTO cron_jobs (job_name, execute_dt) VALUES ('Invoicegenerator', now())";
	if ($conn->query($logSql) === TRUE) {
		echo "Invoicegenerator Log created successfully\n";
	}

	$domainSql="SELECT od.id oid, od.order_id, od.company_id, od.domain, od.first_pay_amount, od.recurring_amount, od.reg_date, od.exp_date, od.next_due_date, DATEDIFF(od.exp_date, CURDATE()) as date_diff, o.currency_id, o.currency_code
		FROM order_domains od 
		JOIN orders o on od.order_id=o.id 
		WHERE DATEDIFF(od.exp_date, CURDATE()) >= 0 and DATEDIFF(od.exp_date, CURDATE()) <= 15 and od.status=1";

	$hostingSql="SELECT os.id sid, os.order_id, os.company_id, os.description, os.hosting_domain, os.first_pay_amount, os.recurring_amount, os.reg_date, os.exp_date, os.next_due_date, DATEDIFF(os.exp_date, CURDATE()) as date_diff, o.currency_id, o.currency_code, ps.product_name
		FROM order_services os 
		JOIN orders o on os.order_id=o.id 
		JOIN product_services ps on os.product_service_id=ps.id 
		WHERE DATEDIFF(os.exp_date, CURDATE()) >= 0 and DATEDIFF(os.exp_date, CURDATE()) <= 15 and os.status=1";

	$domain_arr = array();
	$hosting_arr = array();
	$order_ids = array();

	if ($result=mysqli_query($conn, $domainSql)) {
		// Fetch row
		while($row=mysqli_fetch_assoc($result)){
			array_push($domain_arr, $row);
			$order_ids[$row['order_id']] = $row['order_id'];
		}
		// Free result set
		mysqli_free_result($result);
	}

	if ($result=mysqli_query($conn, $hostingSql)) {
		// Fetch row
		while($row=mysqli_fetch_assoc($result)){
			array_push($hosting_arr, $row);
			$order_ids[$row['order_id']] = $row['order_id'];
		}
		// Free result set
		mysqli_free_result($result);
	}

	mysqli_close($conn);

	foreach ($order_ids as $oid) {

		$conn = mysqli_connect($dbinfo['hostname'], $dbinfo['username'], $dbinfo['password'], $dbinfo['database']);

		/* ------------- Check the invoice already generated or not ---------------------*/
		$invoiceCheckSql = "SELECT iv.id, DATEDIFF(iv.due_date, CURDATE()) as date_diff
		FROM invoices iv 
		WHERE iv.order_id = $oid and DATEDIFF(iv.due_date, CURDATE()) >= -14 and DATEDIFF(iv.due_date, CURDATE()) <= 15 and iv.status=1";

		$ivNo = -1;
		if ($result=mysqli_query($conn, $invoiceCheckSql)) {
			while($row=mysqli_fetch_assoc($result)){
				$ivNo = $row['id'];
			}
			mysqli_free_result($result);
		}

		if( $ivNo > 0 ) { // invoice has already been generated
			echo "Invoice has already been generated for OrderId-".$oid."\n";
			continue;
		}

		/* ------------- Check the invoice already generated or not ---------------------*/

		$currency_id = 0;
		$currency_code = "";
		$company_id = 0;
		$sub_total = 0;
		$last_exp_date = "";
		$order_date = "";

		$invoiceHosting = array();

		foreach( $hosting_arr as $hosting ) {
			if( $oid == $hosting['order_id'] ) {
				$currency_id = $hosting['currency_id'];
				$currency_code = $hosting['currency_code'];
				$company_id = $hosting['company_id'];
				$last_exp_date = $hosting['exp_date'];
				$order_date = $hosting['reg_date'];

				$invoiceHosting['item'] = 'Hosting package';
				$invoiceHosting['item_type'] = 1;
				$invoiceHosting['item_desc'] = 'Renewal of hosting package -> '.$hosting['product_name'];
				$invoiceHosting['sub_total'] = $hosting['recurring_amount'];
				$invoiceHosting['note'] = '';
				$invoiceHosting['tax'] = 0;
				$invoiceHosting['vat'] = 0;
				$invoiceHosting['total'] = $hosting['recurring_amount'];

				$sub_total += $hosting['recurring_amount'];
				break;
			}
		}

		$invoiceDomain = array();
		foreach( $domain_arr as $domain ){
			if( $oid == $domain['order_id'] && ($last_exp_date == "" || $last_exp_date==$domain['exp_date']) ) {
				$currency_id = $domain['currency_id'];
				$currency_code = $domain['currency_code'];
				$company_id = $domain['company_id'];
				$last_exp_date = $domain['exp_date'];
				$order_date = $domain['reg_date'];

				$invoiceDomain['item'] = 'Domain renewal';
				$invoiceDomain['item_type'] = 1;
				$invoiceDomain['item_desc'] = 'Renewal of Domain -> '.$domain['domain'];
				$invoiceDomain['sub_total'] = $domain['recurring_amount'];
				$invoiceDomain['note'] = '';
				$invoiceDomain['tax'] = 0;
				$invoiceDomain['vat'] = 0;
				$invoiceDomain['total'] = $domain['recurring_amount'];

				$sub_total += $domain['recurring_amount'];
				break;
			}
		}

		$invoice['invoice_uuid'] = gen_uuid();
		$invoice['company_id'] = $company_id;
		$invoice['order_id'] = $oid;
		$invoice['currency_id'] = $currency_id;
		$invoice['currency_code'] = $currency_code;
		$invoice['invoice_no'] = generateNumberNo('INVOICE', $conn);
		$invoice['sub_total'] = $sub_total;
		$invoice['tax'] = 0.0;
		$invoice['vat'] = 0.0;
		$invoice['total'] = $sub_total;
		$invoice['order_date'] = $order_date;
		$invoice['due_date'] = $last_exp_date;
		$invoice['status'] = 1;
		$invoice['pay_status'] = 'DUE';
		$invoice['need_api_call'] = 1;
		$invoice['inserted_on'] = date('Y-m-d H:i:s');
		$invoice['inserted_by'] = 1;

		$invoiceId = saveInvoiceTable($invoice, $conn);

		if( !empty($invoiceDomain) ){
			$invoiceDomain['invoice_id'] = $invoiceId;
			saveInvoiceItemTable($invoiceDomain, $conn);
		}

		if( !empty($invoiceHosting) ){
			$invoiceHosting['invoice_id'] = $invoiceId;
			saveInvoiceItemTable($invoiceHosting, $conn);
		}

		mysqli_close($conn);
		sleep(2);
	}

?>
