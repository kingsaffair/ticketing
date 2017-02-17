<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Index page
 *
 */
 
if (!defined('IN_KA'))
	exit('Not supposed to be here!');

$session->readSession(false);

$template = new KATemplate(true);

$template->assign('page_name', 'Uh oh!');

if ($session->isLoaded()) {
	$template->assign('user', $session);
}

$error_code = '404';

if (isset($_GET['arg'])) {
	$error_code = $_GET['arg'];
}

$error_descriptions = array(
	'404'	=> 'The requested page could not be found, please go back or return to the <a href="' . mask_url('') . '">home page</a>.',
	'500'	=> 'There was an internal server error. Please try again.'
);

if (!array_key_exists($error_code, $error_descriptions))
	$error_code = '404';

if (!is_array($error_descriptions[$error_code]))
	$error_descriptions[$error_code] = array($error_descriptions[$error_code]);

$error_descriptions[$error_code][] = 'If the problem persists please contact the <a href="mailto:' . $config['general']['webmaster'] . '">webmaster</a>.';

$template->assign('error_code', $error_code);
$template->assign('description', $error_descriptions[$error_code]);

$template->display('error.tpl', $error_code);

?>