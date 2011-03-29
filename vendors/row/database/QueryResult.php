<?php

namespace row\database;

abstract class QueryResult extends \row\core\Object {

	public $result; // typeof who cares

	abstract public function singleResult();

	abstract public function nextObject( $class = '\stdClass' );
	public function allObjects( $class = '\stdClass' ) {
		$a = array();
		while ( $r = $this->nextObject($class) ) {
			$a[] = $r;
		}
		return $a;
	}

	abstract public function nextAssocArray();
	public function allAssocArrays() {
		$a = array();
		while ( $r = $this->nextAssocArray() ) {
			$a[] = $r;
		}
		return $a;
	}

	abstract public function nextNumericArray();
	public function allNumericArrays() {
		$a = array();
		while ( $r = $this->nextNumericArray() ) {
			$a[] = $r;
		}
		return $a;
	}

}


