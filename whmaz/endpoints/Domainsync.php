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


	$sql="SELECT od.id, od.domain, od.domain_order_id, od.domain_cust_id, od.dom_register_id, dr.platform, dr.api_base_url, dr.auth_userid, dr.auth_apikey FROM order_domains od JOIN dom_registers dr on od.dom_register_id=dr.id WHERE od.status NOT IN (4,5,6)";
	$data_arr = array();

	if ($result=mysqli_query($conn, $sql)) {
		// Fetch row
		while($row=mysqli_fetch_assoc($result)){
			array_push($data_arr, $row);
		}
		// Free result set
		mysqli_free_result($result);
	}

	foreach( $data_arr as $record){

		if( strtoupper($record['platform']) == "STARGATE" ) { // resell.biz, resellerclub

			$domain_order_id = !empty($record['domain_order_id']) ? trim($record['domain_order_id']) : '';
			$domain_name = !empty($record['domain']) ? trim($record['domain']) : '';

			/*if( $domain_order_id == "" ){
				$url = $record['api_base_url'] . '/orderid.json?auth-userid=' . $record['auth_userid'] . '&api-key=' . $record['auth_apikey'] . '&domain-name='.$record['domain'];
				$domain_order_id = getRequest($url);

				if( !empty($domain_order_id) && strlen($domain_order_id) <= 20 ){
					$sql = "UPDATE order_domains SET domain_order_id=$domain_order_id where id=".$record['id'];
					if (mysqli_query($conn, $sql)) {
						echo "STARGATE -> ORDER_ID has been updated for ". $record['domain'];
					}
				}
			}*/

			$url = $record['api_base_url'] . '/details-by-name.json?auth-userid=' . $record['auth_userid'] . '&api-key=' . $record['auth_apikey'] . '&domain-name='.$domain_name . '&options=All';
			$details = getRequest($url);
			print_r($details);

			/*
			 {
  "domainname": "finboi.com",
  "premiumdnsenabled": "false",
  "recurring": "false",
  "tnc_required": "false",
  "domsecret": "b4u(dfxAuht",
  "serviceproviderid": "3",
  "productkey": "domcno",
  "orderSuspendedByParent": "false",
  "multilingualflag": "f",
  "isOrderSuspendedUponExpiry": "false",
  "isprivacyprotected": "false",
  "allowdeletion": "true",
  "productcategory": "domorder",
  "creationtime": "1692267530",
  "customerid": "12037768",
  "bulkwhoisoptout": "t",
  "endtime": "1755425930",
  "autoRenewAttemptDuration": "30",
  "orderid": "107940477",
  "moneybackperiod": "4",
  "orderstatus": [
    "transferlock"
  ],
  "classkey": "domcno",
  "gdpr": {
    "enabled": "false",
    "eligible": "false"
  },
  "premiumdnsallowed": "true",
  "autoRenewTermType": "LONG_TERM",
  "classname": "com.logicboxes.foundation.sfnb.order.domorder.DomCno",
  "privacyprotectedallowed": "true",
  "parentkey": "999999999_80588_572394",
  "isImmediateReseller": "true",
  "currentOrderTenure": "12"
}
			 * */


		} else if ( strtoupper($record['platform']) == "NAMECHEAP" ) {

			//ApiUser=apiexample&ApiKey=56b4c87ef4fd49cb96d915c0db68194&UserName=apiexample&Command=namecheap.domains.dns.getList&ClientIp=192.168.1.109&SLD=domain&TLD=com

			/*$url = $record['api_base_url'] . '?ApiUser=' . $record['auth_userid'] . '&ApiKey=' . $record['auth_apikey'] . '&UserName='.$record['auth_userid'] . '&Command=namecheap.domains.dns.getList&ClientIp=202.134.10.138&&SLD=erpboi&TLD=com';
			//echo $url;
			$response = getRequest($url);

			$parser = xml_parser_create();
			xml_parse_into_struct($parser, trim($response), $xml_values);
			xml_parser_free($parser);
			foreach ($xml_values as $data => $val) {
//				extract($data);
				print_r($data);
				print_r($val);
			}*/
		}

	}

	mysqli_close($conn);

?>
