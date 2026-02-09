app.controller('AdminDashboardCtrl', function ($scope, $http, $timeout, $rootScope, $sce, $mdDialog, $interval, ClientService, DialogBox, Communication) {
	$scope.baseurl = BASE_URL;
	$scope.tickets = [];
	$scope.invoices = [];
	$scope.summary = [];
	$scope.domainPrices = [];
	$scope.expensesData = null;
	$scope.expensesChart = null;
	$scope.loadingDomainPrices = false;
	$scope.loadingExpenses = false;

    $scope.getSummaryInfo = function(){
        $scope.summary = [];
        $scope.summary[0] = {"cnt":-1};
        $scope.summary[1] = {"cnt":-1};
        $scope.summary[2] = {"cnt":-1};
        $scope.summary[3] = {"cnt":-1};

        var req = Communication.request("POST", BASE_URL + 'whmazadmin/dashboard/summary_api', {});
        req.then(function (resp) {
            $scope.summary = resp;
        }, function (err) {
            log("summary error", JSON.stringify(err));
        });
    };

    $scope.getSupportTickets = function(){
        $scope.tickets = [];
        var req = Communication.request("POST", BASE_URL + 'whmazadmin/ticket/recent_list_api', {"limit":5});
        req.then(function (resp) {
            $scope.tickets = resp;
        }, function (err) {
            log("tickets error", JSON.stringify(err));
        });
    };

	$scope.getRecentInvoices = function(){
		$scope.invoices = [];
		var req = Communication.request("POST", BASE_URL + 'whmazadmin/invoice/recent_list_api', {"limit":5});
		req.then(function (resp) {
			$scope.invoices = resp;
		}, function (err) {
			log("invoices error", JSON.stringify(err));
		});
	};

    $scope.getPendingOrders = function(){
		$scope.orders = [];
		var req = Communication.request("POST", BASE_URL + 'whmazadmin/order/recent_list_api', {"limit":5});
		req.then(function (resp) {
			$scope.orders = resp;
		}, function (err) {
			log("orders error", JSON.stringify(err));
		});
	};

	// Get domain selling prices
	$scope.getDomainPrices = function(){
		$scope.loadingDomainPrices = true;
		$scope.domainPrices = [];
		var req = Communication.request("POST", BASE_URL + 'whmazadmin/dashboard/domain_prices_api', {"limit": 10});
		req.then(function (resp) {
			$scope.domainPrices = resp;
			$scope.loadingDomainPrices = false;
		}, function (err) {
			log("domain prices error", JSON.stringify(err));
			$scope.loadingDomainPrices = false;
		});
	};

	// Get last 12 months expenses and render chart
	$scope.getExpensesChart = function(){
		$scope.loadingExpenses = true;
		var req = Communication.request("POST", BASE_URL + 'whmazadmin/dashboard/expenses_chart_api', {});
		req.then(function (resp) {
			$scope.expensesData = resp;
			$scope.loadingExpenses = false;
			// Render chart after data is loaded
			$timeout(function() {
				$scope.renderExpensesChart();
			}, 100);
		}, function (err) {
			log("expenses chart error", JSON.stringify(err));
			$scope.loadingExpenses = false;
		});
	};

	// Render the expenses bar chart using Chart.js
	$scope.renderExpensesChart = function() {
		var ctx = document.getElementById('expensesChart');
		if (!ctx || !$scope.expensesData) return;

		// Destroy existing chart if any
		if ($scope.expensesChart) {
			$scope.expensesChart.destroy();
		}

		$scope.expensesChart = new Chart(ctx.getContext('2d'), {
			type: 'bar',
			data: {
				labels: $scope.expensesData.labels,
				datasets: [{
					label: 'Monthly Expenses',
					data: $scope.expensesData.amounts,
					backgroundColor: 'rgba(54, 162, 235, 0.7)',
					borderColor: 'rgba(54, 162, 235, 1)',
					borderWidth: 1,
					borderRadius: 4,
					barThickness: 'flex',
					maxBarThickness: 40
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Expense: ' + context.parsed.y.toLocaleString();
							}
						}
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return value.toLocaleString();
							}
						},
						grid: {
							color: 'rgba(0, 0, 0, 0.05)'
						}
					},
					x: {
						grid: {
							display: false
						},
						ticks: {
							maxRotation: 45,
							minRotation: 45
						}
					}
				}
			}
		});
	};

});
