app.controller('ServiceProductCtrl', function ($scope, $http, $timeout, $rootScope, $sce, $mdDialog, $interval, ClientService, DialogBox, Communication) {
	$scope.baseurl = BASE_URL;
	$scope.tickets = [];

	// Domain modal state
	$scope.hosting_domain = "";
	$scope.domain_action = "dns_update"; // dns_update, register, transfer
	$scope.epp_code = "";
	$scope.pay_term = "";
	$scope.sericeId = "";
	$scope.sericeName = "";
	$scope.hosting_cart_id = null;

	// Domain search state
	$scope.domain_search_keyword = "";
	$scope.domain_search_results = [];
	$scope.domain_searching = false;
	$scope.domain_search_no_results = false;
	$scope.selected_domain = {};
	$scope.transfer_price_info = null;

	// Reset modal state
	$scope.resetModalState = function() {
		$scope.hosting_domain = "";
		$scope.domain_action = "dns_update";
		$scope.epp_code = "";
		$scope.domain_search_keyword = "";
		$scope.domain_search_results = [];
		$scope.domain_searching = false;
		$scope.domain_search_no_results = false;
		$scope.selected_domain = {};
		$scope.transfer_price_info = null;
		$scope.hosting_cart_id = null;
	};

	// Open hosting modal - Flow 1
	$scope.addToService = function(sericeId, name) {
		let e = document.getElementById("pay_term_" + sericeId);
		$scope.pay_term = e.options[e.selectedIndex].value;
		$scope.sericeId = sericeId;
		$scope.sericeName = name;

		$scope.resetModalState();

		var modalEl = document.getElementById('hostingDomainModal');
		var modal = bootstrap.Modal.getOrCreateInstance(modalEl, {backdrop: 'static', keyboard: false});
		modal.show();
	};

	// Handle domain action change
	$scope.onDomainActionChange = function() {
		$scope.hosting_domain = "";
		$scope.epp_code = "";
		$scope.domain_search_results = [];
		$scope.selected_domain = {};
		$scope.domain_search_no_results = false;
		$scope.transfer_price_info = null;
	};

	// Search domain availability for registration
	$scope.searchDomainAvailability = function() {
		if (!$scope.domain_search_keyword || $scope.domain_search_keyword.trim() === '') {
			toastWarning("Please enter a domain name to search");
			return;
		}

		$scope.domain_searching = true;
		$scope.domain_search_results = [];
		$scope.domain_search_no_results = false;
		$scope.selected_domain = {};

		let url = BASE_URL + 'domain-search?type=register&domkeyword=' + encodeURIComponent($scope.domain_search_keyword);

		let req = Communication.request("GET", url, {});
		req.then(function (resp) {
			$scope.domain_searching = false;

			if (resp.error) {
				toastError(resp.error);
				$scope.domain_search_no_results = true;
				return;
			}

			if (resp.status === 1 && resp.info && resp.info.length > 0) {
				$scope.domain_search_results = resp.info;
			} else {
				$scope.domain_search_no_results = true;
			}

		}, function (err) {
			$scope.domain_searching = false;
			$scope.domain_search_no_results = true;
			console.log("Domain search error", err);
		});
	};

	// Select a domain from search results
	$scope.selectDomain = function(domain) {
		$scope.selected_domain = domain;
		$scope.hosting_domain = domain.name;
	};

	// Handle transfer domain input change - get pricing
	$scope.onTransferDomainChange = function() {
		if (!$scope.hosting_domain || $scope.hosting_domain.trim() === '') {
			$scope.transfer_price_info = null;
			return;
		}

		// Extract extension and get transfer price
		let parts = $scope.hosting_domain.split('.');
		if (parts.length >= 2) {
			let ext = '.' + parts[parts.length - 1];
			// Find matching price from dom_prices (passed from PHP)
			// For now, we'll get it from the API when adding to cart
		}
	};

	// Check if can add to cart
	$scope.canAddToCart = function() {
		if (!$scope.hosting_domain || $scope.hosting_domain.trim() === '') {
			return false;
		}

		if ($scope.domain_action === 'register' && !$scope.selected_domain.domPriceId) {
			return false;
		}

		if ($scope.domain_action === 'transfer' && (!$scope.epp_code || $scope.epp_code.trim() === '')) {
			return false;
		}

		return true;
	};

	// Flow-1: Add hosting with domain
	$scope.addHostingWithDomain = function() {
		if (!$scope.canAddToCart()) {
			toastWarning("Please complete all required fields");
			return;
		}

		DialogBox.showProgress();

		// Step 1: Add hosting to cart
		let req = Communication.request("POST", BASE_URL + 'cart/addHostingToCart', {
			"product_service_pricing_id": $scope.pay_term,
			"quantity": 1
		});

		req.then(function (resp) {
			if (resp.code === 200 && resp.data && resp.data.cart_id) {
				$scope.hosting_cart_id = resp.data.cart_id;

				// Step 2: Link domain to hosting
				$scope.linkDomainToHosting();

			} else {
				DialogBox.hideProgress();
				toastError(resp.msg || "Failed to add hosting to cart");
			}

		}, function (err) {
			DialogBox.hideProgress();
			toastError("Failed to add hosting to cart");
			console.log("addHostingToCart error", err);
		});
	};

	// Link domain to hosting cart
	$scope.linkDomainToHosting = function() {
		let domPricingId = 0;

		// Get domain pricing ID based on action
		if ($scope.domain_action === 'register') {
			domPricingId = $scope.selected_domain.domPriceId || 0;
		} else if ($scope.domain_action === 'transfer') {
			// For transfer, we need to find the pricing ID based on domain extension
			// This should ideally be fetched from the server
			domPricingId = $scope.getTransferPricingId();
		}

		let req = Communication.request("POST", BASE_URL + 'cart/linkDomainToHosting', {
			"parent_cart_id": $scope.hosting_cart_id,
			"domain_action": $scope.domain_action,
			"domain_name": $scope.hosting_domain,
			"epp_code": $scope.epp_code || null,
			"dom_pricing_id": domPricingId
		});

		req.then(function (resp) {
			DialogBox.hideProgress();

			if (resp.code === 200) {
				toastSuccess("Hosting and domain added to cart successfully!");

				// Close modal
				var modalEl = document.getElementById('hostingDomainModal');
				var modal = bootstrap.Modal.getInstance(modalEl);
				if (modal) modal.hide();

				// Reset state
				$scope.resetModalState();
				$scope.pay_term = "";
				$scope.sericeId = "";
				$scope.sericeName = "";

				// Update cart count
				$scope.updateCartCount();

			} else {
				toastError(resp.msg || "Failed to link domain to hosting");
			}

		}, function (err) {
			DialogBox.hideProgress();
			toastError("Failed to link domain to hosting");
			console.log("linkDomainToHosting error", err);
		});
	};

	// Get transfer pricing ID based on domain extension
	$scope.getTransferPricingId = function() {
		// This will be handled server-side if not found
		// For now, return 0 and let server find it
		return 0;
	};

	// Update cart count in header
	$scope.updateCartCount = function() {
		let req = Communication.request("GET", BASE_URL + 'cart/getCount', {});
		req.then(function (resp) {
			if (resp.count !== undefined) {
				// Update cart badge if exists
				let cartBadge = document.querySelector('.cart-count-badge');
				if (cartBadge) {
					cartBadge.textContent = resp.count;
				}
			}
		});
	};

	// Legacy method for backwards compatibility
	$scope.addToCartApiCall = function() {
		$scope.addHostingWithDomain();
	}

    $scope.getSupportTickets = function(){
        $scope.tickets = [];
		let req = Communication.request("POST", BASE_URL + 'supports/ticket_list_api', {"limit":5});
        req.then(function (resp) {
            $scope.tickets = resp;
        }, function (err) {
            log("tickets error", JSON.stringify(err));
        });
    };
    

});


