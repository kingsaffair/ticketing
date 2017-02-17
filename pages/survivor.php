<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Survivor photo page
 *
 */
 
if (!defined('IN_KA'))
	exit('Not supposed to be here!');
	
if (!$session->readSession())
	die();


if (!isset($_GET['arg']) || $_GET['arg'] == '') {

	if (time() >= $config['tickets']['survivor_end']) {
		
		redirect_to('tickets');
		die();
		
	}

	/*
	 * User is requesting a survivor photo
	 */
	
	$display_form = 'change';
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
					'photo'         => false,
					);
		
		if ($ticket['primary_ticket'] == 1) {
			
			$data[0] = $tdata;
			
		} else {
			
			$guest_count += 1;
			$data[$guest_count] = $tdata;
			
		}
		
	}
	
	if (isset($_POST['sh']) && $_POST['sh'] == $session->sessionHash()) {
		
		$totalphotos = 0;
		
		for ($i = 0; $i <= $guest_count; $i++) {
            
            if ($i==0)
            {
                if (!in_array($_POST['primary_tid'], $tids)) {
                    redirect_to('error','500',500);
                    die();
                }
                $data[0] = array(
					'tid'			=> $_POST['primary_tid'],
                    'photo'         => (isset($_POST['primary_photo']) && $_POST['primary_photo'] == 'true') ? true : false
					);
            }
            else
            {
                if (!in_array($_POST['g' . $i . '_tid'], $tids)) {
                    redirect_to('error','500',500);
                    die();
                }
                
                $data[$i] = array(
					'tid'			=> $_POST['g' . $i . '_tid'],
					'photo'		=> (isset($_POST['g' . $i . '_photo']) && $_POST['g' . $i . '_photo'] == 'true') ? true : false
					);
			
			}
            
			if ($data[$i]['photo'])
				$totalphotos += 1;
			
		}
			
        if ($totalphotos == 0) {
            
            KATemplate::displayGeneral('No photos requested!', array(
                'You did not request any photos.',
                'Click <a href="' . mask_url('tickets') . '">here</a> to return to your tickets.'));
            die();
            
        }
        
        $cost = $totalphotos * $config['tickets']['survivor_photo_extra'];
        
        $db->query('UPDATE ' . $db->prefix . 'tickets SET `survivor_amount`=' . $cost . ' WHERE `crsid`="' . $db->escape($session->crsid) . '" AND `primary_ticket`=1 LIMIT 1');
        
        for ($i = 0; $i <= $guest_count; $i++) {
            
            if ($data[$i]['photo'])
            {
                $db->query('UPDATE ' . $db->prefix . 'tickets SET `survivor`=1 WHERE `id`=' . $data[$i]['tid'] . ' LIMIT 1');
            }
            
        }
        
        $display_form = 'complete';
		
	}
	
	if ($display_form == 'change') {
	
		$template = new KATemplate();
		
		$template->assign('page_name', 'Request survivors photos');
		
		$template->assign('hidereturn', true);
		
		$template->assign('session_hash', $session->sessionHash());
		$template->assign('data', $data);
		$template->assign('error', $error);
		$template->assign('guest_count', $guest_count);
		
		$template->assign('user', $session);
		
		$template->display('survivor_photo_request.tpl');
	
	} elseif ($display_form == 'complete') {
		
		KATemplate::displayGeneral('Survivor Photo request complete', array(
			'Your survivor photo request has been completed.',
			'In order to complete your photo request, you will need to send a Cheque payable to <strong>King&rsquo;s Affair</strong> with the amount <strong>&pound;' . $cost . '</strong>.',
			'Please write the following at the back of the Cheque: <strong>s' . $cost . '-' . $session->crsid . '</strong>.',
			'Cheques should be sent to:',
			'King&rsquo;s Affair<br />King&rsquo;s College<br />CB2 1ST Cambridge<br />United Kingdom',
			'Click <a href="' . mask_url('tickets') . '">here</a> to return to your tickets.'));
		
	}
	
	die();
	
	
} elseif ($_GET['arg'] == 'cancel') {
	
	KATemplate::displayGeneral('Confirm cancellation of survivors photo request', array(
		'Click <a href="' . mask_url('survivor','cancel-confirm') . '">here</a> to confirm that you wish to cancel your survivors photo request.',
		'Click <a href="' . mask_url('tickets') . '">here</a> if you want to go back.'));
	die();
	
} elseif ($_GET['arg'] == 'cancel-confirm') {

	$db->query('UPDATE ' . $db->prefix . 'tickets SET `survivor_amount`=0 WHERE `crsid`="' . $db->escape($session->crsid) . '" AND `primary_ticket`=1');
    
    $db->query('UPDATE ' . $db->prefix . 'tickets SET `survivor`=0 WHERE `crsid`="' . $db->escape($session->crsid) . '"');
	
	redirect_to('tickets');
	die();
	
} else {
	
	redirect_to('error','404',404);
	die();
	
}

?>