<?php
/*
 * King's Affair 2012 Ticketing System
 *
 * Developed and designed by Andrew Lee 2011-2012.
 * Copyright 2011 Andrew Lee. All rights reserved.
 *
 *
 * Database class file for connecting to the mysql database
 * provided for the King's Affair.
 * 
 * Implemented so that the class will only connect to the
 * database when necessary
 *
 */

if (!defined('IN_KA'))
	exit('Not supposed to be here!');
	
// Creates a new object db
$GLOBALS['db'] = new database();

class database {
	
	public $prefix;
	
	public $last_result;
	
	protected $db;
	protected $connected;
	
	/*
	 * Constructor function
	 */
	public function __construct() {
			
		global $config;
		
		$this->connected = false;
			
		$this->prefix = $config['database']['prefix'];
		
		return true;
		
	}
	
	public function __destruct() {
		
		$this->close();
   	
	}
	
	/*
	 * Connect to the database 
	 */
	public function connect() {
			
		global $config;
		
		if (!$this->connected) {
			$this->db = new mysqli($config['database']['server'],
								   $config['database']['username'],
								   $config['database']['password'],
								   $config['database']['database']);
			
			if ($this->db->connect_errno) {
				trigger_error('Unable to connect to the database given in config.ini ('.$this->db->connect_errno.'). '.$this->db->connect_error, E_USER_ERROR);
				return false;
			}			
		}
		
		return true;
	
	}
	
	/*
	 * Escapes a string
	 */
	public function escape($string) { 
		
		if (!$this->connect())
			return false;
		
		return $this->db->escape_string($string); 
	}

	/*
	 * Performs a query on the database. Returns the query_id.
	 */
	public function query($query) {
		
		if (!$this->connect())
			return false;
		
		$this->last_result = $this->db->query($query);
		
		if (!$this->last_result) {
			trigger_error(sprintf('SQL Query "%s" failed', $query), E_USER_WARNING);
			return false;
		}
		
		return $this->last_result;
		
	}
	
	/*
	 * Executes a query without returning a result.
	 */
	public function execute($query) {
		
		if (!$this->connect())
			return false;
		
		$this->last_result = $this->db->query($query);
		
		if (!$result) {
			trigger_error(sprintf('SQL Query "%s" failed', $query), E_USER_WARNING);
			return false;
		}
		
		$this->free_result($result);
		
		return true;
		
	}
	
	/*
	 * Fetches the next results using mysql_fetch_assoc 
	 */
	public function fetchResult($result = NULL) {
		
		if (!$this->connect())
			return false;
		
		if ($result == NULL)
			$result = $this->last_result;
		
		if ($result != NULL)
			return $result->fetch_assoc();
		
	}
	
	/*
	 * Seeks the results
	 */
	public function seekResults($result = NULL, $row) {
		
		if (!$this->connect())
			return false;
		
		return $result->data_seek($row);
		
	}
	
	/*
	 * Returns the number of rows on a given query (or last query).
	 */
	public function numResults($result = NULL) {
		
		if (!$this->connect())
			return false;
		
		if ($result == NULL)
			$result = $this->last_result;
		
		return $result->num_rows;
		
	}
	
	
	/*
	 * Query and returns a single result
	 */
	public function querySingle($query) {
		
		if (!$this->connect())
			return false;
		
		$query = rtrim(trim($query), ';');
		
		$query .= ' LIMIT 1';
		
		$result = $this->query($query);
		// $return = $this->fetchResult($result);
		// $this->free_result($result);
		
		return $result->fetch_assoc();
		
	}
	
	/*
	 * Update query which escapes all data
	 */
	public function queryUpdate($table, $data, $where='TRUE') {
		
		if (!$this->connect())
			return false;
		
		$q = 'UPDATE `' . $this->prefix . $table . '` SET ';
		
		foreach ($data as $key=>$val) {
			$q .= '`' . $key . '` = ';
			if (is_bool($val)) $q .= ($val === true ? 'TRUE' : 'FALSE');
			elseif (is_int($val)) $q .= intval($val);
			elseif(strtolower($val)=='null' || $val === null) $q .= 'NULL'; 
			elseif(strtolower($val)=='now()') $q.= 'NOW()'; 
			elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= '`' . $key . '` + ' . $m[1];
			elseif(preg_match("/^decrement\((\-?\d+)\)$/i",$val,$m)) $q.= '`' . $key . '` - ' . $m[1]; 
			else $q .= '"' . $this->escape($val) . '"';
			$q .= ', ';
		}
		
		$q = substr($q,0,-2) . ' WHERE ' . $where . ';';
		
		if ($this->query($q) !== false)
			return $this->db->affected_rows;
		else
			return false;
		
	}
	
	/*
	 * Insert query which escapes all data
	 */
	public function queryInsert($table, $data) {
		
		if (!$this->connect())
			return false;
		
		$q = 'INSERT INTO `' . $this->prefix . $table . '` ';
		
		$k = '';
		$v = '';
		
		foreach ($data as $key=>$val) {
			$k .= '`' . $key . '`, ';
			if (is_bool($val)) $v .= ($val === true ? 'TRUE' : 'FALSE');
			elseif (is_int($val)) $v .= intval($val);
			elseif(strtolower($val)=='null' || $val === null) $v .= 'NULL'; 
			elseif(strtolower($val)=='now()') $v.= 'NOW()'; 
			else $v .= '"' . $this->escape($val) . '"';
			$v .= ', ';
		}
		
		$q .= "(". substr($k,0,-2) .") VALUES (". substr($v,0,-2) .");";
		
		if ($this->query($q) !== false)
			return $this->db->insert_id;
		else
			return false;
		
	}
	
	/*
	 * Select query
	 */
	public function selectStatement($table, $fields = '*', $args = '') {
		
		
		if (!$this->connect())
			return false;
		
		$q = 'SELECT ';
		
		if (is_array($fields)) {
			foreach ($fields as $f) {
				$q .= '`' . $f . '`, ';
			}
			$q = substr($q,0,-2);
		} else {
			$q .= $fields;
		}
		
		$q .= ' FROM `' . $this->prefix . $table . '` ' . $args;
		
		return $q;
		
	}
	
	/*
	 * Frees the result.
	 */
	public function free_result($result = NULL) {
		
		if (!$this->connect())
			return false;

		if ($result == NULL)
			$result = $this->last_result;

		if ($result != NULL && !$result->free())
			trigger_error('Failed to free the mysql result', E_USER_NOTICE);
		
	}
	
	/*
	 * Close the connection
	 */
	public function close() {
		if ($this->connected) {
			$this->db->close();
			$this->connected = false;
		}
		return true;
	}
}

?>