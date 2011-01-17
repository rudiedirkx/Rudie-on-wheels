<?php

namespace app\Models;

use row\database\Model;
//use app\models\User;

class Post extends Model {

	static public $_table = 'posts';

	static public $_pk = 'post_id';

	static public $_getters = array(
		'author' => array( self::GETTER_ONE, true, 'app\models\User', 'author_id', 'user_id' ),
		'comments' => array( self::GETTER_ALL, true, 'app\models\Comment', 'post_id', 'post_id' ),
	);

	static public function _customSave( $a1 = '', $a2 = '' ) {
		return static::_update($a1, $a2);
	}

	static public function _getPublishedPost( $id ) {
		return static::_get($id, array('is_published' => true));
	}

	static public function _newest( $amount ) {
		return static::_all('1 order by created_on desc limit '.(int)$amount);
	}

	static public function _newestPublished( $amount ) {
		return static::_all('is_published = 1 order by created_on desc limit '.(int)$amount);
	}

}


