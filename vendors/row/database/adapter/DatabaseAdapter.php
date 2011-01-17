<?php

namespace row\database\adapter;

use row\core\Object;

abstract class DatabaseAdapter extends Object {

	abstract public function connect();
	abstract public function escapeValue( $value );
	abstract public function fetch( $query );
	abstract public function query( $query );
	abstract public function error();
	abstract public function errno();
	abstract public function affected_rows();
	abstract public function insert_id();

	static protected $alias_delim = '.'; // [table] "." [column]
	protected $db;
	protected $connection;

	static public function open( $connection, $connect = true ) {
		return new static($connection, $connect);
	}

	protected function __construct( $connection, $connect = true ) {
		$this->connection = $connection;
		if ( $connect ) {
			$this->connect();
			$this->connection = null;
		}
	}

	public function update( $table, $updates, $conditions ) {
		if ( !is_string($updates) ) {
			$updates = $this->stringifyUpdates($updates);
		}
		if ( !is_string($conditions) ) {
			$conditions = $this->stringifyConditions($conditions);
		}
		$sql = 'UPDATE '.$table.' SET '.$updates.' WHERE '.$conditions.'';
		return $this->query($sql);
	}

	public function aliasPrefix( $alias, $column ) {
		return $this->escapeAndQuoteTable($alias) . $this::$alias_delim . $this->escapeAndQuoteColumn($column);
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

	public function stringifyUpdates( $updates ) {
		$u = '';
		foreach ( (array)$updates AS $k => $v ) {
			$u .= ',' . $k . '=' . $this->escapeAndQuoteValue($v);
		}
		return substr($u, 1);
	}

	public function stringifyConditions( $conditions, $delim = 'AND', $table = null ) {
		$sql = array();
		foreach ( $conditions AS $column => $value ) {
			$column = $table ? $this->aliasPrefix($table, $column) : $this->escapeAndQuoteColumn($column);
			$sql[] = $column . ( null === $value ? ' IS NULL' : ' = ' . $this->escapeAndQuoteValue($value) );
		}
		$sql = implode(' '.trim($delim).' ', $sql);
		return $sql;
	}

	public function addLimit( $sql, $limit, $offset = 0 ) {
		if ( 0 == $offset ) {
			return $sql.' LIMIT '.$limit;
		}
		return $sql.' LIMIT '.$offset.', '.$limit;
	}

}


