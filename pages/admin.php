<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Admin page
 *
 */
 
if (!defined('IN_KA'))
	exit('Not supposed to be here!');

if (!$session->readSession())
	die();

if ($session->committee_flag != 2)
	redirect_to('error','404',404);

if ($_GET['arg'] == '') {

	/*
	 * Get stats
	 */
	
	$totals = array();
	
	// Tickets Sold
	if (($r = $db->querySingle($db->selectStatement('tickets','count(*) c','WHERE `waiting`=0'))) === false)
		redirect_to('error','500');
	$totals['total'] = $r['c'];
	
	// Tickets Waiting
	if (($r = $db->querySingle($db->selectStatement('tickets','count(*) c','WHERE `waiting`=1'))) === false)
		redirect_to('error','500');
	$totals['waiting'] = $r['c'];
	
	// QueueJump Sold
	if (($r = $db->querySingle($db->selectStatement('tickets','count(*) c','WHERE `premium`=1 AND `waiting`=0'))) === false)
		redirect_to('error','500');
	$totals['premium'] = $r['c'];
	
	// Total price of all tickets
	if (($r = $db->querySingle($db->selectStatement('tickets','sum(amount) s','WHERE `waiting`=0 AND `primary_ticket`=1'))) === false)
		redirect_to('error','500');
	$totals['total_cost'] = $r['s'];
	
	// Total charity
	if (($r = $db->querySingle($db->selectStatement('tickets','count(*) c','WHERE `waiting`=0 AND `charity`=1'))) === false)
		redirect_to('error','500');
	$totals['charity'] = $r['c'] * $config['tickets']['charity_extra'];
	
	// Total revenue
	$totals['profit'] = $totals['total_cost'] - $totals['charity'];
	$totals['profitexvat'] = $totals['profit'] / 1.2;
	$totals['vat'] = $totals['profit'] - $totals['profitexvat'];
	
	// Total Money by Cheque
	if (($r = $db->querySingle($db->selectStatement('tickets','sum(amount) s','WHERE `waiting`=0 AND `primary_ticket`=1 AND `payment_method`=2'))) === false)
		redirect_to('error','500');
	$totals['chequem'] = $r['s'];
	
	// Total Money by College Bill
	if (($r = $db->querySingle($db->selectStatement('tickets','sum(amount) s','WHERE `waiting`=0 AND `primary_ticket`=1 AND `payment_method`=1'))) === false)
		redirect_to('error','500');
	$totals['college_billm'] = $r['s'];
	
	// Total by Cheque
	if (($r = $db->querySingle($db->selectStatement('tickets','count(*) c','WHERE `waiting`=0 AND `primary_ticket`=1 AND `payment_method`=2'))) === false)
		redirect_to('error','500');
	$totals['cheque'] = $r['c'];
	
	// Total by College Bill
	if (($r = $db->querySingle($db->selectStatement('tickets','count(*) c','WHERE `waiting`=0 AND `primary_ticket`=1 AND `payment_method`=1'))) === false)
		redirect_to('error','500');
	$totals['college_bill'] = $r['c'];
	
	$totals['orders'] = $totals['cheque'] + $totals['college_bill'];
	$totals['average_tickets'] = round($totals['total'] / $totals['orders'],2);
	
	$totals['total_limit'] = $config['tickets']['max'];
	$totals['premium_limit'] = $config['tickets']['max_premium'];
	
	$graph = array();
	
	if (($result = $db->querySingle('SELECT created FROM ' . $db->prefix . 'tickets ORDER BY created ASC')) === false)
		redirect_to('error','500');
	
	$step = floor((time() - $result['created']) / (3600 * 15));
	//$step = floor((strtotime('1st March 2012') - $result['created']) / (3600 * 15));

	if ($step == 0)
		$step = 1;
	
	$step = $step * 3600;
	
	if (($result = $db->query('SELECT floor(created/' . $step . ') h, count(*) c FROM ' . $db->prefix . 'tickets GROUP BY h ORDER BY h ASC')) === false)
		redirect_to('error','500');
		
	$graph['tickets'] = array();
	$graph['orders'] = array();
	$first = true;
	
	while (($r = $db->fetchResult($result)) !== false) {
		
		$h = $r['h'] * $step;
		$graph['tickets'][$h] = $r['c'];
		
		if ($first) {
			
			$graph['orders'][$h] = 0;
			
			$i = $h + $step;
			$c = floor(time() / $step) * $step;
			//$c = floor(strtotime('21st June 2012') / $step) * $step;
			while ($i <= $c) {
				
				$graph['tickets'][$i] = 0;
				$graph['orders'][$i] = 0;
				
				$i += $step;
			}
			
			$first = false;
		}
		
	}
	
	if (($result = $db->query('SELECT floor(created/' . $step . ') h, count(*) c FROM (SELECT * FROM ' . $db->prefix . 'tickets WHERE `primary_ticket`=1) t GROUP BY h ORDER BY h ASC')) === false)
		redirect_to('error','500');
	
	while (($r = $db->fetchResult($result)) !== false) {
		
		$graph['orders'][$r['h'] * $step] = $r['c'];
		
	}
	
	$graph['step'] = $step;
	
	$template = new KATemplate();
	
	$template->assign('page_name', 'Administration Page');
	
	$template->assign('graph', $graph);
	$template->assign('totals', $totals);
	
	$template->assign('user', $session);
	
	$template->display('admin.tpl');
	
} else {
	
	
}

?>
