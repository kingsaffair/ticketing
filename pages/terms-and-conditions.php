<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Terms and Conditions page
 *
 */
 
if (!defined('IN_KA'))
	exit('Not supposed to be here!');

$session->readSession(false);

if ($session->isLoaded()) {

	$template = new KATemplate();
	
	$template->assign('page_name', 'Terms and Conditions');

	$template->assign('user', $session);
	$template->display('terms.tpl');

} else {

	$template = new KATemplate(true);
	
	$template->assign('page_name', 'Terms and Conditions');

	$template->display('terms.tpl');
}

?>