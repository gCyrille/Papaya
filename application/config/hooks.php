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
$hook['post_controller_constructor'] = function()
{
        /* do something here */
	$CI = &get_instance();
	$CI->load->model('admin_model');
	
	$CI->load->config('vegetables');
	define("APP_VERSION", $CI->config->item('papaya_version'));
	
	$curr_page = uri_string();
	if (strstr($curr_page, 'admin') === FALSE
		&& strstr($curr_page, 'install') === FALSE
		&& $CI->admin_model->is_db_ready() === FALSE)
	{ 
		redirect('install');
	}
};
