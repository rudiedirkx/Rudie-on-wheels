<?php

namespace app\models;

use app\specs\Model;
use row\auth\AbstractUser;
use row\utils\Inflector;
use \Exception;

class User extends AbstractUser implements VisitableRecord {

	static public $chain;

	static public $_table = 'users';
	static public $_pk = 'user_id';
	static public $_title = 'full_name';
	static public $_getters = array(
		'acl' => array( self::GETTER_FUNCTION, true, 'getACL' ),
		'posts' => array( self::GETTER_ALL, true, 'app\models\Post', 'user_id', 'author_id' ),
		'numPosts' => array( self::GETTER_FUNCTION, true, 'getNumPosts' ),
	);


	public $_pf_acl = false;
	protected function _post_fill() {
		if ( !is_array($this->_pf_acl) && $this->_exists('access') ) {
			$this->_pf_acl = $this->getACL();
		}
	}


	public function update( $values ) {
		if ( isset($values['password']) ) {
			$password = $values['password'];
			unset($values['password']);

			if ( '' != $password ) {
				$this->setPassword($password);
			}
		}

		return parent::update($values);
	}


	public function isFollowingPost( $post ) {
		$db = self::dbObject();
		// I'm not making a Model for just this! But it **should** be in a Model, not in a Controller!
		return 0 < $db->count('following_posts', 'user_id = ? AND post_id = ?', array($this->user_id, $post->post_id));
	}

	public function toggleFollowingPost( $post ) {
		$method = ( $this->isFollowingPost($post) ? 'stop' : 'start' ) . 'FollowingPost';
		return $this->$method($post);
	}

	public function startFollowingPost( $post ) {
		$db = self::dbObject();
		$db->insert('following_posts', array(
			'user_id' => $this->user_id,
			'post_id' => $post->post_id,
			'started_on' => gmtime(),
		));
		return true;
	}

	public function stopFollowingPost( $post ) {
		$db = self::dbObject();
		$db->delete('following_posts', array(
			'user_id' => $this->user_id,
			'post_id' => $post->post_id,
		));
		return false;
	}


	public function getNumPosts() {
		return \app\models\Post::count(array('author_id' => $this->user_id));
	}

	public function url( $more = '' ) {
		return 'blog/user/'.$this->user_id.'/'.Inflector::slugify((string)$this) . $more;
	}

	public function hasAccess( $zone ) {
		return in_array($zone, $this->acl); // $this->acl is cached because it's a ^GETTER^
	}

	public function isUnaware() {
		return 0 == rand(0, 3);
	}

	public function getACL() {
		return array_map('trim', explode(',', strtolower($this->access)));
	}

	static public function getUserFromUsername( $username ) {
		try {
			$user = self::one(array('username' => $username));
			return $user;
		}
		catch ( Exception $ex ) {}
		return false;
	}

	public function getGroupId() {
		return 1;
	}

	public function getAccessZones() {
		$iGroupId = $this->getGroupId();
		return self::dbObject()->selectFieldsNumeric('group_access ga, access_zones az', 'access_zone', 'ga.access_zone_id = az.access_zone_id AND ga.access_group_id = ?', array($iGroupId));
	}

	public function __tostring() {
		return $this->full_name;
	}

}


