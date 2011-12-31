<?php

namespace row\database;

use row\core\Vendors;
use row\core\Options;
use row\core\RowException;

class DatabaseException extends RowException {}

/**
 * An Adapter has easy and advanced access to its database:
 *   ::fetch	Execute query and return all rows
 *   ::count	Execute query and count natively
 *   ::select	Simpler access (shortcut) for ->fetch
 *   ::result	Execute query and return QueryResult object
 */

abstract class Adapter extends \row\core\Object {

	/* Reflection */ // Should this be put somewhere else?
	abstract public function _getTables();
	abstract public function _getTableColumns( $table );
	abstract public function _getPKColumns( $table );

	abstract static public function initializable();
	abstract public function connect();
	public function connected() {
		return is_object($this->query('SELECT 1'));
	}

	abstract public function escapeValue( $value );

	public $queries = array();
	protected $db;
	protected $_connected;
	protected $connectionArgs;
	public $throwExceptions = true;
	public $logErrors = false;

	static public function open( $connection, $connect = true ) {
		return new static($connection, $connect);
	}

	public function __construct( $connection, $connect = true ) {
		$this->connectionArgs = Options::make($connection, Options::make(array('host' => 'localhost')));
		if ( $connect ) {
			$this->connect();
			$this->connectionArgs = null;
		}
		$this->_fire('init');
	}

	public function quoteValue( $value ) {
		return "'".$value."'";
	}

	public function escapeAndQuoteValue( $value ) {
		if ( null === $value ) {
			return 'NULL';
		}
		if ( is_bool($value) ) {
			return (int)$value;
		}
		return $this->quoteValue($this->escapeValue($value));
	}

	public function escapeTable( $table ) {
		return $table;
	}

	public function quoteTable( $table ) {
		return $table;
	}

	public function escapeAndQuoteTable( $table ) {
		return $this->quoteTable($this->escapeTable($table));
	}

	public function escapeColumn( $column ) {
		return $column;
	}

	public function quoteColumn( $column ) {
		return $column;
	}

	public function escapeAndQuoteColumn( $column ) {
		return $this->quoteColumn($this->escapeColumn($column));
	}

	public function except( $msg = null ) {
		if ( $this->throwExceptions ) {
			$msg or $msg = $this->error();
			throw new DatabaseException($msg);
		}
		return false;
	}

	static public $paramPlaceholder = '?';

	public function replaceholders( $conditions, $params ) {
		$conditions = $this->stringifyConditions($conditions);

		if ( array() === $params || null === $params ) {
			return $conditions;
		}

		$ph = static::$paramPlaceholder;
		$offset = 0;
		foreach ( (array)$params AS $param ) {
			$pos = strpos($conditions, $ph, $offset);
			if ( false === $pos ) {
				break;
			}
			$param = is_array($param) ? implode(', ', array_map(array($this, 'escapeAndQuoteValue'), $param)) : $this->escapeAndQuoteValue((string)$param);
			$conditions = substr_replace($conditions, $param, $pos, strlen($ph));
			$offset = $pos + strlen($param);
		}

		return $conditions;
	}

	public function buildSQL( $query ) {
		$query = options($query);

		$fields = implode(', ', (array)$query->get('fields', '*'));

		$fn_join = function($details) use(&$fn_join) {
			$join = strtoupper(array_shift($details)) . ' ';

			if ( is_int(key($details)) ) {
				// a table: exit join
				$join .= array_shift($details);

				// conditions
				if ( $details ) {
					$join .= ' ON (' . implode(' AND ', (array)array_shift($details)) . ')';
				}
			}
			else {
				// another join!
				$join .= key($details);

				// next join details
				$join2 = array_shift($details);

				// conditions
				if ( $details ) {
					$join .= ' ON (' . implode(' AND ', (array)array_shift($details)) . ')';
				}

				$join .= ' ' . $fn_join($join2);
			}

			return $join;
		};

		$tables = array();
		foreach ( (array)$query->tables AS $i => $table ) {
			if ( is_scalar($table) ) {
				$tables[] = $table;
			}
			else {
				$join = $i;

				$join .= ' ' . $fn_join($table);

				$tables[] = $join;
			}
		}
		$tables = implode(', ', $tables);

		$sql = 'SELECT ' . $fields . ' FROM ' . $tables;

		if ( $query->conditions ) {
			$db = $this;
			$conditions = array_map(function( $condition ) use ( $db ) {
				if ( is_string($condition) ) {
					return $condition;
				}
				return $db->replaceholders($condition[0], $condition[1]);
			}, $query->conditions);
			$conditions = implode(' AND ', $conditions);
			$sql .= ' WHERE ' . $conditions;
		}

		if ( $query->limit ) {
			$sql .= ' LIMIT '.$query->limit[0].', '.$query->limit[1];
		}

		return $sql;
	}

