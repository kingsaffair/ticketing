<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Tickets page
 *
 */

if (!defined('IN_KA'))
	exit('Not supposed to be here!');
	
if (!$session->readSession())
	die();
	
include(INCLUDE_PATH . 'includes/tickets.php');

/*
 * 1. Determine if the user has bought any tickets.
 */ 

if (($has_ticket = $session->getData('Tickets','HasTicket')) === NULL) {
	$tickets = $db->query($db->selectStatement('tickets', '*', 'WHERE `crsid`="' . $db->escape($session->crsid) . '"'));
	
	$has_ticket = ($db->numResults($tickets) == 0);
	
	$session->storeData('Tickets','HasTicket',$has_ticket);
}

if ($has_ticket) {

	if (!$config['tickets']['enabled']) {
		KATemplate::displayGeneral('Ticket Sale Closed', array(
			'Ticket sale is closed at the moment.',
			'If you believe this is an error please contact our <a href="mailto:' . $config['general']['webmaster'] . '">webmaster</a>.'));
			die();
	}
	
	$ticket_sale = new TicketSale();
	
	/*
	 * 1. Is this a $_POST request with data?
	 */
	
	$display_form = 'book';
	$error = array();
	$actual_guests = 0;
	$data = array();
	
	if (isset($_POST['sh'])) {
		
		if ($_POST['sh'] == $session->sessionHash()) {
				
			if (isset($_POST['ch'])) {
				
				/*
				 * Confirmer
				 */
				
				if (isset($_POST['confirm']) && $_POST['confirm'] == 'Confirm') {
					
					if (isset($_POST['toc']) && $_POST['toc'] == 'true') {
						
						/*
						 * Insert!
						 */
						
						/*
						* We need to ignore user aborts for this script.
						*/
						
						ignore_user_abort(true);
						set_time_limit(0);
						
						$data = $session->getDataR('Tickets','Data');
						$actual_guests = $session->getDataR('Tickets','Guest_Count');
						
						/*
						 * Calculate everything first
						 */
						$cost = 0;
						for ($i = 0; $i <= $actual_guests; $i++) {
							if ($i == 0)
								$cost += $ticket_sale->primary_cost;
							else
								$cost += $ticket_sale->guest_cost;
								
							if ($data[$i]['ticket_type'] == 1) {
								if (!$ticket_sale->premium_flag) {
									$cost += $ticket_sale->premium_cost;
								} else {
									$error['premium'] = true;
									$data[$i]['ticket_type'] = 0;
								}
							}
							
							if ($data['charity'])
								$cost += $ticket_sale->charity_cost;
						}
						
						/*
						 * Lock the tables
						 */
						$db->execute('LOCK TABLES ' . $db->prefix . 'tickets WRITE;');
						
						/*
						 * Refresh Flags
						 *
						 * Note: I have chosen to enforce premium flags 'weakly' and waiting flags strongly
						 */
						$temp = $db->query($db->selectStatement('tickets', '*', 'WHERE `crsid`="' . $db->escape($session->crsid) . '"'));
						
						if ($db->numResults($temp) != 0) {
							redirect_to();
							die();
						}
						
						$ticket_sale->waiting_flag = $ticket_sale->getWaitingFlag();
						
						if ($_POST['ch'] == 'c' && $ticket_sale->waiting_flag) {
							$error['waiting'] = true;
						}
						
						for ($i = 0; $i <= $actual_guests; $i++) {
							$db->queryInsert('tickets',array(
									'crsid' 			=> $session->crsid,
									'fname'				=> $data[$i]['fname'],
									'lname'				=> $data[$i]['lname'],
									'waiting'			=> ($ticket_sale->waiting_flag ? 1 : 0),
									'premium'			=> $data[$i]['ticket_type'],
									'charity'			=> ($data['charity'] ? 1 : 0),
									'primary_ticket'	=> ($i == 0 ? 1 : 0),
									'created'			=> time(),
									'amount'			=> ($i == 0 ? $cost : null),
									'payment_method'	=> ($i == 0 ? $data['payment_method'] : null),
									'name_change'		=> 0,
									'song_choice'		=> $data['song_choice'],
                                    'committee'         => (($ticket_sale->committee_flag == 2) ? 3 : $ticket_sale->committee_flag)
									));
						}
						
						// Release the lock
						$db->execute('UNLOCK TABLES');
						
						$data['amount'] = $cost;
						
						/*
						 * Prepare the email
						 */
						if (!$ticket_sale->waiting_flag) {
							
							$message = '** This is an automated email.  Please do not reply. **' . "\n\n";
							
							$message .= 'Thank you for your booking with The King\'s Affair 2017. Below is an' . "\n";
							$message .= 'email confirmation of the tickets which you have booked.' . "\n\n";
							
							for ($i = 0; $i <= $actual_guests; $i++) {
								if ($i == 0) {
									$message .= 'PRIMARY TICKET' . "\n\n";
								} else {
									$message .= 'GUEST ' . $i . ' TICKET' . "\n\n";
								}
								$message .= '    Name:   ' . $data[$i]['fname'] . ' ' . $data[$i]['lname'] . "\n";
								$message .= '    Type:   ' . ($i == 0 && $ticket_sale->committee_flag == 2 ? 'Committee Ticket' : ($data[$i]['ticket_type'] == 1 ? 'QueueJump Ticket' : 'Normal Ticket')) . "\n";
								$message .= "\n\n";
								
							}
							
							$message .= 'The total cost for this order is £' . $data['amount'] . ".\n\n";
							
							if ($data['payment_method'] == 2) {
								
								$message .= 'As you have chosen payment via Bank Transfer, you will need to send a Bank Transfer' . "\n";
								$message .= 'to King\'s Affair for the amount of £' . $data['amount'] . " within 10 days.\n\n";
								$message .= 'Please use the following Reference Code when sending your Bank Transfer :' . "\n\n";
								$message .= '   t' . $data['amount'] . '-' . $session->crsid . "\n\n";
								$message .= 'Bank Transfers should be sent to:' . "\n\n";
								$message .= '   Sort Code: 60-04-23' . "\n";
								$message .= '   Account Number: 24175439' . "\n\n";
								$message .= 'PLEASE DOUBLE CHECK THE SORT CODE, ACCOUNT NUMBER AND REFERENCE CODE,' . "\n";
								$message .= 'AS IF ANY OF THESE ARE INCORRECT YOUR PAYMENT MAY BE LOST.' . "\n\n\n";
								
							}
							
							$message .= 'Details can be found on the website at' . "\n";
							$message .= 'http://www.kingsaffair.com/tickets/';
							
							mail($session->crsid . '@cam.ac.uk', 'King\'s Affair 2017 -- Booking Confirmation', $message, 'From: King\'s Affair 2017 <no-reply@kingsaffair.com>');
							
						}
						
						$session->storeData('Tickets','HasTicket',false);
						
						$display_form = 'complete';
						
					} else {
						
						$display_form = 'confirm';
						
						$data = $session->getData('Tickets','Data');
						$actual_guests = $session->getData('Tickets','Guest_Count');
						
						$error['toc'] = true;
						
					}
					
				} else {
					
					$data = $session->getData('Tickets','Data');
					$actual_guests = $session->getData('Tickets','Guest_Count');
					
				}
				
			} elseif ($_POST['p_crsid'] == $session->crsid) {
				
				/*
				 * Verifier
				 */
				
				$cost = 0;
				$error_flag = false;
				
				$check_guests = $ticket_sale->guests_allowed;
				
				if (isset($_POST['js']) && $_POST['js'] && isset($_POST['guest_select'])) {
					$check_guests = intval($_POST['guest_select']);
				}
				
				if ($check_guests > $ticket_sale->guests_allowed) {
					$check_guests = $ticket_sale->guests_allowed;
				}
				
				/*
				 * Check Primary Ticket
				 */
				$data[0] = array(
						'fname'			=> isset($_POST['p_fname']) ? trim($_POST['p_fname']) : '',
						'lname'			=> $ticket_sale->lname_lock ? $ticket_sale->user['lname'] : (isset($_POST['p_lname']) ? trim($_POST['p_lname']) : ''),
						'ticket_type'	=> isset($_POST['p_ticket_type']) ? intval($_POST['p_ticket_type']) : NULL,
						'age_check'		=> isset($_POST['p_age_check']) ? ($_POST['p_age_check'] == 'true') : NULL
						);
				$error[0] = array();
				
				if ($data[0]['fname'] == '') {
					$error[0]['fname'] = true;
					$error_flag = true;
				}
				
				if ($data[0]['lname'] == '') {
					$error[0]['lname'] = true;
					$error_flag = true;
				}
				
				if ($data[0]['ticket_type'] === NULL) {
					$error[0]['ticket_type'] = true;
					$error_flag = true;
				}
				
				if ($data[0]['age_check'] !== true) {
					$error[0]['age_check'] = true;
					$error_flag = true;
				}
				
				/*
				 * Check guest tickets
				 */
				
				for ($i = 1; $i <= $check_guests; $i++) {
					
					$guest_data = array(
							'fname' 		=> isset($_POST['g' . $i . '_fname']) ? trim($_POST['g' . $i . '_fname']) : '',
							'lname' 		=> isset($_POST['g' . $i . '_lname']) ? trim($_POST['g' . $i . '_lname']) : '',
							'ticket_type'	=> isset($_POST['g' . $i . '_ticket_type']) ? intval($_POST['g' . $i . '_ticket_type']) : NULL,
							'age_check' 	=> isset($_POST['g' . $i . '_age_check']) ? ($_POST['g' . $i . '_age_check'] == 'true') : NULL
							);
					
					if ($guest_data['fname'] != '' || $guest_data['lname'] != '') {
						$actual_guests += 1;
						$data[$actual_guests] = $guest_data;
						$error[$actual_guests] = array();
						
						if ($guest_data['fname'] == '') {
							$error[$actual_guests]['fname'] = true;
							$error_flag = true;
						}
						
						if ($guest_data['lname'] == '') {
							$error[$actual_guests]['lname'] = true;
							$error_flag = true;
						}
						
						if ($guest_data['ticket_type'] === NULL) {
							$error[$actual_guests]['ticket_type'] = true;
							$error_flag = true;
						}
						
						if ($guest_data['age_check'] !== true) {
							$error[$actual_guests]['age_check'] = true;
							$error_flag = true;
						}
						
					}
					
				}
				
				/*
				 * Check payment options
				 */
				
				$data['charity'] = (isset($_POST['charity']) && $_POST['charity'] == 'true') ? true : false;
				$data['payment_method'] = isset($_POST['payment_method']) ? intval($_POST['payment_method']) : 0;
				$data['song_choice'] = isset($_POST['song_choice']) ? trim($_POST['song_choice']) : '';
				
				if ($data['payment_method'] != 1 && $data['payment_method'] != 2) {
					$error['payment_method'] = true;
					$error_flag = true;
				}
				
				if (!$error_flag) {
					
					/*
					 * Confirmation
					 */
					
					$display_form = 'confirm';
					
					/*
					 * Store the data in our session.
					 */
					$session->storeData('Tickets','Data',$data);
					$session->storeData('Tickets','Guest_Count',$actual_guests);
					
				}
				
			}
			
		}
		
	}
	
	if ($display_form == 'book') {
		
		$template = new KATemplate();
		
		if ($ticket_sale->waiting_flag) {
			$template->assign('page_name', 'Join the waiting list');
			$template->assign('description', 'Unfortunately our tickets are sold out. Please fill in the details below for the waiting list.');
		} else {
			$template->assign('page_name', 'Book your tickets!');
			$template->assign('description', 'Please fill in your details below to book your tickets for the ' . $config['general']['name'] . '.');
		}
		
		$template->assign('hidereturn', true);
		
		$template->assign('session_hash', $session->sessionHash());
		$template->assign('sale', $ticket_sale);
		$template->assign('data', $data);
		$template->assign('error', $error);
		$template->assign('guests_enabled', $actual_guests);
		
		$template->assign('user', $session);
		
		$template->display('ticket_book.tpl');
		
	} elseif ($display_form == 'confirm') {
	
		/*
		 * Generate an invoice break down
		 */
		$total = 0;
		$invoice = array();
		
		for ($i = 0; $i <= $actual_guests; $i++) {
			
			$subitems = array();
			$subcost = 0;
			
			if ($i == 0) {
				$subitems[] = array(
							'item'		=> $ticket_sale->primary_type,
							'price'		=> $ticket_sale->primary_cost
							);
				$subcost += $ticket_sale->primary_cost;
			} else {
				$subitems[] = array(
							'item'		=> 'Guest Ticket',
							'price'		=> $ticket_sale->guest_cost
							);
				$subcost += $ticket_sale->guest_cost;
			}
			
			if ($data[$i]['ticket_type'] == 1) {
				$subitems[] = array(
							'item'		=> 'QueueJump Ticket upgrade',
							'price'		=> $ticket_sale->premium_cost
							);
				$subcost += $ticket_sale->premium_cost;
			}
			
			if ($data['charity']) {
				$subitems[] = array(
							'item'		=> 'Charity Donation',
							'price'		=> $ticket_sale->charity_cost
							);
				$subcost += $ticket_sale->charity_cost;
			}
			
			$invoice[] = array(
						'item'		=> ($i == 0 ? 'Primary Ticket' : 'Guest ' . $i . ' Ticket'),
						'price'		=> $subcost,
						'subitems'	=> $subitems
						);
			
			$total += $subcost;
			
		}
		
		
		/*
		 * We then have to display the confirmation page.
		 */
		
		$template = new KATemplate();
		
		if ($ticket_sale->waiting_flag) {
			$template->assign('page_name', 'Confirm your details for the waiting list');
			$template->assign('description', 'As we have sold out on tickets, please confirm your details below to join the waiting list.');
		} else {
			$template->assign('page_name', 'Confirm your booking!');
			$template->assign('description', 'Please confirm your details below to book your tickets.');
		}
		
		$template->assign('hidereturn', true);
		
		$template->assign('session_hash', $session->sessionHash());
		$template->assign('sale', $ticket_sale);
		
		$template->assign('data', $data);
		$template->assign('error', $error);
		$template->assign('guest_count', $actual_guests);
		
		$template->assign('invoice', $invoice);
		$template->assign('total', $total);
		
		$template->assign('user', $session);
		
		$template->display('ticket_confirm.tpl');
		
	} elseif ($display_form == 'complete') {
		
		$message = array();
		
		if ($ticket_sale->waiting_flag) {
			if (isset($error['waiting']))
				$message[] = 'Unfortunately we have sold out on tickets and you were added to the waiting list.';
			else
				$message[] = 'We have added your tickets to the waiting list.';
			
			$message[] = 'We will send you an email when your tickets become available.';
			$message[] = 'Click <a href="' . mask_url('tickets') . '">here</a> to view your booking details.';
			
			KATemplate::displayGeneral('Added to the waiting list!', $message);
		} else {
			$message[] = 'Thank you for booking your tickets for the ' . $config['general']['name'];
			if (isset($error['premium']))
				$message[] = 'Unforunately we sold out on QueueJump tickets and your tickets have been changed to normal ones.';
			if ($data['payment_method'] == 2) {
				$message[] = 'As you have chosen payment via Bank Transfer, you will need to send a Bank Transfer payable to <strong>King&rsquo;s Affair</strong> with the amount <strong>&pound;' . $data['amount'] . '</strong>.';
				$message[] = 'This needs to be done <strong>within 10 days</strong> of the day you booked your ticket.';
				$message[] = 'Please use the following  Reference Code when sending your Bank Transfer: <strong>t' . $data['amount'] . '-' . $session->crsid . '</strong>.';
				$message[] = 'Bank Transfers should be sent to:';
				$message[] = 'Sort Code: 60-04-23<br />Account Number: 24175439';
				$message[] = 'PLEASE DOUBLE CHECK THE SORT CODE, ACCOUNT NUMBER AND REFERENCE CODE, AS IF ANY OF THESE ARE INCORRECT YOUR PAYMENT MAY BE LOST.';
			}
			$message[] = 'Click <a href="' . mask_url('tickets') . '">here</a> to view your booking details.';
			
			KATemplate::displayGeneral('Booking Complete!', $message);
		}
		
	}
	
} else {

	/*
	 * Ticket Status
	 */
	
	if (($committee_flag = $session->getData('Tickets','CommitteeFlag')) === NULL) {
		
		$committee = $db->querySingle($db->selectStatement('committee', '*', 'WHERE `crsid`="' . $db->escape($session->crsid) . '"'));
		
		if ($committee === false) {
			// User is not a member of this committee or the last
			$committee_flag = 0;
		} elseif ($committee['current']) {
			// User is a member of the current committee
			$committee_flag = 2;
		} else {
			// User is a member of the last committee
			$committee_flag = 1;
		}
		
		$session->storeData('Tickets','CommitteeFlag',$committee_flag);
		
	}
	
	$data = array();
	$tickets = $db->query($db->selectStatement('tickets', '*', 'WHERE `crsid`="' . $db->escape($session->crsid) . '" ORDER BY id ASC'));
	
	$guest_count = 0;
	
	while (($ticket = $db->fetchResult($tickets)) !== false) {
		
		$tdata = array(
					'fname'			=> $ticket['fname'],
					'lname'			=> $ticket['lname'],
					'ticket_type'	=> $ticket['premium'],
                    'survivor'      => $ticket['survivor']
					);
		
		if ($ticket['primary_ticket'] == 1) {
			
			$data[0] = $tdata;
			
			$data['waiting'] = $ticket['waiting'];
			$data['charity'] = $ticket['charity'];
			$data['created'] = $ticket['created'];
			$data['amount']  = $ticket['amount'];
			$data['payment_method'] = $ticket['payment_method'];
			$data['paid'] = $ticket['paid'];
			$data['collected'] = $ticket['collected'];
			$data['entered'] = $ticket['entered'];
			$data['name_change'] = $ticket['name_change'];
            $data['survivor'] = (($ticket['survivor_amount'] != 0) ? (($ticket['survivor_paid'] != 0) ? 'paid' : 'pending') : 'none');
            
            if ($data['survivor'] == 'pending')
            {
                $data['survivor_photo_amount'] = $ticket['survivor_amount'];
            }
			
		} else {
			
			$guest_count += 1;
			$data[$guest_count] = $tdata;
			
		}
		
	}
	
	if ($data['name_change']) {
		
		/*
		 * Get namechange details
		 */
		
		$namechange = $db->querySingle($db->selectStatement('namechange', 'amount', 'WHERE `crsid`="' . $db->escape($session->crsid) . '" AND `paid`=0 AND `primary_change`=1'));
		$data['name_change_amount'] = $namechange['amount'];
		
	}
	
	$template = new KATemplate();
	
	$template->assign('hidereturn', true);
	
	$template->assign('nc_enabled', (time() < $config['tickets']['namechange_end']));
    $template->assign('sp_enabled', (time() < $config['tickets']['survivor_end']));
	$template->assign('data', $data);
	$template->assign('guest_count', $guest_count);
	$template->assign('user', $session);
	$template->assign('committee_flag', $committee_flag);
	
	$template->display('ticket_details.tpl');

}

?>
