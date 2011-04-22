<?php

namespace row\database;

use row\core\Object;
use row\database\Adapter; // interface
use row\database\ModelException; // model errors

class Model extends Object {

	public function __tostring() {
		return empty(static::$_title) || !$this->_exists(static::$_title) ? basename(get_class($this)).' model' : $this->{static::$_title};
	}

	static public $_db_key = '_db';
	static public $_db;

	/**
	 * 
	 */
	static public function dbObject( Adapter $db = null ) {
		$dbk = static::$_db_key;
		if ( $db ) {
			self::$$dbk = $db;
		}
		return self::$$dbk;
	}


	static public $_table = '';

	static public $_pk = array();

	static public $_title = '';


	const GETTER_ONE		= 1;
	const GETTER_ALL		= 2;
	const GETTER_FUNCTION	= 3;
	const GETTER_FIRST		= 4;

	static public $_getters = array(
//		'author' => array( self::GETTER_ONE, true, 'User', 'author_id', 'user_id' ).
//		'comments' => array( self::GETTER_ALL, true, 'Comment', 'post_id', 'post_id' ).
//		'first_comment' => array( self::GETTER_FIRST, true, 'Comment', 'post_id', 'post_id' ).
//		'followers' => array( self::GETTER_FUNCTION, true, 'getFollowerUserObjects' ).
	);


	/**
	 * Enables calling of Post::update with defined function _update
	 */
	static public function __callStatic( $func, $args ) {
		if ( '_' != $func{0} ) {
			$func = '_'.$func;
		}
		if ( !method_exists(get_called_class(), $func) ) {
			throw new \row\database\ModelException('Methodo "'.$func.'" no existo!');
		}
		return call_user_func_array(array('static', $func), $args);
	} // END __callStatic() */


	/**
	 * 
	 */
	public static function _query( $conditions ) {
		return 'SELECT * FROM '.static::$_table.' WHERE '.$conditions;
	}


	/**
	 * 
	 */
	static public function _byQuery( $query, $result = false ) {
		$class = get_called_class();
		if ( class_exists($class.'Record') && is_a($class.'Record', get_called_class()) ) {
			$class = $class.'Record';
		}
		if ( $result ) {
			return static::dbObject()->result($query, $class);
		}
		return static::dbObject()->fetch($query, $class);
	}

	/**
	 * 
	 */
	static public function _fetch( $conditions, $params = array() ) {
		$conditions = static::dbObject()->replaceholders($conditions, $params);
		$query = static::_query($conditions, $params);
		return static::_byQuery($query);
	}

	/**
	 * 
	 */
	static public function _all( $conditions = null, $params = array() ) {
		$conditions or $conditions = '1';
		return static::_fetch($conditions, $params);
	}

	/**
	 * Returns exactly one object with the matching conditions OR throws a model exception
	 */
	static public function _one( $conditions, $params = array() ) {
		$conditions = static::dbObject()->stringifyConditions($conditions, $params);
		$query = static::_query($conditions);
		$r = static::_byQuery($query, true);
		if ( 1 !== $r->count() ) {
			throw new ModelException('Not exactly one record returned. Found '.count($r).' of '.get_called_class().'.');
		}
		return $r->nextObject($r->class, array(true));
	}

	/**
	 * Returns null or the first object with the matching conditions
	 */
	static public function _first( $conditions, $params = array() ) {
		$conditions = static::dbObject()->stringifyConditions($conditions, $params);
		$query = static::_query($conditions);
		$r = static::_byQuery($query, true);
		return $r->nextObject($r->class, array(true));
	}

	/**
	 * 
	 */
	static public function _get( $pkValues, $moreConditions = array(), $params = array() ) {
		$pkValues = (array)$pkValues;
		$pkColumns = (array)static::$_pk;
		if ( count($pkValues) !== count($pkColumns) ) {
			throw new ModelException('Invalid number of PK arguments ('.count($pkValues).' instead of '.count($pkColumns).').');
		}
		$pkValues = array_combine($pkColumns, $pkValues);
		$conditions = static::dbObject()->stringifyConditions($pkValues, 'AND', static::$_table);
		if ( $moreConditions ) {
			$conditions .= ' AND '.static::dbObject()->stringifyConditions($moreConditions, $params);
		}
		return static::_one($conditions);
	}

	/**
	 * 
	 */
	static public function _delete( $conditions, $params = array() ) {
		if ( static::dbObject()->delete(static::$_table, $conditions, $params) ) {
			return static::dbObject()->affectedRows();
		}
		return false;
	}

