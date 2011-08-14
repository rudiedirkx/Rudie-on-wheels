<?php

namespace app\controllers;

use app\specs\Controller;
use app\models\User;

	class TestObject {
		function __construct() {}
	}

class dbController extends Controller {

	protected $config = array(
		'oele' => 'boele',
	);

	protected $_actions = array(
		'/' => 'index',
		'/index' => 'index',
		'/in' => 'in',
		'/replace' => 'replace',
		'/conditions' => 'conditions',
		'/build' => 'build',
	);

	protected function _pre_action() {
		echo '<pre>'."\n";
	}


	public function build() {
		include(ROW_APP_PATH.'/config/db-schema.php');

		foreach ( $schema['tables'] AS $tableName => $table ) {
			$table = options($table);
			echo "CREATE TABLE ".$tableName." (\n";

			$tableCharset = $table->get('charset', 'utf8');

			$primary = array();
			foreach ( $table->columns AS $columnName => $column ) {
				$column = options($column);
				if ( $column->primary ) {
					$primary[] = $columnName;
				}
			}
			$primaryIsAutoIncremenent = 1 == count($primary);

			// columns
			$first = true;
			foreach ( $table->columns AS $columnName => $column ) {
				$column = options($column);
				$column->type = strtolower($column->type);

				if ( 'boolean' == $column->type ) {
					$column->type = 'tinyint';
					$column->size = 1;
					if ( $column->_exists('default') ) {
						$column->default = (int)$column->default;
					}
				}

				$type = 'text' == $column->type && $column->size ? 'VARCHAR' : strtoupper($column->type);
				$size = $column->size ? "(".$column->size.")" : '';
				$notnull = !$column->get('null', true) ? ' NOT NULL' : '';
				$unsigned = $column->get('unsigned', false) ? ' UNSIGNED' : '';
				$default = false !== $column->get('default', false) ? ' DEFAULT '.( is_int($column->default) ? $column->default : "'".$column->default."'" ) : '';
				$autoincrement = $primaryIsAutoIncremenent && $column->primary && $column->get('autoincrement', true) ? ' auto_increment' : '';

				$columnCharset = $column->get('charset', $tableCharset);
				$charset = 'text' == $column->type ? ' CHARACTER SET '.$columnCharset : '';

				$comma = $first ? ' ' : ',';
				echo "  ".$comma.$columnName." ".$type.$size.$unsigned.$charset.$notnull.$default.$autoincrement."\n";

				$first = false;
			}

			if ( !$table->_exists('indexes') ) {
				$table->indexes = array();
			}

			if ( $primary ) {
				array_unshift($table->indexes, array('columns' => $primary, 'primary' => true));
			}

			// indexes
			$i = 0;
			foreach ( $table->indexes AS $index ) {
				$i++;
				$index = options($index);

				$type = $index->primary ? 'PRIMARY KEY' : ( $index->unique ? 'UNIQUE' : 'INDEX' );

				echo "  ,".$type." ( ".implode(', ', $index->columns)." )\n";
			}
			echo ");\n\n";
		}

		print_r($schema);
	}


	public function conditions() {
		$this->db->update('oele', array(
			'a' => 'a+1',
			'b = b+1',
		), array(
			'x' => 'X',
			'y > 4'
		));
	}


	public function replace() {
		$this->db->fetch('SELECT 1 FROM oele WHERE (boele IN (?) OR tra = ?) AND bla >= ?', array(array(1,2,3,'x'), 'gister', 4, 19));
	}


	protected function debugQuery() {
		static $c = -1;
		if ( $c != count($this->db->queries) ) {
			echo "\n[ sql query: \"".end($this->db->queries)."\" ]\n";
			$c = count($this->db->queries);
		}
	}


	public function in() {
		$users = User::all('? AND user_id IN (?)', array(1, array(2, 3, 4)));
		var_dump(User::dbObject()->error());
		echo "\n".end($this->db->queries)."\n";
	}


	public function index() {

		$tables = $this->db->_getTables();
		$this->debugQuery();
		var_dump($tables);
		echo "\n";

		$result = $this->db->result('SELECT user_id, username, password, full_name, bio, access FROM users ORDER BY RAND() LIMIT 2');
		$this->debugQuery();
		var_dump($result);
		echo "\n";

		$objects = $result->allObjects('app\models\User'); // post_fill will NOT be executed
		$this->debugQuery();
		var_dump($objects);
		echo "\n";

		$count = $this->db->count('users', '1 ORDER BY RAND() LIMIT 2');
		$this->debugQuery();
		var_dump($count);
		echo "\n";

/*		$count = $this->db->countRows('SHOW TABLES');
		$this->debugQuery();
		var_dump($count);
		echo "\n";*/

		$objects = $this->db->fetch('SELECT * FROM users ORDER BY RAND() LIMIT 2', 'app\models\User'); // post_fill WILL be executed
		$this->debugQuery();
		var_dump($objects);
		echo "\n";

		$posts = $this->db->selectFields('users u', 'username, (SELECT COUNT(1) FROM posts p WHERE p.author_id = u.user_id)', '1 ORDER BY RAND()');
		$this->debugQuery();
		var_dump($posts);

		$usernames = $this->db->selectFieldsNumeric('users', 'username', '1 ORDER BY RAND()');
		$this->debugQuery();
		var_dump($usernames);

		$id = $this->db->selectOne('posts', 'MAX(post_id)', '1');
		$this->debugQuery();
		var_dump($id);

		$users = $this->db->selectByField('users', 'user_id', '1 ORDER BY RAND() LIMIT 4');
		$this->debugQuery();
		var_dump($users);

	}

}


