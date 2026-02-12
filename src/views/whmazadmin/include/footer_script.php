<script src="<?=base_url()?>resources/lib/jquery/jquery.min.js"></script>
<script src="<?=base_url()?>resources/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?=base_url()?>resources/lib/feather-icons/feather.min.js"></script>
<script src="<?=base_url()?>resources/lib/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="<?=base_url()?>resources/lib/prismjs/prism.js"></script>
<script src="<?=base_url()?>resources/lib/quill/quill.min.js"></script>

<script src="<?=base_url()?>resources/assets/js/dashforge.js"></script>

<!-- append theme customizer -->
<script src="<?=base_url()?>resources/lib/js-cookie/js.cookie.js"></script>
<script src="<?=base_url()?>resources/assets/js/dashforge.settings.js"></script>

<script src="<?=base_url()?>resources/lib/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?=base_url()?>resources/lib/datatables.net-dt/js/dataTables.dataTables.min.js"></script>
<script src="<?=base_url()?>resources/lib/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?=base_url()?>resources/lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js"></script>
<script src="<?=base_url()?>resources/lib/select2/js/select2.min.js"></script>
<script src="<?=base_url()?>resources/lib/sweetalert2/sweetalert2.all.min.js"></script>
<script src="<?=base_url()?>resources/lib/chart.js/Chart.bundle.min.js"></script>

<script src="<?=base_url()?>resources/angular/angular.min.js?v=1.0.0"></script>

<script src="<?=base_url()?>resources/angular/angular-ui-router.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-animate.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-aria.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-messages.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-sanitize.min.js?v=1.0.0"></script>

<script src="<?=base_url()?>resources/angular/angular-material.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/ngDialog.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/ngToast.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/assets/js/jquery.toast.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/assets/js/toastcode.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/assets/js/custom.js?v=1.0.0"></script>

<script>
	$(function(){
		'use strict'

		// Setup CSRF token for jQuery AJAX requests
		var csrfName = $('meta[name="csrf-token-name"]').attr('content');
		var csrfHash = $('meta[name="csrf-token-hash"]').attr('content');

		$.ajaxSetup({
			beforeSend: function(xhr, settings) {
				// Add CSRF token to POST requests
				if (settings.type === 'POST' && csrfName && csrfHash) {
					// Always send CSRF token in header for JSON requests
					xhr.setRequestHeader('X-CSRF-TOKEN', csrfHash);

					// For non-JSON requests, also add to data
					var contentType = settings.contentType || '';
					if (contentType.indexOf('application/json') === -1) {
						settings.data = settings.data || {};
						if (typeof settings.data === 'string') {
							settings.data += '&' + csrfName + '=' + csrfHash;
						} else if (typeof settings.data === 'object') {
							settings.data[csrfName] = csrfHash;
						}
					}
				}
			},
			complete: function(xhr) {
				// Update CSRF token from response headers
				var newCsrfName = xhr.getResponseHeader('X-CSRF-TOKEN-NAME');
				var newCsrfHash = xhr.getResponseHeader('X-CSRF-TOKEN-HASH');

				if (newCsrfName && newCsrfHash) {
					csrfName = newCsrfName;
					csrfHash = newCsrfHash;
					$('meta[name="csrf-token-name"]').attr('content', newCsrfName);
					$('meta[name="csrf-token-hash"]').attr('content', newCsrfHash);
				}
			}
		});

		window.darkMode = function(){
			$('.btn-white').addClass('btn-dark').removeClass('btn-white');
		}

		window.lightMode = function() {
			$('.btn-dark').addClass('btn-white').removeClass('btn-dark');
		}

		var hasMode = Cookies.get('df-mode');
		if(hasMode === 'dark') {
			darkMode();
		} else {
			lightMode();
		}

	})
</script>
