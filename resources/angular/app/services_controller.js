app.controller('ServiceProductCtrl', function ($scope, $http, $timeout, $rootScope, $sce, $mdDialog, $interval, ClientService, DialogBox, Communication) {
	$scope.baseurl = BASE_URL;
	$scope.tickets = [];
	$scope.hosting_domain = "";
	$scope.hosting_domain_type = "0"; //0= UPDATE DNS, 1 = REGISTER, 2 = TRANSFER
	$scope.pay_term = "";
	$scope.sericeId = "";
	$scope.sericeName = "";


    $scope.addToService = function(sericeId, name){
		let e = document.getElementById("pay_term_"+sericeId);
		$scope.pay_term = e.options[e.selectedIndex].value;
		$scope.sericeId = sericeId;
		$scope.sericeName = name;

		var modalEl = document.getElementById('hostingDomainModal');
		var modal = bootstrap.Modal.getOrCreateInstance(modalEl, {backdrop: 'static', keyboard: false});
		modal.show();

    };

	$scope.addToCartApiCall = function(){
		let item_type = 2; // product_service (Hosting)

		if( $scope.hosting_domain.trim() == "" ){

			toastWarning("Domain is mandatory for that hosting");

		} else {

			DialogBox.showProgress();
			let req = Communication.request("POST", BASE_URL + 'cart/addToCartAjax/'+item_type+'/'+$scope.pay_term,
				{
					"serviceId": $scope.sericeId,
					"item": $scope.sericeName,
					"hosting_domain": $scope.hosting_domain,
					"hosting_domain_type": $scope.hosting_domain_type,
					"quantity": 1,
				}
			);
			req.then(function (resp) {
				DialogBox.hideProgress();
				if( resp.code == 1 ){
					toastSuccess(resp.msg);

					var modalEl = document.getElementById('hostingDomainModal');
					var modal = bootstrap.Modal.getInstance(modalEl);
					if (modal) modal.hide();

					if( $scope.hosting_domain_type.trim() == "1" ){
						let redirectUrl = BASE_URL + 'cart/domain?type=register&domkeyword='+$scope.hosting_domain;
						window.open(redirectUrl, "_self");

					} else if( $scope.hosting_domain_type.trim() == "1" ){
						let redirectUrl = BASE_URL + 'cart/domain?type=renew&domkeyword='+$scope.hosting_domain;
						window.open(redirectUrl, "_self");

					} else {

						$scope.hosting_domain = "";
						$scope.hosting_domain_type = "0"; //0= UPDATE DNS, 1 = REGISTER, 2 = TRANSFER
						$scope.pay_term = "";
						$scope.sericeId = "";
						$scope.sericeName = "";
					}

				} else{
					toastError(resp.msg);
				}

			}, function (err) {
				DialogBox.hideProgress();
				log("addToService error", JSON.stringify(err));
			});

		}

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
