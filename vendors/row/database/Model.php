<?php

namespace row\database;

//use row\core\Extendable AS ModelParent;
use row\core\Object AS ModelParent;
use row\database\Adapter; // abstract
use row\core\RowException;
use row\core\Chain;

class ModelException extends RowException {}

class Model extends ModelParent {

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


	static public $_on = array();

	static public $_cache = array();


	/**
	 * Enables calling of Post::update with defined function _update
	 */
	static public function __callStatic( $func, $args ) {
		if ( '_' != $func{0} ) {
			$func = '_'.$func;
		}
		if ( !method_exists(get_called_class(), $func) ) {
			throw new ModelException('Methodo "'.$func.'" no existo!');
		}
		return call_user_func_array(array('static', $func), $args);
	} // END __callStatic() */


	/**
	 * 
	 */
	public static function _query( $conditions ) {
		return 'SELECT * FROM '.static::$_table.' WHERE '.$conditions; // .' /*'.get_called_class().'*/';
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
		$conditions = static::dbObject()->stringifyConditions($conditions);

		/* experimental */
		if ( false !== static::$_cache ) {
			$_c = get_called_class();
			if ( isset(static::$_cache[$_c][$conditions]) ) {
				return static::$_cache[$_c][$conditions];
			}
		}
		/* experimental */

		$query = static::_query($conditions);
		$r = static::_byQuery($query, true);
		$c = $r->count();
		if ( 1 !== $c ) {
			throw new ModelException('Found '.$c.' of '.get_called_class().'.');
		}

		$r = $r->nextObject($r->class, array(true));

		/* experimental */
		if ( false !== static::$_cache ) {
			static::$_cache[$_c][$conditions] = $r;
		}
		/* experimental */

		return $r;
	}

	/**
	 * Returns null or the first object with the matching conditions
	 */
	static public function _first( $conditions, $params = array() ) {
		$conditions = static::dbObject()->stringifyConditions($conditions);
		$query = static::_query($conditions);
		$r = static::_byQuery($query, true);
		return $r->nextObject($r->class, array(true));
	}

	/**
	 * 
	 */
	static public function _get( $pkValues, $moreConditions = false, $params = array() ) {
		$pkValues = (array)$pkValues;

		$pkColumns = (array)static::$_pk;
		if ( count($pkValues) !== count($pkColumns) ) {
			throw new ModelException('Invalid number of PK arguments ('.count($pkValues).' instead of '.count($pkColumns).').');
		}
		$pkValues = array_combine($pkColumns, $pkValues);

		$conditions = static::dbObject()->stringifyConditions($pkValues, 'AND', static::$_table);
		if ( $moreConditions ) {
			$conditions .= ' AND '.static::dbObject()->stringifyConditions($moreConditions);
		}

		$r = static::_one($conditions);

		return $r;
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
	static public function _update( $values, $conditions, $params = array() ) {
		$chain = static::event(__FUNCTION__);
		$chain->first(function($self, $args, $chain) {

			// actual methods body //
			if ( $self::dbObject()->update($self::$_table, $args->values, $args->conditions, $args->params) ) {
				return $self::dbObject()->affectedRows();
			}
			return false;
			// actual methods body //

		});
		return $chain(get_called_class(), options(compact('values', 'conditions', 'params')));
	}

	/**
	 * 
	 */
	static public function _insert( $values ) {
		$chain = static::event(__FUNCTION__);
		$chain->first(function($self, $args, $chain) {

			// actual methods body //
			if ( $self::dbObject()->insert($self::$_table, $args->values) ) {
				return (int)$self::dbObject()->insertId();
			}
			return false;
			// actual methods body //

		});
		return $chain(get_called_class(), options(compact('values')));
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
		if ( true === $init || is_array($init) ) {
			$this->_fill($init);
		}

		$chain = static::event('construct');
		return $chain($this, options(compact('init')));
	}

	/**
	 * 
	 */
	public function _fill( $data ) {
		$chain = static::event('fill');
		$chain->first(function($self, $args, $chain) {

			// actual methods body //
			if ( is_array($args) ) {
				foreach ( (array)$args->data AS $k => $v ) {
					if ( $k || '0' === (string)$k ) {
						$self->$k = $v;
					}
				}
			}
			// actual methods body //

		});
		return $chain($this, options(compact('data')));
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

				$foreignTable = $class::$_table;
				$foreignColumns = (array)$getter[4];

				$conditions = array_combine($foreignColumns, $localValues);
				$conditions = static::dbObject()->stringifyConditions($conditions, 'AND', $foreignTable);
				$retrievalMethods = array(
					self::GETTER_ONE => '_one',
					self::GETTER_ALL => '_all',
					self::GETTER_FIRST => '_first',
				);
				$retrievalMethod = $retrievalMethods[$type];


				/* experimental *
				$_name = '_parent';
				if ( isset($getter[4]) ) {
					$cc = get_called_class();
					foreach ( $class::$_getters AS $name => $gt ) {
						if ( isset($gt[4]) ) {
							if ( in_array($gt[0], array(self::GETTER_ONE, self::GETTER_FIRST)) ) {
								if ( $gt[2] == $cc && $gt[3] == $getter[4] && $gt[4] == $getter[3] ) {
									$_name = $name;
									break;
								}
							}
						}
					}
				}
//var_dump($cc.'->'.$key, $_name);
				$_parent = $this;
				$eventIndex = $class::event('construct', function( $self ) use ($_parent, $_name) {
					$self->$_name = $_parent;
				});
var_dump($class, $eventIndex);
				/* experimental */


				$r = call_user_func(array($class, $retrievalMethod), $conditions);


				/* experimental */
				unset($class::$_on['init']['_model']);
				/* experimental */


				if ( $cache ) {
					$this->$key = $r;
				}
				return $r;
			break;
			case self::GETTER_FUNCTION:
				$r = $this->$function();
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
	public function update( $values ) {
		$chain = static::event(__FUNCTION__);
		$chain->first(function($self, $args, $chain) {

			// actual methods body //
			if ( !is_scalar($args->values) ) {
				$self->_fill((array)$args->values);
			}
			$conditions = $self->_pkValue(true);
print_r($args->values, $conditions);
			return $self::_update($args->values, $conditions);
			// actual methods body //

		});
		return $chain($this, options(compact('values')));
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


