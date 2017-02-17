<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Session class file for authenticating with the raven
 * service and retrieving details about the authenticated
 * user.
 *
 */

if (!defined('IN_KA'))
	exit('Not supposed to be here!');

require('ucam_webauth.php');

// Create a new session
$GLOBALS['session'] = new KASession();

class KASession {
	
	protected $session_active;
	protected $loaded;
	
	public $crsid;
	public $committee_flag;
	
	/*
	 * Constuctor
	 */ 
	public function __construct() {
		
		$this->session_active = false;
		$this->loaded = false;
		$this->crsid = '';
		
	}
	
	public function __toString() {
		return $this->crisd;
	}
	
	/*
	 * Authenticates with raven
	 */
	public function authRaven() {
		
		global $config;
		
		// **** TODO: Write a better ucam_webauth which allows custom session handling
		
		setcookie($config['ucam_auth']['cookie_name'], '', time()-60, $config['ucam_auth']['cookie_path']);
		
		// Create a raven ucam_webauth
		$webauth = new Ucam_Webauth(array_merge($config['ucam_auth'],
			array(
				'hostname'		=> $config['general']['hostname'],
				'description'	=> $config['general']['description'])));
				
		/*
		 * Fix the ucam_webauth script.
		 */
		$_SERVER['QUERY_STRING'] = str_replace('mode=user&arg=login&','',$_SERVER['QUERY_STRING']);
		
		// Attempt to authenticate our user
		if (!$webauth->authenticate()) {
			trigger_error('Unable to authenticate with the raven service', E_USER_ERROR);
			return false;
		}
		
		if (!$webauth->success()) {
			$this->destroySession();
			redirect_to();
			return false;
		}
		
		// Take the CRSID of the authenticated user
		$this->crsid = strtolower($webauth->principal());
		
		return true;
		
	}
	
	public function destroySession($destroy_cookie = true) {
		
		global $config;
		
		if ($this->session_active) {
			$_SESSION = array();
			session_unset();
			if ($destroy_cookie && isset($_COOKIE[session_name()])) { 
				setcookie(session_name(), '', time()-60); 
			}
			if ($destroy_cookie && isset($_COOKIE[$config['ucam_auth']['cookie_name']])) {
				setcookie($config['ucam_auth']['cookie_name'], '', time()-60, $config['ucam_auth']['cookie_path']);
			}
			session_destroy();
			$this->session_active = false;
		}
	}
	
	/*
	 * Tries to load an existing session
	 */
	public function readSession($redirect = true) {
		
		global $db, $config;
		
		/*
		 * 1.	Attempt to load the session
		 */
		if (!session_start()) {
			trigger_error('Unable to start a user session', E_USER_ERROR);
			return false;
		}
		$this->session_active = true;
	
		/*
		 * 2.	Check that a session exists and attempt to read the session data.
		 */
		if (isset($_SESSION['crsid'])) {
			
			// Load the data in the session and refresh the timestamp
			if ($this->loadSession()) {
				$this->loaded = true;
				$this->writeSession(false);
				
				if (($this->committee_flag = $this->getData('Tickets','CommitteeFlag')) === NULL) {
					
					$committee = $db->querySingle($db->selectStatement('committee', '*', 'WHERE `crsid`="' . $db->escape($this->crsid) . '"'));
					
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
					
					$this->storeData('Tickets','CommitteeFlag',$this->committee_flag);
					
				}
				
				return true;
			} else {
				/*
				 * We have a faulty session.
				 *
				 * We need to destroy the session and create a new one with a new SID.
				 */
				// Save the old session id
				$old_sessid = session_id();
				
				// Create a new session id and save it
				if (!session_regenerate_id(true)){
					trigger_error('Unable to create a new session id', E_USER_ERROR);
					return false;
				}
				$new_sessid = session_id();
				
				// Load the old session id so it can be destroyed
				session_id($old_sessid);
				
				// Destroy the session
				$this->destroySession(false);
				
				// Load the new session id
				session_id($new_sessid);
				if (!session_start()) {
					trigger_error('Unable to start a user session', E_USER_ERROR);
					return false;
				}
				$this->session_active = true;
			}
			
		}
		
		/*
		 * 3.	If a session does not exist then redirect to login
		 */
		if($redirect) {
			redirect_to('user','login');
		}
		
		return false;
		
	}
	
	/*
	 * Allows external scripts to store data in the session
	 * 
	 * The script prepends d_ to the variable names to
	 * avoid clashes with the user data.
	 * 
	 */
	public function storeData($identifier, $name, $value) {
		
		if ($this->loaded && $this->session_active) {
			
			$_SESSION['d_' . $identifier . '_' . $name] = $value;
			return true;
			
		}
		
		return false;
		
	}
	
	public function getData($identifier, $name) {
		
		if ($this->session_active) {
			
			if (isset($_SESSION['d_' . $identifier . '_' . $name])) 
				return $_SESSION['d_' . $identifier . '_' . $name];
			else
				return NULL;
			
		}
		
	}
	
