<?php

namespace row\database;

//use row\core\Extendable AS ModelParent;
use row\core\Object AS ModelParent;
use row\database\Adapter; // abstract
use row\core\RowException;
use row\core\Chain;

class ModelException extends RowException {}

abstract class Model extends ModelParent {

	static public $chain;

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
//		'author' => array( self::GETTER_ONE, true, 'User', 'author_id', 'user_id' ), // Exactly one ('mandatory') connected User
//		'comments' => array( self::GETTER_ALL, true, 'Comment', 'post_id', 'post_id' ), // >= 0 Comment objects
//		'first_comment' => array( self::GETTER_FIRST, true, 'Comment', 'post_id', 'parent_post_id' ), // First available comment (might not exist)
//		'language' => array( self::GETTER_FIRST, true, 'Language', 'primary_language_id', 'language_id' ), // Optional language (might not be set (eg NULL))
//		'followers' => array( self::GETTER_FUNCTION, true, 'getFollowerUserObjects' ), // Execute and return custom code
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
	static public function _byQuery( $query, $justFirst = false, $params = array() ) {
		// don't require $justFirst, so params can be passed as 2nd argument (in $justFirst)
		if ( is_array($justFirst) ) {
			$_jf = $justFirst;
			// allow reverse arguments: justFirst in $params
			$justFirst = is_bool($params) ? $params : false;
			$params = $_jf;
		}

		$query = static::dbObject()->replaceholders($query, $params);

		$class = get_called_class();
		if ( class_exists($class.'Record') && is_a($class.'Record', get_called_class()) ) {
			$class = $class.'Record';
		}

		return static::dbObject()->fetch($query, array(
			'class' => $class,
			'single' => $justFirst,
			'params' => $params
		));
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
		$conditions = static::dbObject()->replaceholders($conditions, $params);

		/* experimental */
		if ( false !== static::$_cache ) {
			$_c = get_called_class();
			if ( isset(static::$_cache[$_c][$conditions]) ) {
				return static::$_cache[$_c][$conditions];
			}
		}
		/* experimental */

		$query = static::_query($conditions);
		$query = static::dbObject()->addLimit($query, 2);

		$objects = static::_byQuery($query);
		if ( !isset($objects[0]) || isset($objects[1]) ) {
			throw new ModelException('Found '.( !isset($objects[0]) ? '<' : '>' ).' 1 of '.get_called_class().'.');
		}

		$r = $objects[0];

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
		$conditions = static::dbObject()->replaceholders($conditions, $params);
		$query = static::_query($conditions);
		$r = static::_byQuery($query, true);
		return $r;
	}

	/**
	 * 
	 */
	static public function _get( $pkValues, $moreConditions = '', $params = array() ) {
		$pkValues = (array)$pkValues;

		$pkColumns = (array)static::$_pk;
		if ( count($pkValues) !== count($pkColumns) ) {
			throw new ModelException('Invalid number of PK arguments ('.count($pkValues).' instead of '.count($pkColumns).').');
		}
		$pkValues = array_combine($pkColumns, $pkValues);

		$conditions = static::dbObject()->stringifyConditions($pkValues, 'AND', static::$_table);
		if ( $moreConditions ) {
			$conditions .= ' AND '.static::dbObject()->replaceholders($moreConditions, $params);
		}

		$r = static::_one($conditions);

		return $r;
	}

	/**
	 * 
	 */
	static public function _delete( $conditions, $params = array() ) {
		$chain = static::event(__FUNCTION__);
		$chain->first(function($self, $args, $chain, $native = true) {

			// actual methods body //
			if ( $self::dbObject()->delete($self::$_table, $args->conditions, $args->params) ) {
				return $self::dbObject()->affectedRows();
			}
			return $self::dbObject()->except();
			// actual methods body //

		});
		return $chain->start(get_called_class(), options(compact('conditions', 'params')));
	}

