<?php $this->load->view('templates/customer/header');?>

	<div class="content content-fixed content-wrapper" ng-app="ClientareaApp">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0 mt-2" ng-controller="ClientareaCtrl">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Client Area</li>
              </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Welcome back <b><?=getCustomerFullName()?></b></h4>
          </div>
          <div class="d-none d-md-block">
			<a href="<?=base_url()?>cart/domain/register"><button class="btn btn-sm pd-x-15 btn-white btn-uppercase"><i data-feather="globe" class="wd-10 mg-r-5"></i> Register domain</button></a>
			<a href="<?=base_url()?>cart/services/0/0"><button class="btn btn-sm pd-x-15 btn-white btn-uppercase mg-l-5"><i data-feather="printer" class="wd-10 mg-r-5"></i> Order new service</button></a>
			<a href="<?=base_url()?>tickets/newticket"><button class="btn btn-sm pd-x-15 btn-primary btn-uppercase mg-l-5"><i data-feather="tag" class="wd-10 mg-r-5"></i> Open ticket</button></a>
          </div>
        </div>

        <div class="row row-xs" ng-init="getSummaryInfo()">
          <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
              <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Services</h6>
              <div class="d-flex d-lg-block d-xl-flex align-items-end">
                <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1" id="cnt_service">
                	{{summary[0].cnt}}
                	<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if=" summary[0].cnt < 0 " style="height: 23px" />
                </h3>
              </div>
            </div>
          </div><!-- col -->
          <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
            <div class="card card-body">
              <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">Domains</h6>
              <div class="d-flex d-lg-block d-xl-flex align-items-end">
                <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1" id="cnt_domain">
                	{{summary[1].cnt}}
                	<img src="<?=base_url()?>resources/assets/img/working.gif" ng-if=" summary[1].cnt < 0 " style="height: 23px" />
                </h3>
              </div>
            </div>
          </div><!-- col -->
          <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
            <div class="card card-body">
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
            <div class="card card-body">
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
          <div class="col-md-6 col-xl-4 mg-t-10" ng-init="getSupportTickets()">
            <div class="card ht-100p">
              <div class="card-header d-flex align-items-center justify-content-between">
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
                <a href="<?=base_url()?>tickets/index" class="link-03">View all tickets <i class="icon ion-md-arrow-right mg-l-5"></i></a>
              </div><!-- card-footer -->
            </div><!-- card -->
          </div>


          <div class="col-md-6 col-xl-4 mg-t-10" ng-init="getRecentInvoices()">
            <div class="card ht-100p">
              <div class="card-header d-flex align-items-center justify-content-between">
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
                <a href="<?=base_url()?>billing/invoices" class="link-03">View all invoices <i class="icon ion-md-arrow-down mg-l-5"></i></a>
              </div><!-- card-footer -->
            </div><!-- card -->
          </div>
          
        </div><!-- row -->
      </div><!-- container -->
    </div><!-- content -->

<?php $this->load->view('templates/customer/footer_script');?>
<script type="text/javascript">
	var app = angular.module('ClientareaApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/clientarea_controller.js?v=1.0.0"></script>

<?php $this->load->view('templates/customer/footer');?>