	// Retrieves the data and restroys the session variable
	public function getDataR($identifier, $name) {
		
		if ($this->session_active) {
				
			if (isset($_SESSION['d_' . $identifier . '_' . $name])) {
				$r = $_SESSION['d_' . $identifier . '_' . $name];
				$_SESSION['d_' . $identifier . '_' . $name] = NULL;
				unset($_SESSION['d_' . $identifier . '_' . $name]);
				return $r;
			} else {
				return NULL;
			}
		}
		
	}
	
	// Destroy Data
	public function destroyData($identifier, $name) {
		if ($this->session_active) {
			if (isset($_SESSION['d_' . $identifier . '_' . $name])) {
				$_SESSION['d_' . $identifier . '_' . $name] = NULL;
				unset($_SESSION['d_' . $identifier . '_' . $name]);
			}
		}
		return;
	}
	
	/*
	 * Attempts to create a new session
	 */
	public function createSession() {
		
		global $config;
		
		/*
		 * 1. 	Check that an existing session does not already exist
		 */
		if ($this->readSession(false)) {
			return true;
		}
		
		/*
		 * 2. 	Try to authenticate the user with raven
		 */
		if (!$this->authRaven()) {
			trigger_error('Unable to authenticate with the raven service', E_USER_ERROR);
			return false;
		}
		
		$this->loaded = true;
		$this->writeSession();
		return true;
		
	}
	
	/*
	 * Returns if the session has been loaded or not
	 */
	public function isLoaded() {
		return $this->loaded;
	}
	
	/*
	 * Closes the session so that it cannot be written anymore.
	 */
	public function closeSession() {
		session_write_close();
		$this->session_active = false;
	}
	
	/*
	 * Writes data to the session (or just refreshes the timestamp)
	 */
	public function writeSession($updatedata = true) {
		if ($this->loaded && $this->session_active) {
			$_SESSION['timestamp'] = time();
			if ($updatedata) {
				$_SESSION['crsid'] = $this->crsid;
				
				// Security features
				$ipV = ipVersion($_SERVER['REMOTE_ADDR']);
				$_SESSION['ip' . $ipV] = $_SERVER['REMOTE_ADDR'];
				
				if (!isset($_SESSION['ip4']))
					$_SESSION['ip4'] = '';
				
				if (!isset($_SESSION['ip6']))
					$_SESSION['ip6'] = '';
				
				$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			}
			return true;
		} else {
			trigger_error('Attempted to write to a closed session.', E_USER_WARNING);
			return false;
		}
	}
	
	/*
	 * Loads data from $_SESSION, returns true only if all data was available.
	 */
	function loadSession() {
	
		if ($this->session_active) {
			
			if (isset($_SESSION['timestamp'])) {
				
				// Check if session expired
				if (ini_get('session.gc_maxlifetime') < time() - $_SESSION['timestamp'])
					return false;
				
				// If an old session
				if (isset($_SESSION['ip']) && $_SESSION['ip'] != '') {
					$_SESSION['ip' . ipVersion($_SESSION['ip'])] = $_SESSION['ip'];
					$_SESSION['ip'] = '';
					unset($_SESSION['ip']);
				}
				
				// Check if this session is secure	
				if ((isset($_SESSION['ip4']) || isset($_SESSION['ip6'])) && isset($_SESSION['userAgent'])) {
					
					/*
					 * Security:	If the IP Address or the userAgent has changed since we
					 * 				initalised the session then the session is not secure.
					 */
					if ($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
						logIntrusion(sprintf('Session transfer from using %s to using %s.', $_SESSION['userAgent'], $_SERVER['HTTP_USER_AGENT']), (isset($_SESSION['crsid']) ? $_SESSION['crsid'] : ''), false);
						return false;
					}
					
					$ipV = ipVersion($_SERVER['REMOTE_ADDR']);
					
					if ($_SESSION['ip' . $ipV] != '' && $_SESSION['ip' . $ipV] != $_SERVER['REMOTE_ADDR']) {
						logIntrusion(sprintf('Session transfer from %s to %s.', $_SESSION['ip' . $ipV], $_SERVER['REMOTE_ADDR']), (isset($_SESSION['crsid']) ? $_SESSION['crsid'] : ''), false);
						return false;
					}
					
					/*
					 * Attempt to load all the variables
					 * 	- 	if any are missing we have an incomplete session and need to create a
					 *		new one with data from the database. (return false)
					 */
					if (isset($_SESSION['crsid'])) {
						$this->crsid = $_SESSION['crsid'];
						return true;
					}
					
				}
			
			}
			
		}
		
		return false;
		
	}
	
	function sessionHash() {
		$m = session_id();
		for ($i = 0; $i < 1912; $i++) {
			$m = md5($m);
		}
		return $m;
	}
	
}
?>