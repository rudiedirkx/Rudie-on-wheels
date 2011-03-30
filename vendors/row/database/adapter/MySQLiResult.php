<?php

namespace row\database\adapter;

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