	/**
	 * 
	 */
	static public function _update( $updates, $conditions, $params = array() ) {
		if ( static::dbObject()->update(static::$_table, $updates, $conditions, $params) ) {
			return static::dbObject()->affectedRows();
		}
		return false;
	}

	/**
	 * 
	 */
	static public function _insert( $values ) {
		if ( static::dbObject()->insert(static::$_table, $values) ) {
			return (int)static::dbObject()->insertId();
		}
		return false;
	}

	/**
	 * 
	 */
	static public function _replace( $values ) {
		return static::dbObject()->replace(static::$_table, $values);
	}

	static public function _count( $conditions = '' ) {
		return static::dbObject()->count(static::$_table, $conditions);
	}


	/**
	 * 
	 */
	public function __construct( $init = false ) {
		if ( true === $init ) { // Inited by database\Adapter
			$this->_fire('post_fill', array((array)$this));
		}
		else if ( is_array($init) ) { // Inited manually with data
			$this->_fill($init);
			$this->_fire('post_fill', array($init));
		}
		$this->_fire('init');
	}

	/**
	 * 
	 */
	public function _fill( $data ) {
		foreach ( (array)$data AS $k => $v ) {
			if ( $k || '0' === $k ) {
				$this->$k = $v;
			}
		}
		$this->_fire('post_fill', array((array)$data));
	}


	/**
	 * Returns an associative array of PK keys + values
	 */
	public function _pkValue( $strict = true ) {
		return $this->_values((array)static::$_pk, $strict);
	}

	/**
	 * Returns an associative array of keys + values
	 */
	public function _values( Array $columns, $strict = false ) {
		$values = array();
		foreach ( (array)$columns AS $field ) {
			if ( $this->_exists($field) ) {
				$values[$field] = $this->$field;
			}
			else if ( $strict ) {
				return false;
			}
		}
		return $values;
	}


	/**
	 * 
	 */
	protected function __getter( $key ) {
		$getter = $this::$_getters[$key];
		$type = $getter[0];
		$cache = $getter[1];
		$class = $function = $getter[2];
		switch ( $type ) {
			case self::GETTER_ONE:
			case self::GETTER_ALL:
			case self::GETTER_FIRST:
				$localColumns = (array)$getter[3];
				$localValues = $this->_values($localColumns);
//print_r($localValues);
//				$localValues = array_values($localValues); // is this necessary?

				$foreignTable = $class::$_table; // does this work? $class might (syntactically) as well be an object.
				$foreignColumns = (array)$getter[4];
//				$foreignColumn = static::$_db->aliasPrefix($foreignTable, $foreignColumn);
//				$foreignClause = $foreignColumn . ' = ' . ;

				$conditions = array_combine($foreignColumns, $localValues);
//print_r($conditions);
				$conditions = static::dbObject()->stringifyConditions($conditions, 'AND', $foreignTable);
//var_dump($conditions);
				$retrievalMethods = array(
					self::GETTER_ONE => '_one',
					self::GETTER_ALL => '_all',
					self::GETTER_FIRST => '_first',
				);
				$retrievalMethod = $retrievalMethods[$type];
//var_dump($retrievalMethod);
				$r = call_user_func(array($class, $retrievalMethod), $conditions);
//var_dump($r);
				if ( $cache ) {
					$this->$key = $r;
				}
				return $r;
			break;
			case self::GETTER_FUNCTION:
				$r = $this->$function();
//var_dump($r);
				if ( $cache ) {
					$this->$key = $r;
				}
				return $r;
			break;
		}
		// if you're here, you cheat
	}

	/**
	 * 
	 */
	public function __get( $key ) {
		if ( isset($this::$_getters[$key]) ) {
			return $this->__getter($key);
		}
		else if ( $this->_exists($key) ) {
			return $this->$key;
		}
	}


	/**
	 * 
	 */
	public function update( $updates ) {
		if ( !is_scalar($updates) ) {
			$this->_fill((array)$updates);
			$this->_fire('post_fill', array($init));
		}
		$conditions = $this->_pkValue(true);
//print_r($conditions); exit;
		return $this::_update($updates, $conditions);
	}

	/**
	 * 
	 */
	public function delete() {
		return $this::_delete($this->_pkValue(true));
	}


	public function isEmpty() {
		return (array)$this == array();
	}


} // END Class Model


