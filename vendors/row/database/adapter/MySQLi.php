<?php

namespace row\database\adapter;

use row\database\DatabaseException;

class MySQLi extends \row\database\adapter\MySQL {

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


