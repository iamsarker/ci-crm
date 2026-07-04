<script src="<?=base_url()?>resources/lib/jquery/jquery.min.js"></script>
<script src="<?=base_url()?>resources/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?=base_url()?>resources/lib/prismjs/prism.js"></script>
<script src="<?=base_url()?>resources/lib/quill/quill.min.js"></script>

<!-- AdminLTE 4 JS -->
<script src="<?=base_url()?>resources/adminlte4/dist/js/adminlte.min.js"></script>

<!-- Theme Settings (cookies for dark/light mode) -->
<script src="<?=base_url()?>resources/lib/js-cookie/js.cookie.js"></script>

<!-- DataTables -->
<script src="<?=base_url()?>resources/lib/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?=base_url()?>resources/lib/datatables.net-dt/js/dataTables.dataTables.min.js"></script>
<script src="<?=base_url()?>resources/lib/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?=base_url()?>resources/lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js"></script>

<!-- Other Libraries -->
<script src="<?=base_url()?>resources/lib/select2/js/select2.min.js"></script>
<script src="<?=base_url()?>resources/lib/sweetalert2/sweetalert2.all.min.js"></script>

<!-- AngularJS -->
<script src="<?=base_url()?>resources/angular/angular.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-ui-router.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-animate.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-aria.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-messages.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-sanitize.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/angular-material.min.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/ngDialog.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/ngToast.js?v=1.0.0"></script>

<!-- Toast Notifications -->
<script src="<?=base_url()?>resources/assets/js/jquery.toast.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/assets/js/toastcode.js?v=1.0.0"></script>

<script>
	$(function(){
		'use strict'

		// Initialize Bootstrap tooltips
		$('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip()

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

		// Dark/Light Mode Toggle (using Bootstrap 5.3 color modes)
		window.darkMode = function(){
			document.documentElement.setAttribute('data-bs-theme', 'dark');
			Cookies.set('theme', 'dark', { expires: 365 });
			$('.btn-white').addClass('btn-dark').removeClass('btn-white');
			$('.bg-white').addClass('bg-dark').removeClass('bg-white');
		}

		window.lightMode = function() {
			document.documentElement.setAttribute('data-bs-theme', 'light');
			Cookies.set('theme', 'light', { expires: 365 });
			$('.btn-dark').addClass('btn-white').removeClass('btn-dark');
			$('.bg-dark').addClass('bg-white').removeClass('bg-dark');
		}

		// Apply saved theme on page load
		var savedTheme = Cookies.get('theme');
		if(savedTheme === 'dark') {
			darkMode();
		} else {
			lightMode();
		}

		// Currency change handler
		$(document).on("change", "select.currency", function(e){
			var vals = $(this).val();
			var arr = vals.split("-");

			$.ajax({
				url: "<?=base_url()?>change-currency/" + arr[0] + "/" + arr[1],
				method: "GET",
				success: function(html){
					window.location.reload();
				}
			});
		});

		// Load cart count via AJAX
		function loadCartCount(){
			$.ajax({
				url: "<?=base_url()?>cart/getCount",
				method: "GET",
				dataType: "json",
				success: function(response){
					var count = response.count || 0;
					var $badge = $('#cart-count-badge');
					if(count > 0){
						$badge.text(count > 99 ? '99+' : count).show();
					} else {
						$badge.hide();
					}
				}
			});
		}

		// Load cart count on page load
		loadCartCount();

		// Make it globally accessible to refresh after add/remove
		window.loadCartCount = loadCartCount;

	})
</script>

<!-- In-App Notifications -->
<style>
.notif-dropdown{width:340px;max-width:92vw;padding:0;}
.notif-list{max-height:360px;overflow-y:auto;}
.notif-badge-hidden{display:none;}
.notif-item{display:block;padding:10px 14px;border-bottom:1px solid #f0f0f0;color:inherit;text-decoration:none;cursor:pointer;}
.notif-item:hover{background:#f8f9fc;}
.notif-item.unread{background:#eef4ff;}
.notif-item .notif-title{font-weight:600;font-size:.85rem;margin-bottom:2px;}
.notif-item .notif-msg{font-size:.8rem;color:#6c757d;margin-bottom:2px;}
.notif-item .notif-time{font-size:.72rem;color:#a0a0a0;}
</style>
<script>
$(function(){
	var NOTIF_BASE = '<?=base_url()?>notifications/';
	var $badge = $('#notif-badge');
	var $list  = $('#notif-list');
	if(!$badge.length) return;

	function renderBadge(count){
		count = parseInt(count) || 0;
		$badge.text(count > 99 ? '99+' : count).css('display', count > 0 ? 'inline-block' : 'none');
	}
	function escapeHtml(s){ return $('<div>').text(s == null ? '' : s).html(); }

	function loadCount(){
		$.getJSON(NOTIF_BASE + 'unread_count', function(r){ if(r && r.success) renderBadge(r.count); });
	}

	function loadList(){
		$.getJSON(NOTIF_BASE + 'list_api', function(r){
			if(!r || !r.success) return;
			renderBadge(r.unread_count);
			if(!r.notifications.length){
				$list.html('<div class="text-center text-muted py-4">No notifications</div>');
				return;
			}
			var html = '';
			r.notifications.forEach(function(n){
				html += '<a class="notif-item' + (n.is_read == 0 ? ' unread' : '') + '" data-id="' + n.id + '" href="' + (n.url ? escapeHtml(n.url) : '#') + '">'
					 +  '<div class="notif-title">' + escapeHtml(n.title) + '</div>'
					 +  (n.message ? '<div class="notif-msg">' + escapeHtml(n.message) + '</div>' : '')
					 +  '<div class="notif-time">' + escapeHtml(n.time_ago) + '</div>'
					 +  '</a>';
			});
			$list.html(html);
		});
	}

	$('#notifBell').on('show.bs.dropdown', loadList);

	$list.on('click', '.notif-item', function(e){
		var $item = $(this), id = $item.data('id'), url = $item.attr('href');
		$.post(NOTIF_BASE + 'mark_read', { id: id }, function(){ $item.removeClass('unread'); loadCount(); });
		if(!url || url === '#'){ e.preventDefault(); }
	});

	$('#notif-mark-all').on('click', function(e){
		e.preventDefault();
		$.post(NOTIF_BASE + 'mark_all_read', {}, function(){ $list.find('.notif-item').removeClass('unread'); renderBadge(0); });
	});

	loadCount();
	setInterval(loadCount, 60000);
});
</script>
