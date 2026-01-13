<!DOCTYPE html>
<html>
<head>
	<title>WHMAZ - INVOICE PDF</title>
	<meta name="author" content="Tong Bari">
	<style>

		footer {
			position: fixed;
			bottom: 0cm;
			left: 0cm;
			right: 0cm;
			height: 1.2cm;

			/** Extra personal styles **/
			background-color: #0250a3;
			color: white;
			text-align: center;
			line-height: 0.9cm;
		}
	</style>
</head>
	<body>

		<?php if( $viewMode == "PDF" ){?>
		<footer>
			System generated invoice. Seal & signature is not required. Generate Time: <?php echo getDateTime();?>
		</footer>
		<?php }?>

		<table style="width:100%;" cellspacing="0" cellpadding="3">
			<thead>
			<tr>
				<th style="width: 24.5%;"></th>
				<th style="width: 24.5%;"></th>
				<th style="width: 24.5%;"></th>
				<th style="width: 24.5%;"></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td colspan="2"><img src="<?=$logoBase64?>" alt="logo"></td>
				<td colspan="2" style="text-align: right;">
					<h3 style="text-align: right; width: 100%;">
						<?php echo $companyInfo['CompanyName']->cnf_val;?>
					</h3>
					<h5 style="text-align: right; width: 100%;">
						<?php
						echo !empty($companyInfo['CompanyAddress']->cnf_val) ? $companyInfo['CompanyAddress']->cnf_val : "";
						echo !empty($companyInfo['CompanyCity']->cnf_val) ? ", ".$companyInfo['CompanyCity']->cnf_val : "";
						echo !empty($companyInfo['CompanyZip']->cnf_val) ? "-".$companyInfo['CompanyZip']->cnf_val : "";
						echo !empty($companyInfo['CompanyCountry']->cnf_val) ? ", ".$companyInfo['CompanyCountry']->cnf_val : "";
						?>
					</h5>
					<?php
					echo !empty($companyInfo['CompanyVAT']->cnf_val) ? "<h5 style='width: 100%'>VAT No: ".$companyInfo['CompanyVAT']->cnf_val."</h5>" : "";
					?>
				</td>
			</tr>
			<tr>
				<td colspan="4" style="background-color: #e0e0e0;">
					<b style="width: 100%;font-size: 1.12em">Invoice #<?php echo $invoice['invoice_no']; ?></b><br/><br/>
					<b style="width: 100%;">Invoice Date: <?php echo commonDateFormat($invoice['inserted_on']); ?></b><br/>
					<b style="width: 100%;">Due Date: <?php echo commonDateFormat($invoice['due_date']); ?></b><br/>
				</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="4">
					<h3 style="width: 100%;">Invoice To</h3>
					<b style="width: 100%;font-size: 1.12em"><?php echo $invoice['company_name']; ?></b><br/>
					<span style="width: 100%;">
						<?php
						echo !empty($invoice['company_address']) ? $invoice['company_address'] : "";
						echo !empty($invoice['company_city']) ? ", ".$invoice['company_city'] : "";
						echo !empty($invoice['company_state']) ? ", ".$invoice['company_state'] : "";
						echo !empty($invoice['zip_code']) ? "-".$invoice['zip_code'] : "";
						?>
					</span><br/>
					<span style="width: 100%;">
						<?php
						echo !empty($invoice['country']) ? $invoice['country'] : "";
						?>
					</span>
				</td>
			</tr>

			<tr><td colspan="4">&nbsp;</td></tr>
			<tr><td colspan="4">&nbsp;</td></tr>

			<tr>
				<td colspan="3" style="border: 1px solid #c0c0c0;"><h4>Particulars</h4></td>
				<td style="border: 1px solid #c0c0c0;text-align: right;"><h4>Amount</h4></td>
			</tr>

			<?php
			$subTotal = 0;
			$credit = 0;
			foreach ($invoiceItems as $row){

				$itemType = "";
				if( $row['item_type'] == 1 ){
					$itemType = "Domain";
				} else if( $row['item_type'] == 2 ){
					$itemType = "Hosting";
				} else{
					$itemType = "Other";
				}

				echo '<tr>';
				echo '<td colspan="3" style="border: 1px solid #c0c0c0;">'.$itemType.' -> '.$row['item_desc'].'</td>';
				echo '<td style="border: 1px solid #c0c0c0;text-align: right;">'.$row['total'].' '.$invoice['currency_code'].'</td>';
				echo '</tr>';
				$subTotal += $row['total'];
			}
			?>

			<tr>
				<td colspan="3" style="border: 1px solid #c0c0c0;text-align: right;"><b>Sub Total</b></td>
				<td style="border: 1px solid #c0c0c0;text-align: right;"><b><?=$subTotal.' '.$invoice['currency_code']?></b></td>
			</tr>

			<tr>
				<td colspan="3" style="border: 1px solid #c0c0c0;text-align: right;">Credit</td>
				<td style="border: 1px solid #c0c0c0;text-align: right;">(-)&nbsp;<?=$credit.' '.$invoice['currency_code']?></td>
			</tr>

			<tr>
				<td colspan="3" style="border: 1px solid #c0c0c0;text-align: right;">Discount</td>
				<td style="border: 1px solid #c0c0c0;text-align: right;">(-)&nbsp;<?=$invoice['discount_amount'].' '.$invoice['currency_code']?></td>
			</tr>

			<tr>
				<td colspan="3" style="border: 1px solid #c0c0c0;text-align: right;"><b>Grand Total</b></td>
				<td style="border: 1px solid #c0c0c0;text-align: right;"><b><?=$invoice['order_amount'].' '.$invoice['currency_code']?></b></td>
			</tr>

			<tr><td colspan="4">&nbsp;</td></tr>
			<tr><td colspan="4">&nbsp;</td></tr>

			<?php
			if( !empty($txnHistory) ){
				?>
				<tr><td colspan="4"><h3>Transaction History</h3></td></tr>
				<tr>
					<td style="border: 1px solid #c0c0c0;"><h5>Transaction Date</h5></td>
					<td style="border: 1px solid #c0c0c0;"><h5>Payment with</h5></td>
					<td style="border: 1px solid #c0c0c0;"><h5>Transaction ID#</h5></td>
					<td style="border: 1px solid #c0c0c0;text-align: right;"><h5>Amount</h5></td>
				</tr>
				<?php
				foreach ($txnHistory as $row){
					echo '<tr>';
					echo '<td style="border: 1px solid #c0c0c0;">'.$row['item_desc'].'</td>';
					echo '<td style="border: 1px solid #c0c0c0;">'.$row['item_desc'].'</td>';
					echo '<td style="border: 1px solid #c0c0c0;">'.$row['item_desc'].'</td>';
					echo '<td style="border: 1px solid #c0c0c0;text-align: right;">'.$row['total'].' '.$invoice['currency_code'].'</td>';
					echo '</tr>';
				}
				?>
				<tr><td colspan="4">&nbsp;</td></tr>
				<tr><td colspan="4">&nbsp;</td></tr>

			<?php }?>

			<tr>
				<td colspan="2">

					<?php if( $invoice['pay_status'] != "PAID" ){ ?>
						<div style="max-width:200px;border: 3px solid #DD0000; border-radius: 8px;transform: rotate(-20deg);transition: transform 2s;">
							<h2 style="color:#DD0000;text-align: center; margin: 0px;">UNPAID</h2>
						</div>
					<?php } else {?>
						<div style="max-width:200px;border: 3px solid #00DD00; border-radius: 8px;transform: rotate(-20deg);transition: transform 2s;">
							<h2 style="color:#00DD00;text-align: center; margin: 0px;">PAID</h2>
						</div>
					<?php }?>

				</td>
				<td colspan="2"></td>
			</tr>

			<tr><td colspan="4">&nbsp;</td></tr>

			</tbody>
		</table>

	</body>
</html>
