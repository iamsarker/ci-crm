<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper" ng-app="ClientareaApp">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ClientareaCtrl">

        <!-- Welcome Banner -->
        <div class="dashboard-welcome-banner">
            <div class="welcome-content">
                <div class="welcome-text">
                    <p class="welcome-greeting">Welcome back,</p>
                    <h2 class="welcome-name"><?= htmlspecialchars(getCustomerFullName(), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="welcome-subtitle">Manage your services, domains, and billing from your dashboard</p>
                </div>
                <div class="welcome-actions">
                    <a href="<?=base_url()?>cart/domain/register" class="btn btn-light btn-welcome">
                        <i class="fa fa-globe mg-r-5"></i> Register Domain
                    </a>
                    <a href="<?=base_url()?>cart/services/0/0" class="btn btn-light btn-welcome">
                        <i class="fa fa-server mg-r-5"></i> Order Service
                    </a>
                    <a href="<?=base_url()?>tickets/newticket" class="btn btn-welcome-primary">
                        <i class="fa fa-headset mg-r-5"></i> Open Ticket
                    </a>
                </div>
            </div>
            <div class="welcome-illustration d-none d-lg-block">
                <i class="fa fa-user-circle"></i>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row row-xs mg-t-20" ng-init="getSummaryInfo()">
            <div class="col-sm-6 col-lg-3">
                <a href="<?=base_url()?>clientarea/services" class="dashboard-stat-card stat-services">
                    <div class="stat-icon">
                        <i class="fa fa-server"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-number">
                            <span ng-if="summary[0].cnt >= 0">{{summary[0].cnt}}</span>
                            <img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[0].cnt < 0" class="stat-loader" />
                        </h3>
                        <p class="stat-label">Active Services</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
                <a href="<?=base_url()?>clientarea/domains" class="dashboard-stat-card stat-domains">
                    <div class="stat-icon">
                        <i class="fa fa-globe"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-number">
                            <span ng-if="summary[1].cnt >= 0">{{summary[1].cnt}}</span>
                            <img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[1].cnt < 0" class="stat-loader" />
                        </h3>
                        <p class="stat-label">Registered Domains</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                <a href="<?=base_url()?>tickets/index" class="dashboard-stat-card stat-tickets">
                    <div class="stat-icon">
                        <i class="fa fa-headset"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-number">
                            <span ng-if="summary[2].cnt >= 0">{{summary[2].cnt}}</span>
                            <img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[2].cnt < 0" class="stat-loader" />
                        </h3>
                        <p class="stat-label">Support Tickets</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                <a href="<?=base_url()?>billing/invoices" class="dashboard-stat-card stat-invoices">
                    <div class="stat-icon">
                        <i class="fa fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-number">
                            <span ng-if="summary[3].cnt >= 0">{{summary[3].cnt}}</span>
                            <img src="<?=base_url()?>resources/assets/img/working.gif" ng-if="summary[3].cnt < 0" class="stat-loader" />
                        </h3>
                        <p class="stat-label">Total Invoices</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity Cards -->
        <div class="row row-xs mg-t-20">
            <!-- Recent Tickets -->
            <div class="col-md-6 col-xl-6" ng-init="getSupportTickets()">
                <div class="card dashboard-activity-card">
                    <div class="card-header dashboard-card-header">
                        <h6 class="mg-b-0"><i class="fa fa-headset mg-r-10"></i>Recent Support Tickets</h6>
                        <a ng-click="getSupportTickets()" href="javascript:void(0);" class="refresh-btn" title="Refresh">
                            <i class="fa fa-sync-alt"></i>
                        </a>
                    </div>
                    <ul class="list-group list-group-flush dashboard-list">
                        <li class="list-group-item dashboard-list-item" ng-repeat="obj in tickets track by $index">
                            <div class="item-icon ticket-icon">
                                <i class="fa fa-ticket-alt"></i>
                            </div>
                            <div class="item-content">
                                <a href="{{baseurl}}tickets/viewticket/{{obj.id}}" class="item-title">#{{obj.id}} - {{obj.title}}</a>
                                <span class="item-date"><i class="fa fa-clock mg-r-5"></i>{{obj.inserted_on}}</span>
                            </div>
                            <div class="item-status">
                                <span ng-show="obj.priority==1" class="badge bg-secondary badge-sm">Low</span>
                                <span ng-show="obj.priority==2" class="badge bg-info badge-sm">Medium</span>
                                <span ng-show="obj.priority==3" class="badge bg-warning badge-sm">High</span>
                                <span ng-show="obj.priority==4" class="badge bg-danger badge-sm">Critical</span>
                                <br/>
                                <span ng-show="obj.flag==1" class="badge rounded-pill bg-success mt-1">Open</span>
                                <span ng-show="obj.flag==2" class="badge rounded-pill bg-info mt-1">Answered</span>
                                <span ng-show="obj.flag==3" class="badge rounded-pill bg-warning mt-1">Replied</span>
                                <span ng-show="obj.flag==4" class="badge rounded-pill bg-dark mt-1">Closed</span>
                            </div>
                        </li>
                        <li class="list-group-item dashboard-list-empty" ng-if="!tickets || tickets.length == 0">
                            <i class="fa fa-inbox"></i>
                            <p>No recent tickets</p>
                        </li>
                    </ul>
                    <div class="card-footer dashboard-card-footer">
                        <a href="<?=base_url()?>tickets/index" class="view-all-link">
                            View All Tickets <i class="fa fa-arrow-right mg-l-5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="col-md-6 col-xl-6 mg-t-10 mg-md-t-0" ng-init="getRecentInvoices()">
                <div class="card dashboard-activity-card">
                    <div class="card-header dashboard-card-header">
                        <h6 class="mg-b-0"><i class="fa fa-file-invoice-dollar mg-r-10"></i>Recent Invoices</h6>
                        <a ng-click="getRecentInvoices()" href="javascript:void(0);" class="refresh-btn" title="Refresh">
                            <i class="fa fa-sync-alt"></i>
                        </a>
                    </div>
                    <ul class="list-group list-group-flush dashboard-list">
                        <li class="list-group-item dashboard-list-item" ng-repeat="obj in invoices track by $index">
                            <div class="item-icon invoice-icon">
                                <i class="fa fa-file-invoice"></i>
                            </div>
                            <div class="item-content">
                                <a href="{{baseurl}}billing/view_invoice/{{obj.invoice_uuid}}" class="item-title">Invoice #{{obj.invoice_no}}</a>
                                <span class="item-amount"><strong>{{obj.currency_code}}</strong> {{obj.total}}</span>
                            </div>
                            <div class="item-status">
                                <span class="item-due-date"><i class="fa fa-calendar mg-r-5"></i>{{obj.due_date}}</span>
                                <br/>
                                <span ng-show="obj.pay_status=='PAID'" class="badge rounded-pill bg-success mt-1">Paid</span>
                                <span ng-show="obj.pay_status=='DUE'" class="badge rounded-pill bg-danger mt-1">Due</span>
                                <span ng-show="obj.pay_status=='PARTIAL'" class="badge rounded-pill bg-warning mt-1">Partial</span>
                            </div>
                        </li>
                        <li class="list-group-item dashboard-list-empty" ng-if="!invoices || invoices.length == 0">
                            <i class="fa fa-inbox"></i>
                            <p>No recent invoices</p>
                        </li>
                    </ul>
                    <div class="card-footer dashboard-card-footer">
                        <a href="<?=base_url()?>billing/invoices" class="view-all-link">
                            View All Invoices <i class="fa fa-arrow-right mg-l-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<script type="text/javascript">
	var app = angular.module('ClientareaApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/clientarea_controller.js?v=1.0.0"></script>

<?php $this->load->view('templates/customer/footer');?>
