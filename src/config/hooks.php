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
| SECURITY: Custom Error Handler Hook
| -------------------------------------------------------------------------
| Registers our custom ErrorHandler class to handle PHP errors, exceptions,
| and fatal errors throughout the application.
|
| This hook runs early in the application lifecycle to catch all errors.
*/
$hook['post_controller_constructor'] = array(
	'class'    => 'ErrorHandler',
	'function' => '__construct',
	'filename' => 'ErrorHandler.php',
	'filepath' => 'hooks'
);
