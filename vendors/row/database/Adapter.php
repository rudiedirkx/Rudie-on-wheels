<?php

namespace row\database;

use row\core\Object;
use row\utils\Options;

abstract class Adapter extends Object {

	public function __tostring() {
		return get_class($this).' database adapter';
	}

	static public $paramPlaceholder = '?';
	public function replaceholders( $conditions, $params ) {
		$conditions = $this->stringifyConditions($conditions);
		if ( !$params ) return $conditions;
		$ph = static::$paramPlaceholder;
//		$conditions = str_replace($ph, $this->quoteValue('%s'), $conditions);
		$offset = 0;
		foreach ( (array)$params AS $param ) {
			$pos = strpos($conditions, $ph, $offset);
			if ( false === $pos ) break;
			$param = $this->escapeAndQuote($param);
			$conditions = substr_replace($conditions, $param, $pos, strlen($ph));
			$offset = $pos + strlen($param);
		}
		return $conditions;
	}

	static public $_adapters = array('MySQL', 'MySQLi', 'SQLite', 'PDOSQLite', /*'SQLite3'*/);

	/* Reflection */ // Should this be put somewhere else?
	abstract public function _getTables();
	abstract public function _getTableColumns( $table );

	abstract static public function initializable();
	abstract public function connect();
	public function connected() {
		return is_object($this->query('SELECT 1'));
	}

	abstract public function escapeValue( $value );
	abstract public function fetch( $query, $class = null, $justFirst = false );
	abstract public function query( $query );
	abstract public function execute( $query ); // Just like PDO =(
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

	public function __construct( $connection, $connect = true ) {
		$this->connectionArgs = Options::make($connection, Options::make(array('host' => 'localhost')));
		if ( $connect ) {
			$this->connect();
			$this->connectionArgs = null;
		}
		$this->_fire('init');
	}

	public function _post_connect() {
		if ( $this->connectionArgs->names ) {
			$this->execute('SET NAMES \''.$this->connectionArgs->names.'\'');
		}
		else if ( $this->connectionArgs->charachter_set ) {
			$this->execute('SET CHARACTER SET \''.$this->connectionArgs->charachter_set.'\'');
		}
	}

	public function select( $table, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT * FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetch($query);
	}

	public function selectByField( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT * FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		return $this->fetchByField($query, $field);
	}

	public function fetchFields( $query ) {
		return $this->fetchFieldsAssoc($query);
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


