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

if ($session->readSession(false)) {
	redirect_to('tickets');
}

$template = new KATemplate(true);

$template->assign('page_name', '');

$template->assign('hidereturn', true);

$template->display('index.tpl');

 ?>