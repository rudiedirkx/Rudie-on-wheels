<?php

namespace row\database;

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

	protected function except( $msg ) {
		if ( $this->throwExceptions ) {
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
//		$conditions = str_replace($ph, $this->quoteValue('%s'), $conditions);
		$offset = 0;
		foreach ( (array)$params AS $param ) {
			$pos = strpos($conditions, $ph, $offset);
			if ( false === $pos ) break;
			$param = is_array($param) ? implode(', ', array_map(array($this, 'escapeAndQuoteValue'), $param)) : $this->escapeAndQuoteValue($param);
			$conditions = substr_replace($conditions, $param, $pos, strlen($ph));
			$offset = $pos + strlen($param);
		}
		return $conditions;
	}

	public function fetch( $query, $class = null, $justFirst = false ) {
		$result = $this->result($query);
		class_exists($class) or $class = false;
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
		if ( !$r || !$r->count() ) {
			return false;
		}
		return $r->singleResult();
	}

	public function countRows( $query ) {
		$r = $this->result($query);
		if ( !$r ) {
			return false;
		}
		return $r->count();
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
		return $this->fetch($query, null, $justFirst);
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
		$conditions = $this->replaceholders($conditions, $params);
		$sql = 'SELECT 1 FROM '.$this->escapeAndQuoteTable($table).' WHERE '.( $conditions ?: '1' );
		return $this->countRows($sql);
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
				$u .= ',' . $k . '=' . $this->escapeAndQuoteValue($v);
			}
			$updates = substr($u, 1);
		}
		return $updates;
	}

	public function stringifyConditions( $conditions, $delim = 'AND', $table = null ) {
		if ( !is_string($conditions) ) {
			$sql = array();
			foreach ( (array)$conditions AS $column => $value ) {
				$column = $table ? $this->aliasPrefix($table, $column) : $this->escapeAndQuoteColumn($column);
				$sql[] = $column . ( null === $value ? ' IS NULL' : ' = ' . $this->escapeAndQuoteValue($value) );
			}
			$conditions = implode(' '.$delim.' ', $sql);
		}
		return $conditions;
	}

	public function addLimit( $sql, $limit, $offset = 0 ) {
		if ( 0 == $offset ) {
			return $sql.' LIMIT '.$limit;
		}
		return $sql.' LIMIT '.$offset.', '.$limit;
	}

}


