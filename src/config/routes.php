<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['billing'] = FALSE;
$route['change-currency/(:any)/(:any)'] = 'auth/change_currency/$1/$2';
$route['domain-search'] = 'cart/domain_search';
$route['domain-suggestion'] = 'cart/get_domain_suggestions';
$route['pages/(:any)'] = 'pages/index/$1';

// Third-party REST API (module `api`). Versioned URLs map to the api module:
//   /api/v1/domains/check -> api/domains/check
// Specific shortcuts first, then the versioned catch-all.
// ── Third-party REST API (module `api`) ──────────────────────────────────
// Every API controller is Api-prefixed (ApiDomains, ApiHosting, …). The public
// URLs stay resource-friendly (/api/v1/domains/…) and each maps to the exact
// controller class case below. No generic catch-all: routing every resource
// explicitly avoids CI's lowercase segment→class resolution mangling camelCase
// controller names. Two-segment routes precede one-segment for each resource.
$route['api/v1/ping']     = 'api/ApiSystem/ping';
$route['api/v1/me']       = 'api/ApiSystem/me';
$route['api/v1/checkout'] = 'api/ApiCheckout/index';

$route['api/v1/cart']                  = 'api/ApiCart/index';
$route['api/v1/cart/(:any)/(:any)']    = 'api/ApiCart/$1/$2';
$route['api/v1/cart/(:any)']           = 'api/ApiCart/$1';

$route['api/v1/customers']                 = 'api/ApiCustomers/index';
$route['api/v1/customers/(:any)/(:any)']   = 'api/ApiCustomers/$1/$2';
$route['api/v1/customers/(:any)']          = 'api/ApiCustomers/$1';

$route['api/v1/currencies']                = 'api/ApiProducts/currencies';
$route['api/v1/billing_cycles']            = 'api/ApiProducts/cycles';
$route['api/v1/products/(:any)']           = 'api/ApiProducts/$1';

$route['api/v1/domains']                   = 'api/ApiDomains/index';
$route['api/v1/domains/(:any)/(:any)']     = 'api/ApiDomains/$1/$2';
$route['api/v1/domains/(:any)']            = 'api/ApiDomains/$1';

$route['api/v1/hosting']                   = 'api/ApiHosting/index';
$route['api/v1/hosting/(:any)/(:any)']     = 'api/ApiHosting/$1/$2';
$route['api/v1/hosting/(:any)']            = 'api/ApiHosting/$1';

$route['api/v1/orders']                    = 'api/ApiOrder/index';
$route['api/v1/orders/(:any)/(:any)']      = 'api/ApiOrder/$1/$2';
$route['api/v1/orders/(:any)']             = 'api/ApiOrder/$1';

$route['api/v1/invoices']                  = 'api/ApiInvoices/index';
$route['api/v1/invoices/(:any)/(:any)']    = 'api/ApiInvoices/$1/$2';
$route['api/v1/invoices/(:any)']           = 'api/ApiInvoices/$1';

$route['api/v1/licenses']                  = 'api/ApiLicenses/index';
$route['api/v1/licenses/(:any)/(:any)']    = 'api/ApiLicenses/$1/$2';
$route['api/v1/licenses/(:any)']           = 'api/ApiLicenses/$1';

