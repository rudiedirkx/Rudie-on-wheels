<?php

namespace row\database;

abstract class QueryResult extends \row\core\Object {

	static public function make( $result, $class = '' ) {
		return false !== $result ? new static($result, $class) : false;
	}


	public $result; // typeof who cares
	public $class = '';


	public function __construct( $result, $class = '' ) {
		$this->result = $result;
		$this->class = $class;
	}


	abstract public function singleResult();


	abstract public function nextObject( $class = '\stdClass', $args = array() );

	public function allObjects( $class = '\stdClass', $args = array() ) {
		$a = array();
		while ( $r = $this->nextObject($class, $args) ) {
			$a[] = $r;
		}
		return $a;
	}


	abstract public function nextAssocArray();

	public function nextRecord() {
		return $this->nextAssocArray();
	}

	public function allAssocArrays() {
		$a = array();
		while ( $r = $this->nextAssocArray() ) {
			$a[] = $r;
		}
		return $a;
	}


	abstract public function nextNumericArray();

	public function nextRow() {
		return $this->nextNumericArray();
	}

	public function allNumericArrays() {
		$a = array();
		while ( $r = $this->nextNumericArray() ) {
			$a[] = $r;
		}
		return $a;
	}


	abstract public function count();

}


