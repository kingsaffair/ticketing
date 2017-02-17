<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * User class file for authenticating users via the raven
 * online authentication service and then retrieving details
 * via the lookup service.
 *
 */

if (!defined('IN_KA'))
	exit('Not supposed to be here!');
    
require_once(dirname(__FILE__) . '/ibis.php');
    
/*
 * Attempts to connect to the university's lookup service at 
 * ldap.lookup.cam.ac.uk and retrieve details about the
 * user using the IBIS web API.
 */
function lookupIBIS($crsid) {
	
	global $config, $college_data;
	
    $person = Ibis::getPerson($crsid);
    if (is_null($person))
    {
        trigger_error(sprintf('Unable to find user %s in the LDAP database', $crsid), E_USER_WARNING);
        return false;
    }
	
	$r = array();
	
	// Set the UCS Registered name
	
	if (is_null($person->surname)) {
		// Supressed surname
		$r['lname'] = '';
	} else {
		$r['lname']= trim($person->surname);
	}
	
	if (is_null($person->registeredName)) {
		// Supressed registered name
		$r['fname'] = '';
	} else {
		$rn = trim($person->registeredName);
		if ($r['lname'] != '' && substr($rn,-strlen($r['lname'])) == $r['lname']) {
			// Try to match the last name to the registered name
			$r['fname'] = trim(substr($rn,0,-strlen($r['lname'])));
		} else {
			// Split and take the last word as the last name
			$pos = strrpos($dn,' ');
			$r['fname'] = substr($rn,0,$pos);
			$r['lname'] = substr($rn,$pos+1);
		}
	}
	
	// Filter through institution data till we find one which corresponds to a known
	// college.
	$r['college'] = 0;
	if (array_key_exists(strtoupper($person->attributes[0]->value), $college_data)) {
        	$r['college'] = $college_data[strtoupper($person->attributes[0]->value)];
	}

	if ($crsid == 'ms2335') {
		$r['college'] = 18; // King's (not on Lookup)
		return $r;
	}
	
	if ($r['college'] != $config['general']['college'])
    	{
        	foreach ($person->institutions as $inst)
        	{
            		if (array_key_exists(strtoupper($inst->instid), $college_data))
            		{
                		if ($college_data[strtoupper($inst->instid)] == $config['general']['college'])
                		{
                    			$r['college'] = $config['general']['college'];
                    			break;
                		}
            		}
        	}
    	}

	return $r;
	
}
