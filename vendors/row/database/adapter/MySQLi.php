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
		$this->db = new \mysqli($connection->host, $connection->user ?: 'root', $connection->pass ?: '', $connection->dbname ?: '');
	}

	public function connected() {
		try {
			$r = $this->query('SELECT 1 FROM sqlite_master');
			return false !== $r;
		}
		catch ( DatabaseException $ex ) {}
		return false;
	}


	public function selectOne( $table, $field, $conditions ) {
		$conditions = $this->stringifyConditions($stringifyConditions);
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
				return $r->fetch_object($class);
			}
			return $r->fetch_assoc();
		}
		$a = array();
		if ( $class ) {
			while ( $l = $r->fetch_object($class) ) {
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
		$q = $this->db->query($query);
		if ( !is_object($q) ) {
			if ( $this->throwExceptions ) {
				throw new DatabaseException($query.' -> '.$this->error());
			}
			return false;
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


