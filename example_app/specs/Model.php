<?php

namespace app\specs;

/**
 * In case the row\database\Model misses something in
 * functionality, or it might in the future, start with an
 * extension so it's always used in your app.
 * 
 * In this example app, I miss nothing, because I made the thing =)
 */

class Model extends \row\database\Model {

	/**
	 * In case you don't like the PK return of `insert()`...
	 * Now it'll return a bool
	 *
	static public function _insert( $values ) {
		return static::dbObject()->insert(static::$_table, $values);
	} // */

}


