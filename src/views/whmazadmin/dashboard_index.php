<?php $this->load->view('whmazadmin/include/header'); ?>

<div class="content content-fixed content-wrapper" ng-app="AdminDashboardApp">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="AdminDashboardCtrl">
		<div class="d-sm-flex align-items-center justify-content-between mb-2">
			<div>
				<h4 class="mg-b-0 tx-spacing--1">Welcome back <b><?=getAdminFullName()?></b></h4>
			</div>
			<div class="d-none d-md-block">
				<a href="<?=base_url()?>whmazadmin/company/manage"><button class="btn btn-sm pd-x-15 btn-white btn-uppercase mg-l-5"><i data-feather="user-plus" class="wd-10 mg-r-5"></i> New customer</button></a>
				<a href="<?=base_url()?>whmazadmin/order/manage"><button class="btn btn-sm pd-x-15 btn-primary btn-uppercase mg-l-5"><i data-feather="plus-square" class="wd-10 mg-r-5"></i> New order</button></a>
			</div>
		</div>

		<div class="row row-xs" ng-init="getSummaryInfo()">
			<div class="col-sm-6 col-lg-3">
				<div class="card card-body bg-success-light">
					<h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Customers</h6>
					<div class="d-flex d-lg-block d-xl-flex align-items-end">
						<h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1" id="cnt_service">
							{{summary[0].cnt}}
							<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if=" summary[0].cnt < 0 " style="height: 23px" />
						</h3>
					</div>
				</div>
			</div><!-- col -->
			<div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
				<div class="card card-body bg-success-light">
					<h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Orders</h6>
					<div class="d-flex d-lg-block d-xl-flex align-items-end">
						<h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1" id="cnt_domain">
							{{summary[1].cnt}}
							<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if=" summary[1].cnt < 0 " style="height: 23px" />
						</h3>
					</div>
				</div>
			</div><!-- col -->
			<div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
				<div class="card card-body bg-success-light">
					<h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Tickets</h6>
					<div class="d-flex d-lg-block d-xl-flex align-items-end">
						<h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1" id="cnt_ticket">
							{{summary[2].cnt}}
							<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if=" summary[2].cnt < 0 " style="height: 23px" />
						</h3>
					</div>
				</div>
			</div><!-- col -->
			<div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
				<div class="card card-body bg-success-light">
					<h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Invoices</h6>
					<div class="d-flex d-lg-block d-xl-flex align-items-end">
						<h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1" id="cnt_invoice">
							{{summary[3].cnt}}
							<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if=" summary[3].cnt < 0 " style="height: 23px" />
						</h3>
					</div>
				</div>
			</div><!-- col -->
		</div>


		<div class="row row-xs">

			<div class="col-md-6 col-xl-4 mg-t-10" ng-init="getPendingOrders()">
				<div class="card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between bg-warning-light">
						<h6 class="mg-b-0">Pending orders</h6>
						<div class="d-flex align-items-center tx-18">
							<a href="#" ng-click="getPendingOrders()" class="link-03 lh-0"><i class="icon ion-md-refresh"></i></a>
						</div>
					</div>
					<ul class="list-group list-group-flush tx-13">
						<li class="list-group-item d-flex pd-sm-x-20" ng-repeat="obj in orders track by $index">
							<div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-info"><i class="icon fa fa-shopping-cart"></i></span></div>
							<div class="pd-l-10">
								<p class="tx-medium mg-b-0"><a href="{{baseurl}}whmazadmin/order/view_order/{{obj.order_uuid}}">Order #{{obj.order_no}}</a></p>
								<small class="tx-12 tx-color-03 mg-b-0">Amount {{obj.currency_code}} {{obj.total_amount}}</small>
							</div>
							<div class="mg-l-auto text-right">
								<p class="tx-medium mg-b-0">
									<span class="badge badge-pill">{{obj.inserted_on}}</span>
								</p>
								<small class="tx-12 tx-success mg-b-0">
									<span ng-show="obj.status=='PAID'" class="badge rounded-pill bg-success">Paid</span>
									<span ng-show="obj.status=='DUE'" class="badge rounded-pill bg-danger">Due</span>
									<span ng-show="obj.status=='PARTIAL'" class="badge rounded-pill bg-warning">Partial</span>
								</small>
							</div>
						</li>

					</ul>
					<div class="card-footer text-center tx-13">
						<a href="<?=base_url()?>whmazadmin/order/index" class="link-03">View all orders <i class="icon ion-md-arrow-down mg-l-5"></i></a>
					</div><!-- card-footer -->
				</div><!-- card -->
			</div>

			<div class="col-md-6 col-xl-4 mg-t-10" ng-init="getSupportTickets()">
				<div class="card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between bg-warning-light">
						<h6 class="mg-b-0">Recent support tickets</h6>
						<div class="d-flex tx-18">
							<a ng-click="getSupportTickets()" href="#" class="link-03 lh-0"><i class="icon ion-md-refresh"></i></a>
						</div>
					</div>
					<ul class="list-group list-group-flush tx-13">

						<li class="list-group-item d-flex pd-sm-x-20" ng-repeat="obj in tickets track by $index">
							<div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-teal"><i class="icon fa fa-tag"></i></span></div>
							<div class="pd-sm-l-10">
								<p class="tx-medium mg-b-0"><a href="{{baseurl}}tickets/viewticket/{{obj.id}}">#{{obj.id}} - {{obj.title}}</a></p>
								<small class="tx-12 tx-color-03 mg-b-0">{{obj.inserted_on}}</small>
							</div>
							<div class="mg-l-auto text-right">
								<p class="tx-medium mg-b-0">
									<span ng-show="obj.priority==1" class="badge badge-pill">Low</span>
									<span ng-show="obj.priority==2" class="badge badge-pill">Medium</span>
									<span ng-show="obj.priority==3" class="badge badge-pill">High</span>
									<span ng-show="obj.priority==4" class="badge badge-pill">Critical</span>
								</p>
								<small class="tx-12 tx-success mg-b-0">
									<span ng-show="obj.flag==1" class="badge rounded-pill bg-success">Opened</span>
									<span ng-show="obj.flag==2" class="badge rounded-pill bg-info">Answered</span>
									<span ng-show="obj.flag==3" class="badge rounded-pill bg-warning">Customer reply</span>
									<span ng-show="obj.flag==4" class="badge rounded-pill bg-dark">Closed</span>
								</small>
							</div>
						</li>

					</ul>
					<div class="card-footer text-center tx-13">
						<a href="<?=base_url()?>whmazadmin/ticket/index" class="link-03">View all tickets <i class="icon ion-md-arrow-right mg-l-5"></i></a>
					</div><!-- card-footer -->
				</div><!-- card -->
			</div>


			<div class="col-md-6 col-xl-4 mg-t-10" ng-init="getRecentInvoices()">
				<div class="card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between bg-warning-light">
						<h6 class="mg-b-0">Recent invoices</h6>
						<div class="d-flex align-items-center tx-18">
							<a href="#" ng-click="getRecentInvoices()" class="link-03 lh-0"><i class="icon ion-md-refresh"></i></a>
						</div>
					</div>
					<ul class="list-group list-group-flush tx-13">
						<li class="list-group-item d-flex pd-sm-x-20" ng-repeat="obj in invoices track by $index">
							<div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-info"><i class="icon fa fa-file-invoice"></i></span></div>
							<div class="pd-l-10">
								<p class="tx-medium mg-b-0"><a href="{{baseurl}}billing/view_invoice/{{obj.invoice_uuid}}">Invoice #{{obj.invoice_no}}</a></p>
								<small class="tx-12 tx-color-03 mg-b-0">Amount {{obj.currency_code}} {{obj.total}}</small>
							</div>
							<div class="mg-l-auto text-right">
								<p class="tx-medium mg-b-0">
									<span class="badge badge-pill">{{obj.due_date}}</span>
								</p>
								<small class="tx-12 tx-success mg-b-0">
									<span ng-show="obj.pay_status=='PAID'" class="badge rounded-pill bg-success">Paid</span>
									<span ng-show="obj.pay_status=='DUE'" class="badge rounded-pill bg-danger">Due</span>
									<span ng-show="obj.pay_status=='PARTIAL'" class="badge rounded-pill bg-warning">Partial</span>
								</small>
							</div>
						</li>

					</ul>
					<div class="card-footer text-center tx-13">
						<a href="<?=base_url()?>whmazadmin/invoice/index" class="link-03">View all invoices <i class="icon ion-md-arrow-down mg-l-5"></i></a>
					</div><!-- card-footer -->
				</div>
			</div>

		</div>

		<div class="row row-xs">
			<div class="col-sm-12 col-md-8 col-xl-8 mt-3" ng-init="getRecentInvoices()">
				<div class="card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between bg-warning-light">
						<h6 class="mg-b-0">Last 12 months expenses</h6>
						<div class="d-flex align-items-center tx-18">
							<a href="#" ng-click="getRecentInvoices()" class="link-03 lh-0"><i class="icon ion-md-refresh"></i></a>
						</div>
					</div>
					<div class="card-body">
						Column chart
					</div>
				</div>
			</div>

			<div class="col-sm-12 col-md-4 col-xl-4 mt-3" ng-init="getRecentInvoices()">
				<div class="card ht-100p">
					<div class="card-header d-flex align-items-center justify-content-between bg-warning-light">
						<h6 class="mg-b-0">Domain selling prices</h6>
						<div class="d-flex align-items-center tx-18">
							<a href="#" ng-click="getRecentInvoices()" class="link-03 lh-0"><i class="icon ion-md-refresh"></i></a>
						</div>
					</div>

					<div class="card-body">
						List
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
