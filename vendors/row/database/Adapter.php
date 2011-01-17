<?php

namespace row\database;

use row\core\Object;
use row\utils\Options;

abstract class Adapter extends Object {

	/* Reflection */ // Should this be put somewhere else?
	abstract public function _getTables();
	abstract public function _getTableColumns( $table );

	abstract static public function initializable();

	abstract public function connect();
	abstract public function escapeValue( $value );
	abstract public function fetch( $query, $class = null, $justFirst = false );
	abstract public function query( $query );
	abstract public function error();
	abstract public function errno();
	abstract public function affectedRows();
	abstract public function insertId();

	abstract public function fetchFieldsAssoc( $query );
	abstract public function fetchFieldsNumeric( $query );
	abstract public function selectOne( $table, $field, $conditions );
	abstract public function countRows( $query );
	abstract public function fetchByField( $query, $field );

	static protected $aliasDelim = '.'; // [table] "." [column]
	protected $db;
	protected $connectionArgs;
	public $throwExceptions = true;
	public $logErrors = false;

	static public function open( $connection, $connect = true ) {
		return new static($connection, $connect);
	}

	protected function __construct( $connection, $connect = true ) {
		$this->connectionArgs = Options::make($connection, Options::make(array('host' => 'localhost')));
		if ( $connect ) {
			$this->connect();
			$this->connectionArgs = null;
		}
	}

	public function select( $table, $conditions ) {
		$conditions = $this->stringifyConditions($conditions);
		$query = 'SELECT * FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetch($query);
	}

	public function selectByField( $table, $field, $conditions ) {
		$conditions = $this->stringifyConditions($conditions);
		$query = 'SELECT * FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchByField($query, $field);
	}

	public function fetchFields( $query ) {
		return $this->fetchFieldsAssoc($query);
	}

	public function selectFields( $table, $fields, $conditions ) {
		return $this->selectFieldsAssoc($table, $fields, $conditions);
	}

	public function selectFieldsAssoc( $table, $fields, $conditions ) {
		if ( !is_string($fields) ) {
			$fields = implode(', ', array_map(array($this, 'escapeAndQuoteColumn'), (array)$fields));
		}
		$query = 'SELECT '.$fields.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchFieldsAssoc($query);
	}

	public function selectFieldsNumeric( $table, $field, $conditions ) {
		$conditions = $this->stringifyConditions($stringifyConditions);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchFieldsNumeric($query);
	}

	public function replace($table, $values) {
		$values = array_map(array($this, 'escapeAndQuoteValue'), $values);
		$sql = 'REPLACE INTO '.$this->escapeAndQuoteTable($table).' ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
		return $this->query($sql);
	}

	public function insert($table, $values) {
		$values = array_map(array($this, 'escapeAndQuoteValue'), $values);
		$sql = 'INSERT INTO '.$this->escapeAndQuoteTable($table).' ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
		return $this->query($sql);
	}

	public function delete($table, $conditions) {
		$conditions = $this->stringifyConditions($conditions);
		$sql = 'DELETE FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions.';';
		return $this->query($sql);
	}

	public function update( $table, $updates, $conditions ) {
		$updates = $this->stringifyUpdates($updates);
		$conditions = $this->stringifyConditions($conditions);
		$sql = 'UPDATE '.$this->escapeAndQuoteTable($table).' SET '.$updates.' WHERE '.$conditions.'';
		return $this->query($sql);
	}

	public function aliasPrefix( $alias, $column ) {
		return $this->escapeAndQuoteTable($alias) . $this::$aliasDelim . $this->escapeAndQuoteColumn($column);
	}

	public function quoteValue( $value ) {
		return "'".$value."'";
	}

	public function escapeAndQuoteValue( $value ) {
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


