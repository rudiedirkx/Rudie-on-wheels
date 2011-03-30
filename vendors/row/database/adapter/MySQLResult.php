<?php

namespace row\database\adapter;

class MySQLResult extends \row\database\QueryResult {

	public function singleResult() {
		return mysql_result($this->result, 0);
	}

	public function nextObject( $class = '\stdClass', $args = array() ) {
		return mysql_fetch_object($this->result, $class, $args);
	}

	public function nextAssocArray() {
		return mysql_fetch_assoc($this->result);
	}

	public function nextNumericArray() {
		return mysql_fetch_row($this->result);
	}

	public function count() {
		return mysql_num_rows($this->result);
	}

}


