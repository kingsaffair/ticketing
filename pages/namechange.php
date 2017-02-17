<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Name Change page
 *
 */
 
if (!defined('IN_KA'))
	exit('Not supposed to be here!');
	
if (!$session->readSession())
	die();


if (!isset($_GET['arg']) || $_GET['arg'] == '') {

	if (time() >= $config['tickets']['namechange_end']) {
		
		redirect_to('tickets');
		die();
		
	}

	/*
	 * User is requesting a namechange
	 */
	
	$display_form = 'change';
	$error = array();
	$actual_guests = 0;
	$data = array();
	
	$tickets = $db->query($db->selectStatement('tickets', '*', 'WHERE `crsid`="' . $db->escape($session->crsid) . '" ORDER BY id ASC'));
	
	$guest_count = 0;
	
	$tids = array();
	
	while (($ticket = $db->fetchResult($tickets)) !== false) {
		
		$tids[] = $ticket['id'];
		
		$tdata = array(
					'tid'			=> $ticket['id'],
					'fname'			=> $ticket['fname'],
					'lname'			=> $ticket['lname'],
					'change'		=> false,
					);
		
		if ($ticket['primary_ticket'] == 1) {
			
			$data[0] = $tdata;
			
		} else {
			
			$guest_count += 1;
			$data[$guest_count] = $tdata;
			
		}
		
	}
	
	if (isset($_POST['sh']) && $_POST['sh'] == $session->sessionHash()) {
		
		$error_flag = false;
		
		$totalchanges = 0;
		
		for ($i = 1; $i <= $guest_count; $i++) {
		
			if (!in_array($_POST['g' . $i . '_tid'], $tids)) {
				redirect_to('error','500',500);
				die();
			}
			
			$data[$i] = array(
					'tid'			=> $_POST['g' . $i . '_tid'],
					'fname' 		=> isset($_POST['g' . $i . '_fname']) ? trim($_POST['g' . $i . '_fname']) : '',
					'lname' 		=> isset($_POST['g' . $i . '_lname']) ? trim($_POST['g' . $i . '_lname']) : '',
					'change'		=> isset($_POST['g' . $i . '_change']) && $_POST['g' . $i . '_change'] == 'true' ? true : false
					);
			
			if ($data[$i]['fname'] == '') {
				$error[$i]['fname'] = true;
				$error_flag = true;
			}
			
			if ($data[$i]['lname'] == '') {
				$error[$i]['lname'] = true;
				$error_flag = true;
			}
			
			if ($data[$i]['change'])
				$totalchanges += 1;
			
		}
		
		if (!$error_flag) {
			
			if ($totalchanges == 0) {
				
				KATemplate::displayGeneral('No name changes!', array(
					'You did not request any name changes.',
					'Click <a href="' . mask_url('tickets') . '">here</a> to return to your tickets.'));
				die();
				
			}
			
			$db->query('UPDATE ' . $db->prefix . 'tickets SET `name_change`=1 WHERE `crsid`="' . $db->escape($session->crsid) . '" AND `primary_ticket`=1');
			
			$cost = $totalchanges * $config['tickets']['namechange_cost'];
			
			$first = true;
			
			for ($i = 1; $i <= $guest_count; $i++) {
				
				if ($data[$i]['change']) {
					
					$db->queryInsert('namechange',array(
						'crsid' 			=> $session->crsid,
						'tid'				=> $data[$i]['tid'],
						'nfname'			=> $data[$i]['fname'],
						'nlname'			=> $data[$i]['lname'],
						'amount'			=> $cost,
						'paid'				=> 0,
						'primary_change'	=> $first ? 1 : 0
						));
					
					$first = false;
					
				}
				
			}
			
			$display_form = 'complete';
			
		}
		
	}
	
	if ($display_form == 'change') {
	
		$template = new KATemplate();
		
		$template->assign('page_name', 'Request a name change');
		
		$template->assign('hidereturn', true);
		
		$template->assign('session_hash', $session->sessionHash());
		$template->assign('data', $data);
		$template->assign('error', $error);
		$template->assign('guest_count', $guest_count);
		
		$template->assign('user', $session);
		
		$template->display('name_change_request.tpl');
	
	} elseif ($display_form == 'complete') {
		
		KATemplate::displayGeneral('Name change request complete', array(
			'Your name change request has been completed.',
			'In order to complete your name change, you will need to send a Bank Transfer to <strong>King&rsquo;s Affair</strong> for the amount of <strong>&pound;' . $cost . '</strong>.',
			'Please use the following Reference Code when sending your Bank Transfer: <strong>n' . $cost . '-' . $session->crsid . '</strong>.',
			'Bank Transfers should be sent to:',
			'Sort Code: 60-04-23<br />Account Number: 24175439',
			'PLEASE DOUBLE CHECK THE SORT CODE, ACCOUNT NUMBER AND REFERENCE CODE, AS IF ANY OF THESE ARE INCORRECT YOUR PAYMENT MAY BE LOST.',
			'Click <a href="' . mask_url('tickets') . '">here</a> to return to your tickets.'));
		
	}
	
	die();
	
	
} elseif ($_GET['arg'] == 'cancel') {
	
	KATemplate::displayGeneral('Confirm cancellation of name change', array(
		'Click <a href="' . mask_url('namechange','cancel-confirm') . '">here</a> to confirm that you wish to cancel your name change request.',
		'Click <a href="' . mask_url('tickets') . '">here</a> if you want to go back.'));
	die();
	
} elseif ($_GET['arg'] == 'cancel-confirm') {

	$db->query('DELETE FROM ' . $db->prefix . 'namechange WHERE `crsid`="' . $db->escape($session->crsid) . '" AND `paid`=0');
	$db->query('UPDATE ' . $db->prefix . 'tickets SET `name_change`=0 WHERE `crsid`="' . $db->escape($session->crsid) . '" AND `primary_ticket`=1');
	
	redirect_to('tickets');
	die();
	
} else {
	
	redirect_to('error','404',404);
	die();
	
}

?>
