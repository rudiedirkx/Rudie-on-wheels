<?php

namespace row\database;

use row\database\Adapter;
use row\core\Options;

abstract class SQLAdapter extends Adapter {

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
			$param = $this->escapeAndQuoteValue($param);
			$conditions = substr_replace($conditions, $param, $pos, strlen($ph));
			$offset = $pos + strlen($param);
		}
		return $conditions;
	}

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


