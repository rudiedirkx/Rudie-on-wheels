<?php

namespace row\database\query;

abstract class QueryCondition implements \ArrayAccess {
	static public function create( $conditions ) {
		if ( 1 < func_num_args() ) {
			return new static(func_get_args());
		}

		if ( is_a($conditions, 'row\database\query\QueryCondition') ) {
			if ( get_class($conditions) == get_called_class() ) {
				return $conditions;
			}

			return new static(array($conditions));
		}

		return new static($conditions);
	}

	public $conditions = array();
	public $operator = 'AND';

	public function __construct( $conditions ) {
		is_string($conditions) && $conditions = array($conditions);

		$this->conditions = $conditions;
	}

	public function add( $condition ) {
		$this->conditions[] = $condition;
	}

	public function render( $db, $parenthesize = true ) {
		$conditions = array();

		foreach ( $this->conditions AS $k => $condition ) {
			if ( is_a($condition, 'row\database\query\QueryCondition') ) {
				$conditions[] = $condition->render($db);
			}

			$conditions[] = $db->condition($condition, $k);
		}

		$ldelim = '(';
		$rdelim = ')';
		if ( !$parenthesize ) {
			$ldelim = $rdelim = '';
		}

		return $ldelim . implode(' ' . $this->operator . ' ', $conditions) . $rdelim;
	}

	public function offsetExists( $offset ) {
		return isset($this->conditions[$offset]);
	}

	public function offsetGet( $offset ) {
		return $this->conditions[$offset];
	}

	public function offsetSet( $offset, $value ) {
		$this->conditions[$offset] = $value;
	}

	public function offsetUnset( $offset ) {
		unset($this->conditions[$offset]);
	}

}