	/**
	 * 
	 */
	static public function _update( $values, $conditions, $params = array() ) {
		$chain = static::event(__FUNCTION__);
		$chain->first(function($self, $args, $chain, $native = true) {

			// actual methods body //
			if ( $self::dbObject()->update($self::$_table, $args->values, $args->conditions, $args->params) ) {
				return $self::dbObject()->affectedRows();
			}
			return $self::dbObject()->except();
			// actual methods body //

		});
		return $chain->start(get_called_class(), options(compact('values', 'conditions', 'params')));
	}

	/**
	 * 
	 */
	static public function _insert( $values, $_method = 'insert' ) {
		$chain = static::event('_'.$_method);
		$chain->first(function($self, $args, $chain, $native = true) use ( $_method ) {

			// actual method body //
			if ( $self::dbObject()->$_method($self::$_table, $args->values) ) {
				return (int)$self::dbObject()->insertId();
			}
			return $self::dbObject()->except();
			// actual method body //

		});
		return $chain->start(get_called_class(), options(compact('values')));
	}

	/**
	 * 
	 */
	static public function _replace( $values ) {
		return static::_insert($values, 'replace');
	}

	/**
	 * 
	 */
	static public function _count( $conditions = '', $params = array() ) {
		return static::dbObject()->count(static::$_table, $conditions, $params);
	}


	/**
	 * 
	 */
	public function __construct( $init = false ) {
		$chain = static::event('construct');
		$chain->first(function($self, $args, $chain) {

			// actual method body //
			if ( true === $args->init || is_array($args->init) ) {
				$self->_fill($args->init);
			}

			$self->_fire('init');
			// actual method body //

			// no chain->next
			// no return (cos it's __construct)
		});
		return $chain->start($this, options(compact('init')));
	}

	/**
	 * 
	 */
	public function _fill( $data ) {
		$chain = static::event('fill');
		$chain->first(function($self, $args, $chain, $native = true) {

			// actual methods body //
			if ( is_array($args->data) ) {
				foreach ( (array)$args->data AS $k => $v ) {
					if ( $k || '0' === (string)$k ) {
						$self->$k = $v;
					}
				}
			}

			$self->_fire('post_fill');
			// actual methods body //

		});
		return $chain->start($this, options(compact('data')));
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


				/* experimental */
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
//var_dump($class, $cc.'->'.$key, $_name);

				$_parent = $this;
				$evName = 'tmpEvent'.rand(0, 999);

				$_chain = $class::event('construct');
//echo "\n[ ".$evName." ".count($_chain->events)." events PRE ]\n";
				$_chain->add(function( $self, $args, $chain, $semiNative = true ) use ($_parent, $_name, $class) {
					$r = $chain($self, $args);
					$self->$_name = $_parent;
					return $r;
				}, $evName);
//echo "\n[ ".$evName." ".count($_chain->events)." events +1 ]\n";
				/* experimental */


				// Get object(s)
				$r = call_user_func(array($class, $retrievalMethod), $conditions);


				/* experimental */
//echo "\n[ ".$evName." ".count($_chain->events)." events SAME AS +1 ]\n";
				$_chain->remove($evName);
//echo "\n[ ".$evName." ".count($_chain->events)." events POST: -1 ]\n";
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
		return $this->_chain(__FUNCTION__, function($self, $args, $chain, $native = true) {

			// actual method body //
			if ( is_array($args->values) ) {
				$self->_fill((array)$args->values);
			}
			$conditions = $self->_pkValue(true);
			return $self::_update($args->values, $conditions);
			// actual method body //

		}, compact('values'));
	}

	/**
	 * 
	 */
	public function delete() {
		return $this->_chain(__FUNCTION__, function($self, $args, $chain, $native = true) {

			// actual method body //
			return $self::_delete($self->_pkValue(true));
			// actual method body //

		});
	}


	/**
	 * 
	 */
	public function isEmpty() {
		return (array)$this == array();
	}


	/**
	 * 
	 */
	public function toArray() {
		$arr = (array)$this;
		foreach ( static::$_getters AS $k => $x ) {
			unset($arr[$x]);
		}
		return $arr;
	}


} // END Class Model


