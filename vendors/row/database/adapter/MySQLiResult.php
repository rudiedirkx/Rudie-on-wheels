<?php

namespace row\database\adapter;

abstract class MySQLiResult extends \row\database\QueryResult {

	abstract public function singleResult() {
		return current($this->result->fetch_row());
	}

	abstract public function nextObject( $class = '\stdClass' ) {
		return $this->result->fetch_object($class);
	}

	public function nextAssocArray() {
		return $this->result->fetch_assoc();
	}

	public function nextNumericArray() {
		return $this->result->fetch_row();
	}

}


