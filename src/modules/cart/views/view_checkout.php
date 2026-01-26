<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

		<div class="row">
			<div class="col-md-3 col-sm-12">
				<?php $this->load->view('templates/customer/cart_category_nav'); ?>
				<?php $this->load->view('templates/customer/cart_action_nav'); ?>
			</div>


			<div class="col-md-9 col-sm-12">
				<h3>CheckOut</h3>
				<hr class="mg-5"/>
				<div class="row mg-t-15">
					<div class="card card-widget card-contacts" style="margin-top:15px;">
						<div class="card-header">
							<h6 class="card-title mg-b-0"><i class="fa fa-money"></i> &nbsp;Choose Payment Type</h6>
							<nav class="nav">

							</nav>
						</div><!-- card-header -->
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<select class="form-select">
										<option value="1">Online</option>
										<option value="1">Offline</option>

								</select>
							</li>
						</ul>
					</div>

					<div class="col-md-12">
						<table class="table table-borderless">
							<tbody>
							<tr>

							</tr>
							</tbody>
						</table>
						<span id="registryData"> </span>
						<a  href='<?= base_url() ?>cart/checkout' class="btn btn-primary" type="submit">Continue</a>
					</div>
				</div>

			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
	$(function () {
		var TotalValue = 0;

		$("tr #subtotal").each(function (index, value) {
			currentRow = parseFloat($(this).text());
			TotalValue += currentRow
		});

		document.getElementById('total').innerHTML = TotalValue;

	});
</script>

<script>
	function myFunction(url) {
		var r = confirm("Are you sure !");
		if (r == true) {
			window.location.href = url;
		} else {
			txt = "You pressed Cancel!";
		}
	}
</script>

<?php $this->load->view('templates/customer/footer_script'); ?>
<script>
	<?php $alert_success = $this->session->flashdata('alert_success'); ?>
	<?php if ($alert_success) { ?>
		toastSuccess(<?= json_encode(htmlspecialchars($alert_success, ENT_QUOTES, 'UTF-8')) ?>);
	<?php } ?>
	<?php $alert_error = $this->session->flashdata('alert_error'); ?>
	<?php if ($alert_error) { ?>
		toastError(<?= json_encode(htmlspecialchars($alert_error, ENT_QUOTES, 'UTF-8')) ?>);
	<?php } ?>
</script>
<?php $this->load->view('templates/customer/footer'); ?>
