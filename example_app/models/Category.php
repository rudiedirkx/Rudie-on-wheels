<?php

namespace app\models;

use app\specs\Model;
use row\utils\Inflector;

class Category extends Model {

	static public $events;

	static public $_table = 'categories';
	static public $_pk = 'category_id';
	static public $_title = 'category_name';
	static public $_getters = array(
		'posts' => array( self::GETTER_ALL, true, 'app\models\Post', 'category_id', 'category_id' ),
	);

	public function url( $more = '' ) {
		return 'blog/category/' . $this->category_id . '/' . Inflector::slugify($this->category_name) . $more;
	}

	public function numPosts() {
		return Post::count(array('category_id' => $this->category_id));
	}

}


