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

class TicketSale {

	public $committee_flag;
	public $user;
	public $guests_allowed;
	public $primary_type;
	public $primary_cost;
	public $guest_cost;
	public $charity_cost;
	public $college_flag;
	public $waiting_flag;
	public $premium_flag;
	public $lname_lock;
	public $payment_options;
	
	public function __construct() {
		
		global $session, $config, $db;
	
		if (($this->committee_flag = $session->getData('Tickets','CommitteeFlag')) === NULL) {
			
			$committee = $db->querySingle($db->selectStatement('committee', '*', 'WHERE `crsid`="' . $db->escape($session->crsid) . '"'));
			
			if ($committee === false) {
				// User is not a member of this committee or the last
				$this->committee_flag = 0;
			} elseif ($committee['current']) {
				// User is a member of the current committee
				$this->committee_flag = 2;
			} else {
				// User is a member of the last committee
				$this->committee_flag = 1;
			}
			
			$session->storeData('Tickets','CommitteeFlag',$this->committee_flag);
			
		}
		
		if (($this->user = $session->getData('Tickets','User')) === NULL) {
			
			$this->user = lookupIBIS($session->crsid);
			
			$session->storeData('Tickets','User',$this->user);
			
		}
		
		if ($this->user['lname'] != '')
			$this->lname_lock = true;
		else
			$this->lname_lock	= false;
		
		$this->college_flag = ($this->user['college'] == $config['general']['college']);
		
		/*
		 * Determine if the user is allowed to purchase tickets now
		 */
		
		$time_left = 0;
		$aim_zone = 0;
		
		// Current Committee Members can purchase tickets whenever they want.
		if ($this->committee_flag == 2) {
			
			$time_left = 0;
			
		} else {
			
			if (time() < $config['tickets']['college_start'] && $this->college_flag) {
				
				$time_left = $config['tickets']['college_start'] - time();
				$aim_zone = 1;
				
			} elseif (time() < $config['tickets']['college_end'] && $this->college_flag) {
				
				$time_left = 0;
				
			} elseif (time() < $config['tickets']['general_start']) {
				
				$time_left = $config['tickets']['general_start'] - time();
				
			} else {
				
				$time_left = 0;
				
			}
			
		}

		
		if ($time_left != 0) {
			// TODO :: FIX $time_left
			$time_left += 5;
			
			$template = new KATemplate();
			
			$template->assign('page_name', 'Ticket sale is not open yet!');
			
			$template->assign('hidereturn', true);
			
			$seconds = $time_left % 60;
			$minutes = intval($time_left / 60) % 60;
			$hours = intval($time_left / 3600) % 24;
			$days = intval($time_left / 86400);
			
			$template->assign('seconds', $seconds);
			$template->assign('minutes', $minutes);
			$template->assign('hours', $hours);
			$template->assign('days', $days);
			
			$template->assign('time_left', $time_left + 1);
			
			$template->assign('aim_zone', $aim_zone);
			$template->assign('college_start', $config['tickets']['college_start']);
			$template->assign('college_end', $config['tickets']['college_end']);
			$template->assign('general_start', $config['tickets']['general_start']);
			
			$template->assign('sale', $this);
			$template->assign('user', $session);
						
			$template->display('ticket_wait.tpl');
			
			die();
		}
		
		/*
		 * Settings
		 */
		
		$this->guests_allowed = 0;
		$this->primary_cost = $config['tickets']['normal_price'];
		$this->guest_cost = $config['tickets']['normal_price'];
		$this->charity_cost = $config['tickets']['charity_extra'];
		$this->premium_cost = $config['tickets']['premium_extra'];
		$this->primary_type = 'University Member Ticket';
		
		if ($this->committee_flag == 2) {
			$this->guests_allowed = 3;
			$this->primary_cost = 0;
			$this->primary_type = 'King&rsquo;s Affair Committee Ticket';
		} elseif ($this->committee_flag == 1) {
			$this->guests_allowed = 2;
			$this->primary_cost = 0;
			$this->primary_type = 'King&rsquo;s Affair Previous Committee Ticket';
		} elseif ($this->college_flag) {
			$this->guests_allowed = 2;
			$this->primary_cost = $config['tickets']['normal_price_college'];
			$this->primary_type = 'King&rsquo;s Member Ticket';
		}
        
        /* Example of how to override for specific users */
		// gideon and raph get extra guest ticket ???
		//if (in_array($session->crsid, array('rs658', 'gtf21'))) {
		//	$this->guests_allowed = 2;
		//	$this->primary_cost = -85;
		//}
		if (in_array($session->crsid, array('sec93'))) {
			$this->guests_allowed = 1;
		}

		// Cameron and Tim's music people from King's --> free kings ticket
		//if (in_array($session->crsid, array('ked37','oh231','eab71'))) {
		//	$this->primary_cost = 0;
		//}

		// (KA 2017): Louis McBride gets a free guest ticket (former committee)
		if (in_array($session->crsid, array('lom20'))) {
			$this->primary_cost = -85;
		}
		
		// (KA 2017): Claire Tatami-Siljedahl and Jenny O'Sullivan, Osh and Katherine (Finding Freddie Love)
		if (in_array($session->crsid, array('cct43', 'jlo43', 'oh231', 'ked37'))) {
			$this->primary_cost = 0;
		}

		// Double free tickets from 2x KA committees
		if (in_array($session->crsid, array('cb793'))) {
			$this->primary_cost = -85;
		}

		$this->premium_cost = $config['tickets']['premium_extra'];
        	$this->survivor_cost = $config['tickets']['survivor_photo_extra'];
		
		$this->waiting_flag = $this->getWaitingFlag();
		$this->premium_flag = $this->getPremiumFlag();
		
		$this->payment_options = array();
		
		if ($this->college_flag)
			$this->payment_options[1] = 'College Bill';
		$this->payment_options[2] = 'Bank Transfer';
		
	}
	
	public function getWaitingFlag() {
		
		global $db, $config;
		
		if ($this->committee_flag == 2)
			return false;
		
		$numberoftickets = $db->querySingle($db->selectStatement('tickets', 'count(*)', 'WHERE `waiting`=0'));
		$numberoftickets = $numberoftickets['count(*)'];
		
		if ($numberoftickets > $config['tickets']['max']) {
			return true;
		}
		return false;
		
	}
	
	public function getPremiumFlag() {
		
		global $db, $config;
		
		if ($this->committee_flag == 2)
			return false;
		
		$numberoftickets = $db->querySingle($db->selectStatement('tickets', 'count(*)', 'WHERE `waiting`=0 AND `premium`=1'));
		$numberoftickets = $numberoftickets['count(*)'];
		
		if ($numberoftickets > $config['tickets']['max_premium']) {
			return true;
		}
		return false;
		
	}
	
	
}

?>
