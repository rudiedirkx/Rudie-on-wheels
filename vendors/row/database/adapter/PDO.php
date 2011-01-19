<?php

namespace row\database\adapter;

use row\database\Adapter;
use row\database\DatabaseException;

abstract class PDO extends Adapter {

	public $affected = 0;

	static public function initializable() {
		return class_exists('\PDO');
	}

	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = new \PDO($connection->dsn);
		$this->_fire('post_connect');
	}


	public function selectOne( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		$r = $this->query($query);
		if ( !$r || 0 >= $r->rowCount() ) {
			return false;
		}
		return $r->fetchColumn(0);
	}

	public function countRows( $query ) {
		$r = $this->query($query);
		if ( $r ) {
			return $r->rowCount();
		}
		return false;
	}

	public function fetchByField( $query, $field ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = $r->fetch(\PDO::FETCH_ASSOC) ) {
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
		while ( $l = $r->fetch(\PDO::FETCH_NUM) ) {
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
		while ( $l = $r->fetch(\PDO::FETCH_NUM) ) {
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
				return $r->fetchObject($class);
			}
			return $r->fetch(\PDO::FETCH_ASSOC);
		}
		$a = array();
		if ( $class ) {
			while ( $l = $r->fetchObject($class) ) {
				$a[] = $l;
			}
		}
		else {
			while ( $l = $r->fetch(\PDO::FETCH_ASSOC) ) {
				$a[] = $l;
			}
		}
		return $a;
	}

	public function query( $query ) {
		try {
			$q = $this->db->query($query);
		} catch ( \PDOException $ex ) {
			if ( $this->throwExceptions ) {
				throw new DatabaseException($query.' -> '.$ex->getMessage());
			}
			return false;
		}
		return $q;
	}

	public function execute( $query ) {
		try {
			$q = $this->db->exec($query);
			if ( !$q ) {
				throw new DatabaseException($query.' -> '.$this->error());
			}
		} catch ( \PDOException $ex ) {
			if ( $this->throwExceptions ) {
				throw new DatabaseException($query.' -> '.$ex->getMessage());
			}
			return false;
		}
		$this->affected = $q;
		return $q;
	}

	public function error() {
		$err = $this->db->errorInfo();
		return $err[2] ?: $err[0];
	}

	public function errno() {
		return $this->db->errorCode();
	}

	public function affectedRows() {
		return $this->affected;
	}

	public function insertId() {
		return $this->db->lastInsertId();
	}

	public function escapeValue( $value ) {
		return addslashes((string)$value);
	}

}


