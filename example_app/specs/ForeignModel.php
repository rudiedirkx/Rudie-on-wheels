<?php

namespace app\specs;

/**
 * This Model extension allows for insanely easy multi-
 * database support. By changing the location of the
 * database object, you can now store multiple db objects.
 * 
 * To use it, you'll assign the normal db object to Model
 * (like in config/database.php) and assign the other db
 * object to this Model:
 * 
	$defaultDatabase = MySQL::open(...);
	app\specs\Model::dbObject($defaultDatabase); // row\database\Model::$_db
	...
	$foreignDatabase = SQLite::open(...);
	app\specs\ForeignModel::dbObject($foreignDataase); // row\database\Model::$_db2
 * 
 * This is possible because the location of the db object
 * is variable. If you have just 1 database or don't use
 * Models for 'the other one', forget you ever saw this.
 */

class ForeignModel extends Model {

	static public $_db_key = '_db2';

}