app.controller('ServiceDomainCtrl', function ($scope, $http, $timeout, $rootScope, $sce, $mdDialog, $interval, ClientService, DialogBox, Communication) {
	$scope.baseurl = BASE_URL;
	$scope.search_domain_name = "";
	$scope.data = {
		status:-1,
		info:{}
	};
	$scope.suggestionList = [];

	$scope.loadDomainToVar = function(){
		$scope.search_domain_name = document.getElementById("search_domain_name").value;

		if( $scope.search_domain_name && $scope.search_domain_name.trim() != "" ){
			$scope.btnSearchDomain();
		}
	}

    $scope.btnSearchDomain = function(){

		$scope.search_domain_name = document.getElementById("search_domain_name").value;

		if (!$scope.search_domain_name || $scope.search_domain_name.trim() === '') {
			toastWarning("Please enter a domain name to search");
			return;
		}

		// Check if reCAPTCHA is configured and get response
		let recaptchaToken = '';
		if (typeof RECAPTCHA_SITE_KEY !== 'undefined' && RECAPTCHA_SITE_KEY !== '') {
			recaptchaToken = grecaptcha.getResponse();
			if (!recaptchaToken) {
				toastWarning("Please complete the reCAPTCHA verification");
				return;
			}
		}

		DialogBox.showProgress();

		let url = BASE_URL + 'domain-search?type=register&domkeyword=' + $scope.search_domain_name;
		if (recaptchaToken) {
			url += '&recaptcha_token=' + encodeURIComponent(recaptchaToken);
		}

		let req = Communication.request("GET", url, {});
        req.then(function (resp) {
			DialogBox.hideProgress();

			// Reset reCAPTCHA for next search
			if (typeof grecaptcha !== 'undefined' && RECAPTCHA_SITE_KEY !== '') {
				grecaptcha.reset();
			}

			if (resp.error) {
				toastError(resp.error);
				$scope.data = {status: -1, info: {}};
				return;
			}

            $scope.data = resp;

			setTimeout(function (){
				$scope.getDomainSuggestion();
			}, 100);

        }, function (err) {
			DialogBox.hideProgress();
			// Reset reCAPTCHA on error
			if (typeof grecaptcha !== 'undefined' && RECAPTCHA_SITE_KEY !== '') {
				grecaptcha.reset();
			}
            log("summary error", JSON.stringify(err));
        });
    };

	$scope.getDomainSuggestion = function(){
		$scope.suggestionList = [];

		document.getElementById('avail-ext-price').style.display = 'none';
		document.getElementById('domain-suggestions').style.display = 'block';

		let req = Communication.request("GET", BASE_URL + 'domain-suggestion?domkeyword='+$scope.search_domain_name, {});
		req.then(function (resp) {
			$scope.suggestionList = resp;

		}, function (err) {
			log("summary error", JSON.stringify(err));
		});

	};


	$scope.addToCartRegisterDomain = function(domPriceId, fullDomain){
		let item_type = 1; // Domain

		DialogBox.showProgress();
		let req = Communication.request("POST", BASE_URL + 'cart/addToCartAjax/'+item_type+'/'+domPriceId,
			{
				"serviceId": domPriceId,
				"item": fullDomain + " - Domain Registration",
				"hosting_domain": fullDomain,
				"hosting_domain_type": 1, // 0=DNS, 1=REGISTER, 2=TRANSFER
				"quantity": 1,
			}
		);
		req.then(function (resp) {
			DialogBox.hideProgress();
			if( resp.code == 1 ){
				toastSuccess(resp.msg);

			} else{
				toastError(resp.msg);
			}

		}, function (err) {
			DialogBox.hideProgress();
			log("addToService error", JSON.stringify(err));
		});

	}
    

});



