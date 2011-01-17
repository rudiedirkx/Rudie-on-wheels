<?php

namespace row\database\adapter;

//use row\database\adapter\Generic;
use row\database\adapter\DatabaseAdapter;
use row\database\DatabaseException;

class MySQL extends DatabaseAdapter {

	public function connect() {
		$connection = $this->connection;
		$this->db = new \mysqli($connection[0], $connection[1], $connection[2], $connection[3]);
	}

	public function fetch( $query, $class = null, $just_first = false ) {
//var_dump($query);
		$r = $this->query($query);
		$cb = array($r, 'fetch_object');
		$cl = $class && class_exists((string)$class, true) ? array((string)$class) : array();
		if ( $just_first ) {
			return call_user_func_array($cb, $cl);
		}
		$a = array();
		while ( $l = call_user_func_array($cb, $cl) ) {
			$a[] = $l;
		}
		return $a;
	}

	public function query( $query ) {
		$q = $this->db->query($query);
		if ( !is_object($q) ) {
			throw new DatabaseException($query.' -> '.$this->error());
			return false;
		}
		return $q;
	}

	public function error() {
		return $this->db->error;
	}

	public function errno() {
		return $this->db->errno;
	}

	public function affected_rows() {
		return $this->db->affected_rows;
	}

	public function insert_id() {
		return $this->db->insert_id;
	}

	public function escapeValue( $value ) {
		return $this->db->real_escape_string((string)$value);
	}

}


