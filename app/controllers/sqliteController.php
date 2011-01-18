<?php

namespace app\controllers;

use app\controllers\ControllerParent;
use row\database\adapter\SQLite;
use row\database\adapter\SQLite3;
use row\database\adapter\PDOSQLite;

class sqliteController extends ControllerParent {

	protected function _pre_action() {
		echo '<pre>';
	}

	public function index() {
		foreach ( array('v2', 'v2-data', '', 'v3', 'v3-data', '', 'pdo', 'pdo-data', '', 'first-match-structure', 'first-match-data') AS $m ) {
			echo '<li><a href="/'.$this->_dispatcher->_module.'/'.$m.'">'.$m.'</a></li>';
		}
	}

	protected function structure( $sqlite ) {
		var_dump($sqlite);
		var_dump($sqlite->connected());
		var_dump($sqlite->execute('CREATE TABLE people ( id INT, name TEXT NOT NULL DEFAULT \'\', age INT NOT NULL DEFAULT 0, PRIMARY KEY(id) )'));
	}

	protected function data( $sqlite, $table = null ) {
		$masterdata = $sqlite->select('sqlite_master', '1');
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

	public function v2( $return = false ) {
		$sqlite = new SQLite(array('path' => ROW_APP_PATH.'/runtime/database.sqlite2'));
		if ( $return ) return $sqlite;
		return $this->structure($sqlite);
	}

	public function v3( $return = false ) {
		$sqlite = new SQLite3(array('path' => ROW_APP_PATH.'/runtime/database.sqlite3'));
		if ( $return ) return $sqlite;
		return $this->structure($sqlite);
	}

	public function pdo( $return = false ) {
		$sqlite = new PDOSQLite(array('path' => ROW_APP_PATH.'/runtime/database.pdosqlite'));
		if ( $return ) return $sqlite;
		return $this->structure($sqlite);
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


