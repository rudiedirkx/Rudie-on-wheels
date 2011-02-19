<?php

namespace app\controllers;

use app\specs\Controller;
use row\database\adapter\SQLite;
use row\database\adapter\SQLite3;
use row\database\adapter\PDOSQLite;

class sqliteController extends Controller {

	protected function _pre_action() {
		echo '<ul>';
		foreach ( \row\database\Adapter::$_adapters AS $adapter ) {
			$class = 'row\database\adapter\\'.$adapter;
			echo '<li>'.$adapter.': '.(int)$class::initializable().'</li>';
		}
		echo '</ul>';
		echo '<pre>';
	}

	public function index() {
		foreach ( array('v2', 'v2-data', '', 'v3', 'v3-data', '', 'pdo', 'pdo-data', '', 'first-match-structure', 'first-match-data') AS $m ) {
			echo '<li><a href="/'.$this->_dispatcher->_module.'/'.$m.'">'.$m.'</a></li>';
		}
	}

	protected function structure( $sqlite, $table = 'friends' ) {
		try {
			var_dump($sqlite->execute('CREATE TABLE people ( id INT, name TEXT NOT NULL DEFAULT \'\', age INT NOT NULL DEFAULT 0, PRIMARY KEY(id) )'));
		}
		catch ( \row\database\DatabaseException $ex ) {}
		try {
			var_dump($sqlite->execute('CREATE TABLE friends ( person_id INT, friend_id INT, PRIMARY KEY(person_id, friend_id) )'));
		}
		catch ( \row\database\DatabaseException $ex ) {}
		print_r($tables = $sqlite->_getTables());
		if ( !$table ) {
			$table = $tables[array_rand($tables)];
		}
		print_r($sqlite->_getPKColumns($table));
		print_r($sqlite->_getTableColumns($table));
	}

	protected function data( $sqlite, $table = null ) {
		$masterdata = $sqlite->select('sqlite_master', '1 ORDER BY RAND()');
		print_r($masterdata);
	}


	public function first_match_structure( $type = 'sqlite2' ) {
		$dsn = array('path' => ROW_APP_PATH.'/runtime/database.'.$type);
		$sqlite = SQLite::open($dsn);
		return $this->structure($sqlite);
	}

	public function first_match_data( $type = 'sqlite2' ) {
		$dsn = array('path' => ROW_APP_PATH.'/runtime/database.'.$type);
		$sqlite = SQLite::open($dsn);
		return $this->data($sqlite);
	}

	public function v2( $return = null ) {
		$sqlite = new SQLite(array('path' => ROW_APP_PATH.'/runtime/database.sqlite2'));
		if ( true === $return ) return $sqlite;
		return $this->structure($sqlite, $return);
	}

	public function v3( $return = null ) {
		$sqlite = new SQLite3(array('path' => ROW_APP_PATH.'/runtime/database.sqlite3'));
		if ( true === $return ) return $sqlite;
		return $this->structure($sqlite, $return);
	}

	public function pdo( $return = null ) {
		$sqlite = new PDOSQLite(array('path' => ROW_APP_PATH.'/runtime/database.pdosqlite'));
		if ( true === $return ) return $sqlite;
		return $this->structure($sqlite, $return);
	}

	public function v2_data( $table = null ) {
		$sqlite = $this->v2(true);
		return $this->data($sqlite, $table);
	}

	public function v3_data( $table = null ) {
		$sqlite = $this->v3(true);
		return $this->data($sqlite, $table);
	}

	public function pdo_data( $table = null ) {
		$sqlite = $this->pdo(true);
		return $this->data($sqlite, $table);
	}

}


