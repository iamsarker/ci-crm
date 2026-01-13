app.controller('ClientareaCtrl', function ($scope, $http, $timeout, $rootScope, $sce, $mdDialog, $interval, ClientService, DialogBox, Communication) {
	$scope.baseurl = BASE_URL;
	$scope.tickets = [];
	$scope.invoices = [];
	$scope.summary = [];

    $scope.getSummaryInfo = function(){
        $scope.summary = [];
        $scope.summary[0] = {"cnt":-1};
        $scope.summary[1] = {"cnt":-1};
        $scope.summary[2] = {"cnt":-1};
        $scope.summary[3] = {"cnt":-1};

        var req = Communication.request("POST", BASE_URL + 'clientarea/summary_api', {});
        req.then(function (resp) {
            $scope.summary = resp;
        }, function (err) {
            log("summary error", JSON.stringify(err));
        });
    };

    $scope.getSupportTickets = function(){
        $scope.tickets = [];
        var req = Communication.request("POST", BASE_URL + 'tickets/ticket_list_api', {"limit":5});
        req.then(function (resp) {
            $scope.tickets = resp;
        }, function (err) {
            log("tickets error", JSON.stringify(err));
        });
    };

	$scope.getRecentInvoices = function(){
		$scope.invoices = [];
		var req = Communication.request("POST", BASE_URL + 'billing/invoice_list_api', {"limit":5});
		req.then(function (resp) {
			$scope.invoices = resp;
		}, function (err) {
			log("invoices error", JSON.stringify(err));
		});
	};
    

});