app.controller('ServiceCheckoutCtrl', function ($scope, $http, $timeout, $rootScope, $sce, $mdDialog, $interval, ClientService, DialogBox, Communication) {
	$scope.baseurl = BASE_URL;
	$scope.payment_gateway = "0";
	$scope.instructions = "";

	$scope.clearCartData = function(path){
		DialogBox.confirm("Are you sure to delete this cart?").then(function (confirm){
			if( confirm ){

				DialogBox.showProgress();
				let req = Communication.request("GET", BASE_URL + path, {});
				req.then(function (resp) {
					DialogBox.hideProgress();

					setTimeout(function (){
						window.location.reload();
					}, 100);

				}, function (error) {
					DialogBox.hideProgress();
					console.log("error", error);
				});

			}
		}, function (err) {
			console.log("err", err);
		});
	}


	$scope.btnCartCheckout = function(){

		if( $scope.payment_gateway == "0" ){
			toastWarning("Please select payment type");
			return;
		}

		DialogBox.showProgress();
		let req = Communication.request("POST", BASE_URL + 'cart/checkoutSubmit', {"payment_gateway":$scope.payment_gateway, "instructions":$scope.instructions, "promo_code":""});
		req.then(function (resp) {
			DialogBox.hideProgress();

			if( resp.code == 200 ){
				toastSuccess(resp.msg);
			} else {
				toastWarning(resp.msg);
			}

		}, function (err) {
			DialogBox.hideProgress();
			toastError(err.msg);
		});
	};

});
