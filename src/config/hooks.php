<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

/*
| -------------------------------------------------------------------------
| post_controller_constructor
| -------------------------------------------------------------------------
| Two handlers, run in array order. CI supports a list of hooks per point --
| Hooks::call_hook() loops when the value is an array without a 'function'
| key (whmaz/core/Hooks.php) -- so do NOT collapse this back into a single
| associative array or RequestGuard stops running and every reseller silently
| regains access to the whole admin portal.
|
| 1. ErrorHandler  — SECURITY: custom PHP error/exception/fatal handling.
|                    Registered first so it is in place for anything after it.
| 2. RequestGuard  — SECURITY: admin portal tenant authorization. Blocks
|                    reseller admins from controllers outside the allowlist in
|                    src/config/capabilities.php. No-op for platform staff.
|
| This point fires after the controller constructor and before the requested
| method, which is what lets RequestGuard block a method centrally.
*/
$hook['post_controller_constructor'] = array(
	array(
		'class'    => 'ErrorHandler',
		'function' => '__construct',
		'filename' => 'ErrorHandler.php',
		'filepath' => 'hooks'
	),
	array(
		'class'    => 'RequestGuard',
		'function' => '__construct',
		'filename' => 'RequestGuard.php',
		'filepath' => 'hooks'
	),
);
