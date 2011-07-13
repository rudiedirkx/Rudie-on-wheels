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
	);

	protected function _pre_action() {
		echo '<pre>'."\n";
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


