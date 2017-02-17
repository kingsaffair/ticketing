<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * User page
 *
 */
 
if (!defined('IN_KA'))
	exit('Not supposed to be here!');

if (!isset($_GET['arg'])) {
	$_GET['arg'] = 'view';
}

if ($_GET['arg'] == 'login') {

	/*
	 * 1.	Either read an existing session or try to create
	 *		a new one.
	 */
	if (!$session->createSession()) {
		trigger_error('Unable to create a new Session!', E_USER_ERROR);
		die();
	}

	/*
	 * 2.	Redirect to tickets.
	 */
	redirect_to('tickets','',302);
	
	die();
	
} elseif ($_GET['arg'] == 'logout') {
	
	/*
	 * Destroy the existing session and go back to home
	 */

	if (!$session->readSession())
		redirect_to();
	
	$session->destroySession();
	redirect_to();
	
} else {
	
	redirect_to('error', '404', 404);
	
}

 ?>