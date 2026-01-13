<!DOCTYPE html>
<html>
	<head>
	    <meta charset="utf-8" />
	    <title>Techno Valley 21 - Invoice Information</title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	</head>
	<body>
		<table style="font-family:Courier New;" width="100%">
			<tr>
				<td colspan="5">
					Dear valued Customer(<b><?php echo $info['customer'];?></b>),<br/>
					Here is the invoice details which has been <b>marked as paid</b> or <b>modified</b> at <i style="text-decoration-line: underline; text-decoration-style: dotted;"><?php echo $invoice['created'];?>.</i>
					<h4>You can pay via Rocket/DBBL/City Bank account.</h4>
					Invoice # <b><?php echo $invoice['invoice_no'];?></b><br/>
					Sales Date : <b><?php echo $invoice['sales_date'];?></b><br/>
					Total Amount : <b><?php echo $invoice['g_total'];?></b><br/><br/>
				</td>
			</tr>
			<tr>
				<td colspan="5" style="border:1px dotted #999;"> <b>Invoice Information</b> </td>
			</tr>
			<tr>
				<td style="border:1px dotted #999; text-align:center;"> <b>Particulars</b> </td>
				<td style="border:1px dotted #999; text-align:center;"> <b>Qty</b> </td>
				<td style="border:1px dotted #999; text-align:center;"> <b>Duration</b> </td>
				<td style="border:1px dotted #999; text-align:center;"> <b>Remarks</b> </td>
				<td style="border:1px dotted #999; text-align:center;"> <b>Amount (BDT)</b> </td>
			</tr>

			<?php foreach ($invoiceDetails as $key => $value) {?>
				<tr>
					<td style="border:1px dotted #999;"> <?php echo $serviceList[$value['service_id']];?> </td>
					<td style="border:1px dotted #999; text-align:center;"> <?php echo $value['qty'];?> </td>
					<td style="border:1px dotted #999; text-align:center;"> <?php echo $SERVICE_YEAR[$value['service_duration']];?> </td>
					<td style="border:1px dotted #999;"> <?php echo $value['remark'];?> </td>
					<td style="border:1px dotted #999; text-align:right;"> <?php echo $value['amount'];?>&nbsp;</td>
				</tr>
			<?php } ?>
			
			<tr>
				<td style="border:1px dotted #999; text-align:right;" colspan="4"><b>*INVOICE AMOUNT</b></td>
				<td style="border:1px dotted #999; text-align:right;"><b> <?php echo $invoice['total'];?> </b>&nbsp;</td>
			</tr>
			<tr>
				<td style="border:1px dotted #999; text-align:right;" colspan="4"><b>*INVOICE PROMO/DISCOUNT</b></td>
				<td style="border:1px dotted #999; text-align:right;"><b> <?php echo $invoice['discount'];?> </b>&nbsp;</td>
			</tr>
			<tr>
				<td style="border:1px dotted #999; text-align:right;" colspan="4"><b>*INVOICE RECEIVED</b></td>
				<td style="border:1px dotted #999; text-align:right;"><b> <?php echo $invoice['total_receive_amount'];?> </b>&nbsp;</td>
			</tr>
			<tr>
				<td style="border:1px dotted #999; text-align:right;" colspan="4"><b>*INVOICE DUE</b></td>
				<td style="border:1px dotted #999; text-align:right;"><b> <?php echo ($invoice['g_total'] - $invoice['total_receive_amount']);?> </b>&nbsp;</td>
			</tr>
			<tr>
				<td style="border:1px dotted #999; text-align:right;color:#F0AD4E;" colspan="4"><b>PREVIOUS DUE</b></td>
				<td style="border:1px dotted #999; text-align:right;color:#F0AD4E;"><b> <?php echo ($info['g_total']-$info['received']);?> </b>&nbsp;</td>
			</tr>
			<tr>
				<td style="border:1px dotted #999; text-align:right;color:#D43F3A;" colspan="4"><b>TOTAL DUE</b></td>
				<td style="border:1px dotted #999; text-align:right;color:#D43F3A;"><b> <?php echo (($info['g_total']-$info['received']) + $invoice['g_total']) - $invoice['total_receive_amount'];?></b>&nbsp;</td>
			</tr>

			<tr><td colspan="5">&nbsp;</td></tr>
			<tr><td colspan="5">&nbsp;</td></tr>
			<tr>
				<td colspan="5"> <h3>Support</h3> </td>
			</tr>
			<tr>
				<td colspan="5">For any support with respect to your relationship with us you can always contact us directly using the following Information.
 				</td>
			</tr>
			<tr><td colspan="5">&nbsp;</td></tr>
			<tr><td colspan="5">
				<b>Email Address 1:</b> techno.valley.21@gmail.com<br>
				<b>Email Address 2:</b> support@technovalley21.com<br>
				<b>Tel No.:</b> +88 01536 121323, +88 01824 880161<br><br>
				<b>Visit:</b> <a target="_blank" href="http://www.technovalley21.com/">www.technovalley21.com</a>
			</td></tr>
			<tr><td colspan="5">&nbsp;</td></tr>
			<tr>
				<td colspan="5">
					<h3>&nbsp;Thanks</h3>
					<b>&nbsp;Md. Shahadat Sarker</b><br>
					<img class="logo" alt="TECHNO VALLEY 21" src="http://technovalley21.com/images/tvalley21.png">
				</td>
			</tr>
			<tr><td colspan="5">&nbsp;</td></tr>
			<tr><td colspan="5" style="font-size:9pt;"><b>Note:</b> this email is auto generated from Techno Valley 21 billing software. Please, don't reply to this mail.</td></tr>
		</table>
	</body>
</html>