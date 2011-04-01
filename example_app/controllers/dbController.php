<?php

namespace app\controllers;

use app\specs\Controller;

	class TestObject {
		function __construct() {}
	}

class dbController extends Controller {

	static $config = array(
		'oele' => 'boele',
	);

	static $_actions = array(
		'/' => 'index',
		'/index' => 'index',
	);

	public function _pre_action() {
		echo '<pre>'."\n";
	}

	protected function debugQuery() {
		static $c = false;
		if ( $c != count($this->db->queries) ) {
			echo "\n[ sql query: \"".end($this->db->queries)."\" ]\n";
			$c = count($this->db->queries);
		}
	}

	public function index() {

		$result = $this->db->result('SELECT user_id, username, password, full_name, bio, access FROM users ORDER BY RAND() LIMIT 2');
		$this->debugQuery();
		var_dump($result);
		echo "\n";

		$count = $result->count();
		$this->debugQuery();
		var_dump($count);
		echo "\n";

		$objects = $result->allObjects('app\models\User'); // post_fill will NOT be executed
		$this->debugQuery();
		var_dump($objects);
		echo "\n";

		$count = $this->db->count('users', '1 ORDER BY RAND() LIMIT 2');
		$this->debugQuery();
		var_dump($count);
		echo "\n";

		$count = $this->db->countRows('SHOW TABLES');
		$this->debugQuery();
		var_dump($count);
		echo "\n";

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


