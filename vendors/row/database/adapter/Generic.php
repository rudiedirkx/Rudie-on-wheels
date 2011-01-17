<?php

namespace row\database\adapter;

abstract class Generic /*implements DatabaseAdapter*/ {

	protected $alias_delim = '.'; // [table] "." [column]
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

	public function aliasPrefix( $alias, $column ) {
		return $this->escapeAndQuoteTable($alias) . static::$alias_delim . $this->escapeAndQuoteColumn($column);
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


