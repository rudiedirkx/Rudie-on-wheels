<?php

namespace row\database\adapter;

use row\database\adapter\MySQL;
use row\database\DatabaseException;

class MySQLi extends MySQL {

	static public $events;

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



class MySQLiResult extends \row\database\QueryResult {

	public function singleResult() {
		return current($this->result->fetch_row());
	}

	public function nextObject( $class = '\stdClass', $args = array() ) {
		return $this->result->fetch_object($class, $args);
	}

	public function nextAssocArray() {
		return $this->result->fetch_assoc();
	}

	public function nextNumericArray() {
		return $this->result->fetch_row();
	}

	public function count() {
		return $this->result->num_rows;
	}

}


