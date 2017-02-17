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
			
			$this->db = @mysql_connect($config['database']['server'], $config['database']['username'], $config['database']['password']);
			
			if (!$this->db) {
				trigger_error('Unable to connect to the database given in config.ini', E_USER_ERROR);
				return false;
			}
			
			if (!@mysql_select_db($config['database']['database'], $this->db)) {
				trigger_error('Could not find the database given in config.ini', E_USER_ERROR);
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
		
		return @mysql_real_escape_string($string,$this->db); 
	}

	/*
	 * Performs a query on the database. Returns the query_id.
	 */
	public function query($query) {
		
		if (!$this->connect())
			return false;
		
		$this->last_result = @mysql_query($query, $this->db);
		
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
		
		$result = @mysql_query($query, $this->db);
		
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
			return mysql_fetch_assoc($result);
		
	}
	
	/*
	 * Seeks the results
	 */
	public function seekResults($result = NULL, $row) {
		
		if (!$this->connect())
			return false;
		
		return mysql_data_seek($result, $row);
		
	}
	
	/*
	 * Returns the number of rows on a given query (or last query).
	 */
	public function numResults($result = NULL) {
		
		if (!$this->connect())
			return false;
		
		if ($result == NULL)
			$result = $this->last_result;
		
		return mysql_num_rows($result);
		
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
		$return = $this->fetchResult($result);
		$this->free_result($result);
		
		return $return;
		
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
			return mysql_affected_rows();
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
			return mysql_insert_id();
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

		if ($result != NULL && !@mysql_free_result($result))
			trigger_error('Failed to free the mysql result', E_USER_NOTICE);
		
	}
	
	/*
	 * Close the connection
	 */
	public function close() {
		if ($this->connected) {
			if (!@mysql_select_db($config['database']['database'], $this->db)) {
				trigger_error('Could not close the database connection', E_USER_WARNING);
				return false;
			}
		}
		return true;
	}
}

?>