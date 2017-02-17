<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Error Handler class file.
 *
 */

if (!defined('IN_KA'))
	exit('Not supposed to be here!');
	
// Custom Error Handler	
function errorHandler($errno, $errstr, $errfile, $errline) {
	/*
	 * 1.	Determine if our error is fatal.
	 *		If it is then log our error and exit.
	 */
	global $config;
	if ($errno == E_ERROR || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR || $errno == E_USER_ERROR) {
		// **** TODO: Log error to a file and output a message
		error_log($errstr);
		$template = new KATemplate();
		$template->assign('page_name', 'Oh no!');
		$template->assign('error_code', '500');
		$template->assign('description', array('An unexpected error occured, please go back and try again.', 'If the problem persists please contact the <a href="mailto:' . $config['general']['webmaster'] . '">webmaster</a>.'));

		$template->display('error.tpl', '500');
		die();
	}
}

// Log Intrusion
function logIntrusion($description, $user = '', $redirect = true) {
	
	global $config;
	error_log('INTRUSION:' . $description . ' (' . $user . ')');
	
	if ($redirect) {
		
		$template = new KATemplate();
		$template->assign('page_name', 'Oh no!');
		$template->assign('error_code', '500');
		$template->assign('description', array('An unexpected error occured, please go back and try again.', 'If the problem persists please contact the <a href="mailto:' . $config['general']['webmaster'] . '">webmaster</a>.'));
		
		$template->display('error.tpl', '500');
		
		die();
		
	}
	
}

set_error_handler("errorHandler");

?>