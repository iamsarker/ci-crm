<?php $this->load->view('whmazadmin/include/header'); ?>

<div class="content content-fixed content-wrapper" ng-app="AdminDashboardApp">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="AdminDashboardCtrl">

		<!-- Dashboard Header -->
		<div class="dashboard-header d-sm-flex align-items-center justify-content-between">
			<div>
				<h4>Welcome back, <strong><?=getAdminFullName()?></strong></h4>
			</div>
			<div class="d-none d-md-flex gap-2 mt-3 mt-sm-0">
				<a href="<?=base_url()?>whmazadmin/company/manage" class="btn btn-sm pd-x-15 btn-white btn-uppercase">
					<i data-feather="user-plus" class="wd-10 mg-r-5"></i> New Customer
				</a>
				<a href="<?=base_url()?>whmazadmin/order/manage" class="btn btn-sm pd-x-15 btn-light-primary btn-uppercase">
					<i data-feather="plus-square" class="wd-10 mg-r-5"></i> New Order
				</a>
			</div>
		</div>

		<!-- Stats Cards -->
		<div class="row row-xs" ng-init="getSummaryInfo()">
			<div class="col-sm-6 col-lg-3 mb-3">
				<div class="dashboard-stat-card stat-card-customers">
					<div class="stat-icon">
						<i class="fa fa-users"></i>
					</div>
					<p class="stat-label">Customers</p>
					<h3 class="stat-value">
						{{summary[0].cnt}}
						<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[0].cnt < 0" />
					</h3>
				</div>
			</div>
			<div class="col-sm-6 col-lg-3 mb-3">
				<div class="dashboard-stat-card stat-card-orders">
					<div class="stat-icon">
						<i class="fa fa-shopping-cart"></i>
					</div>
					<p class="stat-label">Orders</p>
					<h3 class="stat-value">
						{{summary[1].cnt}}
						<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[1].cnt < 0" />
					</h3>
				</div>
			</div>
			<div class="col-sm-6 col-lg-3 mb-3">
				<div class="dashboard-stat-card stat-card-tickets">
					<div class="stat-icon">
						<i class="fa fa-ticket-alt"></i>
					</div>
					<p class="stat-label">Tickets</p>
					<h3 class="stat-value">
						{{summary[2].cnt}}
						<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[2].cnt < 0" />
					</h3>
				</div>
			</div>
			<div class="col-sm-6 col-lg-3 mb-3">
				<div class="dashboard-stat-card stat-card-invoices">
					<div class="stat-icon">
						<i class="fa fa-file-invoice-dollar"></i>
					</div>
					<p class="stat-label">Invoices</p>
					<h3 class="stat-value">
						{{summary[3].cnt}}
						<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[3].cnt < 0" />
					</h3>
				</div>
			</div>
		</div>


		<!-- List Cards Row -->
		<div class="row row-xs">

			<!-- Pending Orders Card -->
			<div class="col-md-6 col-xl-4 mb-3" ng-init="getPendingOrders()">
				<div class="card dashboard-card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center">
							<div class="header-icon icon-warning">
								<i class="fa fa-shopping-cart"></i>
							</div>
							<h6 class="mg-b-0">Pending Orders</h6>
						</div>
						<button class="refresh-btn" ng-click="getPendingOrders()">
							<i class="icon ion-md-refresh"></i>
						</button>
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item dashboard-list-item d-flex align-items-center" ng-repeat="obj in orders track by $index">
							<div class="item-avatar avatar-order d-none d-sm-flex">
								<i class="fa fa-shopping-cart"></i>
							</div>
							<div class="pd-l-15 flex-grow-1">
								<p class="item-title mg-b-0">
									<a href="{{baseurl}}whmazadmin/order/view_order/{{obj.order_uuid}}">Order #{{obj.order_no}}</a>
								</p>
								<span class="item-subtitle">{{obj.currency_code}} {{obj.total_amount}}</span>
							</div>
							<div class="text-end">
								<span class="item-date d-block mb-1">{{obj.inserted_on}}</span>
								<span ng-show="obj.status=='PAID'" class="badge-status status-paid">Paid</span>
								<span ng-show="obj.status=='DUE'" class="badge-status status-due">Due</span>
								<span ng-show="obj.status=='PARTIAL'" class="badge-status status-partial">Partial</span>
							</div>
						</li>
					</ul>
					<div class="card-footer text-center">
						<a href="<?=base_url()?>whmazadmin/order/index">View All Orders <i class="icon ion-md-arrow-forward mg-l-5"></i></a>
					</div>
				</div>
			</div>

			<!-- Support Tickets Card -->
			<div class="col-md-6 col-xl-4 mb-3" ng-init="getSupportTickets()">
				<div class="card dashboard-card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center">
							<div class="header-icon icon-success">
								<i class="fa fa-ticket-alt"></i>
							</div>
							<h6 class="mg-b-0">Support Tickets</h6>
						</div>
						<button class="refresh-btn" ng-click="getSupportTickets()">
							<i class="icon ion-md-refresh"></i>
						</button>
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item dashboard-list-item d-flex align-items-center" ng-repeat="obj in tickets track by $index">
							<div class="item-avatar avatar-ticket d-none d-sm-flex">
								<i class="fa fa-tag"></i>
							</div>
							<div class="pd-l-15 flex-grow-1">
								<p class="item-title mg-b-0">
									<a href="{{baseurl}}tickets/viewticket/{{obj.id}}">#{{obj.id}} - {{obj.title}}</a>
								</p>
								<span class="item-subtitle">{{obj.inserted_on}}</span>
							</div>
							<div class="text-end">
								<span ng-show="obj.priority==1" class="badge-priority priority-low d-block mb-1">Low</span>
								<span ng-show="obj.priority==2" class="badge-priority priority-medium d-block mb-1">Medium</span>
								<span ng-show="obj.priority==3" class="badge-priority priority-high d-block mb-1">High</span>
								<span ng-show="obj.priority==4" class="badge-priority priority-critical d-block mb-1">Critical</span>
								<span ng-show="obj.flag==1" class="badge-status status-opened">Opened</span>
								<span ng-show="obj.flag==2" class="badge-status status-answered">Answered</span>
								<span ng-show="obj.flag==3" class="badge-status status-reply">Reply</span>
								<span ng-show="obj.flag==4" class="badge-status status-closed">Closed</span>
							</div>
						</li>
					</ul>
					<div class="card-footer text-center">
						<a href="<?=base_url()?>whmazadmin/ticket/index">View All Tickets <i class="icon ion-md-arrow-forward mg-l-5"></i></a>
					</div>
				</div>
			</div>

			<!-- Recent Invoices Card -->
			<div class="col-md-6 col-xl-4 mb-3" ng-init="getRecentInvoices()">
				<div class="card dashboard-card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center">
							<div class="header-icon icon-info">
								<i class="fa fa-file-invoice-dollar"></i>
							</div>
							<h6 class="mg-b-0">Recent Invoices</h6>
						</div>
						<button class="refresh-btn" ng-click="getRecentInvoices()">
							<i class="icon ion-md-refresh"></i>
						</button>
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item dashboard-list-item d-flex align-items-center" ng-repeat="obj in invoices track by $index">
							<div class="item-avatar avatar-invoice d-none d-sm-flex">
								<i class="fa fa-file-invoice"></i>
							</div>
							<div class="pd-l-15 flex-grow-1">
								<p class="item-title mg-b-0">
									<a href="{{baseurl}}billing/view_invoice/{{obj.invoice_uuid}}">Invoice #{{obj.invoice_no}}</a>
								</p>
								<span class="item-subtitle">{{obj.currency_code}} {{obj.total}}</span>
							</div>
							<div class="text-end">
								<span class="item-date d-block mb-1">{{obj.due_date}}</span>
								<span ng-show="obj.pay_status=='PAID'" class="badge-status status-paid">Paid</span>
								<span ng-show="obj.pay_status=='DUE'" class="badge-status status-due">Due</span>
								<span ng-show="obj.pay_status=='PARTIAL'" class="badge-status status-partial">Partial</span>
							</div>
						</li>
					</ul>
					<div class="card-footer text-center">
						<a href="<?=base_url()?>whmazadmin/invoice/index">View All Invoices <i class="icon ion-md-arrow-forward mg-l-5"></i></a>
					</div>
				</div>
			</div>

		</div>

		<!-- Chart & Domain Pricing Row -->
		<div class="row row-xs">
			<!-- Expenses Chart -->
			<div class="col-sm-12 col-md-8 col-xl-8 mb-3" ng-init="getExpensesChart()">
				<div class="card dashboard-card chart-card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center">
							<div class="header-icon icon-primary">
								<i class="fa fa-chart-line"></i>
							</div>
							<h6 class="mg-b-0">Expenses (Last 12 Months)</h6>
						</div>
						<div class="d-flex align-items-center gap-2">
							<span class="chart-total-badge" ng-if="expensesData && expensesData.total > 0">
								Total: {{expensesData.total | number:2}}
							</span>
							<button class="refresh-btn" ng-click="getExpensesChart()">
								<i class="icon ion-md-refresh"></i>
							</button>
						</div>
					</div>
					<div class="card-body" style="min-height: 300px; position: relative;">
						<div ng-if="loadingExpenses" class="loading-state">
							<img src="<?=base_url()?>resources/assets/img/working.gif" />
							<p class="loading-state-text">Loading chart...</p>
						</div>
						<div ng-if="!loadingExpenses && expensesData && expensesData.total == 0" class="empty-state">
							<div class="empty-state-icon"><i class="icon ion-md-analytics"></i></div>
							<p class="empty-state-text">No expense data available</p>
						</div>
						<canvas id="expensesChart" ng-show="!loadingExpenses && expensesData && expensesData.total > 0" style="width: 100%; height: 280px;"></canvas>
					</div>
					<div class="card-footer text-center">
						<a href="<?=base_url()?>whmazadmin/expense/index">View All Expenses <i class="icon ion-md-arrow-forward mg-l-5"></i></a>
					</div>
				</div>
			</div>

			<!-- Domain Pricing -->
			<div class="col-sm-12 col-md-4 col-xl-4 mb-3" ng-init="getDomainPrices()">
				<div class="card dashboard-card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center">
							<div class="header-icon icon-primary">
								<i class="fa fa-globe"></i>
							</div>
							<h6 class="mg-b-0">Domain Pricing</h6>
						</div>
						<button class="refresh-btn" ng-click="getDomainPrices()">
							<i class="icon ion-md-refresh"></i>
						</button>
					</div>
					<div class="card-body pd-0" style="max-height: 340px; overflow-y: auto;">
						<div ng-if="loadingDomainPrices" class="loading-state">
							<img src="<?=base_url()?>resources/assets/img/working.gif" />
						</div>
						<div ng-if="!loadingDomainPrices && domainPrices.length == 0" class="empty-state">
							<div class="empty-state-icon"><i class="icon ion-md-globe"></i></div>
							<p class="empty-state-text">No domain prices configured</p>
						</div>
						<table class="table domain-pricing-table" ng-if="!loadingDomainPrices && domainPrices.length > 0">
							<thead>
								<tr>
									<th>Extension</th>
									<th class="text-end">Register</th>
									<th class="text-end">Transfer</th>
									<th class="text-end">Renewal</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="domain in domainPrices track by $index">
									<td><span class="domain-ext-badge">{{domain.extension}}</span></td>
									<td class="text-end"><span class="price-register">{{domain.currency_symbol}}{{domain.reg_price | number:2}}</span></td>
									<td class="text-end"><span class="price-transfer">{{domain.currency_symbol}}{{domain.transfer | number:2}}</span></td>
									<td class="text-end"><span class="price-renewal">{{domain.currency_symbol}}{{domain.renewal | number:2}}</span></td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="card-footer text-center">
						<a href="<?=base_url()?>whmazadmin/domain_pricing/index">Manage Domain Pricing <i class="icon ion-md-arrow-forward mg-l-5"></i></a>
					</div>
				</div>
			</div>
		</div>


	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script'); ?>
<script type="text/javascript">
	var app = angular.module('AdminDashboardApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/admindashboard_controller.js?v=1.0.0"></script>
<?php $this->load->view('whmazadmin/include/footer'); ?>
