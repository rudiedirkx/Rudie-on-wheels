<?php

namespace row\database\adapter;

use row\database\Adapter;
use row\database\DatabaseException;
use \PDOException;

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


	public function query( $query ) {
		$this->queries[] = $query;

		try {
			$q = @$this->db->query($query);
			if ( !$q ) {
				return $this->except($query.' -> '.$this->error());
			}
		} catch ( PDOException $ex ) {
			return $this->except($query.' -> '.$ex->getMessage());
		}

		return $q;
	}

	public function execute( $query ) {
		$this->queries[] = $query;

		try {
			$q = @$this->db->exec($query);
			if ( !$q ) {
				throw new DatabaseException($query.' -> '.$this->error());
			}
		} catch ( PDOException $ex ) {
			return $this->except($query.' -> '.$ex->getMessage());
		}

		$this->affected = $q;

		return $q;
	}

	public function result( $query, $targetClass = '' ) {
		$resultClass = __CLASS__.'Result';
		return $resultClass::make($this->query($query), $targetClass, $this);
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



class PDOResult extends \row\database\QueryResult {

	public $rows = array();
	public $index = 0;

	public function singleResult() {
		return $this->result->fetchColumn(0);
	}

	public function nextObject( $class = '\stdClass', $args = array() ) {
		if ( !$this->rows ) {
			$this->rows = $this->result->fetchAll(\PDO::FETCH_CLASS, $class, $args);
		}
		if ( isset($this->rows[$this->index]) ) {
			return $this->rows[$this->index++];
		}
	}

	public function nextAssocArray() {
		return $this->result->fetch(\PDO::FETCH_ASSOC);
	}

	public function nextNumericArray() {
		return $this->result->fetch(\PDO::FETCH_NUM);
	}

	public function count() {
		$r = $this->result;
		return count($this->result->fetchAll(\PDO::FETCH_NUM));

		return $this->result->rowCount();

		// WOW, PDO is stupid!
//		return $this->db->query('SELECT FOUND_ROWS() AS r')->fetchColumn(0);

		$q = preg_replace('/select\s.+?\sfrom/i', 'select count(1) AS rv from', $this->result->queryString);
		$c = (int)$this->db->query($q)->fetchColumn(0);
		array_pop($this->db->queries);
		return $c;
	}

}


