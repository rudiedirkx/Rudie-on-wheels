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


	public function query( $query ) {
		$this->queries[] = $query;
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
		$this->queries[] = $query;
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

	public function result( $query, $targetClass = '' ) {
		$resultClass = __CLASS__.'Result';
		return $resultClass::make($this->query($query), $targetClass);
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

	public function singleResult() {
		return $this->result->fetchColumn(0);
	}

	public function nextObject( $class = '\stdClass', $args = array() ) {
		return $this->result->fetchObject($class, $args);
	}

	public function nextAssocArray() {
		return $this->result->fetch(\PDO::FETCH_ASSOC);
	}

	public function nextNumericArray() {
		return $this->result->fetch(\PDO::FETCH_NUM);
	}

	public function count() {
		return $this->result->rowCount();
	}

}