	public function fetch( $query, $mixed = null ) {
		// default options
		$class = false;
		$justFirst = false;
		$params = array();

		// unravel options
		if ( is_array($mixed) ) {
			if ( is_int(key($mixed)) ) {
				$params = $mixed;
			}
			else {
				$class = Options::one($mixed, 'class', false);
				$justFirst = $mixed->get('single', $mixed->get('first', false));
				$params = $mixed->get('params', array());
			}
		}
		else if ( is_bool($mixed) ) {
			$justFirst = $mixed;
		}
		else if ( is_string($mixed) ) {
			$class = $mixed;
		}

		// build SQL
		if ( is_array($query) ) {
			$query = $this->buildSQL($query);
		}

		// apply params
		if ( $params ) {
			$query = $this->replaceholders($query, $params);
		}

		$result = $this->result($query);
		Vendors::class_exists($class) or $class = false;

		if ( $justFirst ) {
			if ( $class ) {
				return $result->nextObject($class, array(true));
			}
			return $result->nextAssocArray();
		}

		if ( $class ) {
			return $result->allObjects($class, array(true));
		}

		return $result->allAssocArrays();
	}

	public function result( $query, $targetClass = '' ) {
		$resultClass = get_class($this).'Result';
		return $resultClass::make($this->query($query), $targetClass, $this);
	}

	abstract public function query( $query );
	abstract public function execute( $query ); // Just like PDO =(
	abstract public function error();
	abstract public function errno();
	abstract public function affectedRows();
	abstract public function insertId();


	public function fetchFields( $query ) {
		return $this->fetchFieldsAssoc($query);
	}

	public function fetchFieldsAssoc( $query ) {
		$r = $this->result($query);
		if ( !is_object($r) ) {
			return false;
		}
		$a = array();
		while ( $l = $r->nextRow() ) {
			$a[$l[0]] = $l[1];
		}
		return $a;
	}

	public function fetchFieldsNumeric( $query ) {
		$r = $this->result($query);
		if ( !is_object($r) ) {
			return false;
		}
		$a = array();
		while ( $l = $r->nextRow() ) {
			$a[] = $l[0];
		}
		return $a;
	}

	public function selectOne( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		$r = $this->result($query);
		if ( !$r ) {
			return false;
		}
		return $r->singleResult();
	}

	public function countRows( $query ) {
		$r = $this->fetch($query);
		if ( !$r ) {
			return false;
		}
		return count($r);
	}

	public function fetchByField( $query, $field ) {
		$r = $this->result($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = $r->nextRecord() ) {
			if ( !array_key_exists($field, $l) ) {
				return $this->except('Undefined index: "'.$field.'"');
			}
			$a[$l[$field]] = $l;
		}
		return $a;
	}

	static protected $aliasDelim = '.'; // [table] "." [column]

	public function select( $table, $conditions, $params = array(), $justFirst = false ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT * FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetch($query, (bool)$justFirst);
	}

	public function selectByField( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT * FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchByField($query, $field);
	}

	public function selectFields( $table, $fields, $conditions, $params = array() ) {
		return $this->selectFieldsAssoc($table, $fields, $conditions);
	}

