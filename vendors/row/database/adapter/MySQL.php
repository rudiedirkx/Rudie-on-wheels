<?php

namespace row\database\adapter;

use row\database\SQLAdapter;
use row\database\DatabaseException;
use row\database\adapter\MySQLi;

class MySQL extends SQLAdapter {

	/* Reflection */
	public function _getTables() {
		$tables = $this->fetch('SHOW TABLES');
		$tables = array_map(function($r) {
			return reset($r);
		}, $tables);
		return $tables;
	}

	public function _getTableColumns( $table ) {
		$_columns = $this->fetch('EXPLAIN '.$table);
		$columns = array();
		foreach ( $_columns AS $c ) {
			$c['type'] = strtoupper(is_int($p=strpos($c['Type'], '(')) ? substr($c['Type'], 0, $p) : $c['Type']);
			$c['name'] = $c['Field'];
			$c['null'] = 'NO' !== $c['Null'];
			$c['default'] = $c['Default'];
			$c['pk'] = 'PRI' === $c['Key'];
			$columns[$c['Field']] = $c;
		}
		return $columns;
	}

	public function _getPKColumns( $table ) {
		$columns = $this->_getTableColumns($table);
		$columns = array_filter($columns, function($c) {
			return $c['pk'];
		});
		$columns = array_keys($columns);
		return $columns;
	}


	static public function initializable() {
		return function_exists('mysql_connect');
	}

	static public function open( $info, $do = true ) {
		if ( MySQLi::initializable() ) {
			return new MySQLi($info, $do);
		}
		return new self($info, $do);
	}

	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = @mysql_connect($connection->host, $connection->user ?: 'root', $connection->pass ?: '');
		if ( !$this->db || ( $connection->dbname && !@mysql_select_db($connection->dbname, $this->db) ) ) {
			return false;
		}
		$this->_fire('post_connect');
	}

	public function connected() {
		if ( is_bool($this->_connected) ) {
			return $this->_connected;
		}
		if ( !is_resource($this->db) ) {
			return $this->_connected = false;
		}
		try {
			$r = $this->query('SHOW TABLES');
			return $this->_connected = (false !== $r);
		}
		catch ( DatabaseException $ex ) {}
		return $this->_connected = false;
	}

	public function _post_connect() {
		if ( $this->connected() ) {
			if ( $this->connectionArgs->names ) {
				$this->execute('SET NAMES \''.$this->connectionArgs->names.'\'');
			}
			else if ( $this->connectionArgs->charachter_set ) {
				$this->execute('SET CHARACTER SET \''.$this->connectionArgs->charachter_set.'\'');
			}
		}
	}


	public function selectOne( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
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
				return mysql_fetch_object($r, $class, array(true));
			}
			return mysql_fetch_assoc($r);
		}
		$a = array();
		if ( $class ) {
			while ( $l = mysql_fetch_object($r, $class, array(true)) ) {
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

	public function execute( $query ) {
		return $this->query($query);
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
	}

}


