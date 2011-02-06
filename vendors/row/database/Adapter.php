<?php

namespace row\database;

use row\core\Object;
use row\core\Options;

abstract class Adapter extends Object {

	public function __tostring() {
		return get_class($this).' database adapter';
	}

	static public $_adapters = array('MySQL', 'MySQLi', 'SQLite', 'PDOSQLite', /*'SQLite3', 'pgSQL', 'PDOpgSQL'*/);

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

}


