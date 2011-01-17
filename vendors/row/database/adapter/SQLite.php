<?php

namespace row\database\adapter;

use row\database\Adapter;
use row\database\DatabaseException;

class SQLite extends Adapter {

	static public function initializable() {
		return false;
	}

/*	static public function open( $info, $do = true ) {
		if ( MySQLi::initializable() ) {
			return new MySQLi($info, $do);
		}
		return new self($info, $do);
	}

	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = mysql_connect($connection->host, $connection->user ?: 'root', $connection->pass ?: '');
		mysql_select_db($connection->dbname, $this->db);
	}

	public function selectOne( $table, $field, $conditions ) {
		$conditions = $this->stringifyConditions($stringifyConditions);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		$r = $this->query($query);
		if ( !$r || 0 >= mysql_num_rows($r) ) {
			return false;
		}
		return mysql_result($r, 0);
	}

	public function countRows( $query ) {
		$r = $this->query($query);
		if ( $r ) {
			return mysql_num_rows($r);
		}
		return false;
	}

	public function fetchByField( $query, $field ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = mysql_fetch_assoc($r) ) {
			$a[$l[$field]] = $l;
		}
		return $a;
	}

	public function fetchFieldsAssoc( $query ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = mysql_fetch_row($r) ) {
			$a[$l[0]] = $l[1];
		}
		return $a;
	}

	public function fetchFieldsNumeric( $query ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = mysql_fetch_row($r) ) {
			$a[] = $l[0];
		}
		return $a;
	}

	public function fetch( $query, $class = null, $justFirst = false ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		if ( !is_string($class) || !class_exists($class) ) {
			$class = false;
		}
		if ( $justFirst ) {
			if ( $class ) {
				return mysql_fetch_object($r, $class);
			}
			return mysql_fetch_assoc($r);
		}
		$a = array();
		if ( $class ) {
			while ( $l = mysql_fetch_object($r, $class) ) {
				$a[] = $l;
			}
		}
		else {
			while ( $l = mysql_fetch_assoc($r) ) {
				$a[] = $l;
			}
		}
		return $a;
	}

	public function query( $query ) {
		$q = mysql_query($query, $this->db);
		if ( !$q ) {
			if ( $this->throwExceptions ) {
				throw new DatabaseException($query.' -> '.$this->error());
			}
			return false;
		}
		return $q;
	}

	public function error() {
		return mysql_error($this->db);
	}

	public function errno() {
		return mysql_errno($this->db);
	}

	public function affectedRows() {
		return mysql_affected_rows($this->db);
	}

	public function insertId() {
		return mysql_insert_id($this->db);
	}

	public function escapeValue( $value ) {
		return mysql_real_escape_string((string)$value, $this->db);
	}*/

}