	public function selectFieldsAssoc( $table, $fields, $conditions, $params = array() ) {
		if ( !is_string($fields) ) {
			$fields = implode(', ', array_map(array($this, 'escapeAndQuoteColumn'), (array)$fields));
		}
		$query = 'SELECT '.$fields.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchFieldsAssoc($query);
	}

	public function selectFieldsNumeric( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchFieldsNumeric($query);
	}

	public function count( $table, $conditions = '', $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params) ?: '1';
		$r = (int)$this->selectOne($table, 'count(1)', $conditions);
		return $r;
		// the following won't work for PDO, which means PDO has to do an additional `select count(1)`. PDO sucks!
//		$sql = 'SELECT 1 FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
//		return $this->countRows($sql);
	}

	public function replace( $table, $values ) {
		$values = array_map(array($this, 'escapeAndQuoteValue'), $values);
		$sql = 'REPLACE INTO '.$this->escapeAndQuoteTable($table).' ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
		return $this->execute($sql);
	}

	public function insert( $table, $values ) {
		$values = array_map(array($this, 'escapeAndQuoteValue'), $values);
		$sql = 'INSERT INTO '.$this->escapeAndQuoteTable($table).' ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
		return $this->execute($sql);
	}

	public function delete( $table, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$sql = 'DELETE FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions.';';
		return $this->execute($sql);
	}

	public function update( $table, $updates, $conditions, $params = array() ) {
		$updates = $this->stringifyUpdates($updates);
		$conditions = $this->replaceholders($conditions, $params);
		$sql = 'UPDATE '.$this->escapeAndQuoteTable($table).' SET '.$updates.' WHERE '.$conditions.'';
//var_dump($sql); exit;
		return $this->execute($sql);
	}

	public function aliasPrefix( $alias, $column ) {
		return $this->escapeAndQuoteTable($alias) . $this::$aliasDelim . $this->escapeAndQuoteColumn($column);
	}

	public function stringifyColumns( $columns ) {
		if ( !is_string($columns) ) {
			$columns = implode(', ', array_map(array($this, 'escapeAndQuoteColumn'), (array)$columns));
		}
		return $columns;
	}

	public function stringifyUpdates( $updates ) {
		if ( !is_string($updates) ) {
			$u = '';
			foreach ( (array)$updates AS $k => $v ) {
				if ( is_int($k) ) {
					$u .= ', ' . $v;
				}
				else {
					$u .= ', ' . $k . ' = ' . $this->escapeAndQuoteValue($v);
				}
			}
			$updates = substr($u, 1);
		}
		return $updates;
	}

	public function stringifyConditions( $conditions, $delim = 'AND', $table = null ) {
		if ( !is_string($conditions) ) {
			$sql = array();
			foreach ( (array)$conditions AS $column => $value ) {
				if ( is_array($value) ) {
					$cond = array_shift($value);
					$cond = $this->replaceholders($cond, $value);
					if ( !is_int($column) ) {
						$column = $table ? $this->aliasPrefix($table, $column) : $this->escapeAndQuoteColumn($column);
						$cond = $column . ' = ' . $cond;
					}
					$sql[] = $cond;
				}
				else if ( is_int($column) ) {
					$sql[] = $value;
				}
				else {
					$column = $table ? $this->aliasPrefix($table, $column) : $this->escapeAndQuoteColumn($column);
					$sql[] = $column . ( null === $value ? ' IS NULL' : ' = ' . $this->escapeAndQuoteValue($value) );
				}
			}
			$conditions = implode(' '.$delim.' ', $sql);
		}
		return $conditions;
	}

	public function addLimit( $query, $limit, $offset = 0 ) {
		if ( is_array($query) ) {
			$query['limit'] = array($offset, $limit);

			return $query;
		}

		if ( 0 == $offset ) {
			return $query.' LIMIT '.$limit;
		}
		return $query.' LIMIT '.$offset.', '.$limit;
	}

}


