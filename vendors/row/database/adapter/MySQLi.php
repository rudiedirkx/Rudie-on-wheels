<?php

namespace row\database\adapter;

use row\database\adapter\MySQL;
use row\database\DatabaseException;

class MySQLi extends MySQL {

	static public function initializable() {
		return class_exists('\mysqli');
	}

	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = @new \mysqli($connection->host, $connection->user ?: 'root', $connection->pass ?: '', $connection->dbname ?: '');
		$this->_fire('post_connect');
	}

	public function connected() {
		if ( is_bool($this->_connected) ) {
			return $this->_connected;
		}
		if ( !is_object($this->db) || $this->db->connect_error ) {
			return $this->_connected = false;
		}
		try {
			$r = $this->query('SHOW TABLES');
			return $this->_connected = (false !== $r);
		}
		catch ( DatabaseException $ex ) {}
		return $this->_connected = false;
	}


	public function selectOne( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		$r = $this->query($query);
		if ( !is_object($r) || 0 >= $r->num_rows ) {
			return false;
		}
		return current($r->fetch_row());
	}

	public function countRows( $query ) {
		$r = $this->query($query);
		if ( is_object($r) ) {
			return $r->num_rows;
		}
		return false;
	}

	public function fetchByField( $query, $field ) {
		$r = $this->query($query);
		if ( !is_object($r) ) {
			return false;
		}
		$a = array();
		while ( $l = $r->fetch_assoc() ) {
			$a[$l[$field]] = $l;
		}
		return $a;
	}

	public function fetchFieldsAssoc( $query ) {
		$r = $this->query($query);
		if ( is_object($r) ) {
			return $r->num_rows;
		}
		$a = array();
		while ( $l = $r->fetch_row() ) {
			$a[$l[0]] = $l[1];
		}
		return $a;
	}

	public function fetchFieldsNumeric( $query ) {
		$r = $this->query($query);
		if ( is_object($r) ) {
			return $r->num_rows;
		}
		$a = array();
		while ( $l = $r->fetch_row() ) {
			$a[] = $l[0];
		}
		return $a;
	}

	public function fetch( $query, $class = null, $justFirst = false ) {
//var_dump($query);
		$r = $this->query($query);
		if ( !is_object($r) ) {
			return false;
		}
		if ( !is_string($class) || !class_exists($class) ) {
			$class = false;
		}
		if ( $justFirst ) {
			if ( $class ) {
				return $r->fetch_object($class, array(true));
			}
			return $r->fetch_assoc();
		}
		$a = array();
		if ( $class ) {
			while ( $l = $r->fetch_object($class, array(true)) ) {
				$a[] = $l;
			}
		}
		else {
			while ( $l = $r->fetch_assoc() ) {
				$a[] = $l;
			}
		}
		return $a;
	}

	public function query( $query ) {
		$this->queries[] = $query;
		$q = @$this->db->query($query);
		if ( !$q ) {
			return $this->except($query.' -> '.$this->error());
		}
		return $q;
	}

	public function execute( $query ) {
		return $this->query($query);
	}

	public function error() {
		return $this->db->error;
	}

	public function errno() {
		return $this->db->errno;
	}

	public function affectedRows() {
		return $this->db->affected_rows;
	}

	public function insertId() {
		return $this->db->insert_id;
	}

	public function escapeValue( $value ) {
		return $this->db->real_escape_string((string)$value);
	}

}


