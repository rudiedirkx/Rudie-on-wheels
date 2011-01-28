<?php

namespace app\Models;

use app\specs\Model;
use row\utils\DateTime;

class Post extends Model {

	static public $_table = 'posts';

	static public $_pk = 'post_id';

	static public $_getters = array(
		'author' => array( self::GETTER_ONE, true, 'app\models\User', 'author_id', 'user_id' ),
		'comments' => array( self::GETTER_ALL, true, 'app\models\Comment', 'post_id', 'post_id' ),
	);

	public function _post_fill( $data ) {
		if ( isset($data['is_published']) ) {
			$this->is_published = (bool)$this->is_published; // because a Bool is prettier than a '0' or '1'
		}
		if ( isset($data['created_on']) ) {
			$this->_created_on = new DateTime($this->created_on);
		}
	}

	public function url( $more = '' ) {
		return '/blog/view/' . $this->post_id . $more;
	}

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


