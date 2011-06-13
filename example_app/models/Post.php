<?php

namespace app\models;

use app\specs\Model;
use app\specs\Validator;
use row\utils\DateTime;
use app\specs\SessionUser;
use row\utils\Inflector;
use app\specs\Output;

class Post extends Model {

	static public $chain;

	static public $_table = 'posts';
	static public $_pk = 'post_id';
	static public $_title = 'title';
	static public $_getters = array(
		'author' => array( self::GETTER_ONE, true, 'app\models\User', 'author_id', 'user_id' ),
		'comments' => array( self::GETTER_ALL, true, 'app\models\Comment', 'post_id', 'post_id' ),
	);

	static public function _validator( $name ) {
		$requireds = array(
			'validator' => 'notEmpty',
			'field' => array('category_id', 'title', 'body'),
		);
		$categories = Category::all('1 ORDER BY RAND()');
		$category = array(
			'validator' => 'oneOfOptions',
			'field' => 'category_id',
			'options' => $categories,
		);
		$filling = array(
			'validator' => 'notEmpty',
			'field' => array('title', 'body'),
			'min' => 4,
		);

		switch ( $name ) {
			case 'add':
			case 'edit':
				return new Validator(array($requireds, $category, $filling), array('categories' => $categories));
		}
	}


	public function doPublish( $pub = 1 ) {
		$this->update(array('is_published' => $pub));
	}

	public function canEdit() {
		$sessionUser = SessionUser::user();
		return $sessionUser->userID() === (int)$this->author_id || $sessionUser->hasAccess('blog edit posts');
	}

	protected function _post_fill() {
		$this->is_published = (bool)$this->is_published;
	}


	public function url( $more = '' ) {
		$slug = $this->original_slug;//Inflector::slugify($this->title);
		return 'blog/view/' . $this->post_id . '/' . $slug . $more;
	}

	public function catUrl() {
		return 'blog/category/'.$this->category_id.'/'.Inflector::slugify($this->category_name);
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


	static public function _query( $conditions ) {
		return 'SELECT c.*, posts.* FROM posts, categories c WHERE c.category_id = posts.category_id AND '.$conditions;
	}

}

/**/
Post::event(array(/*'update', '_update',*/ '_insert'), function( $self, $args, $chain ) {
	if ( isset($args->values['title']) && !isset($args->values['original_slug']) ) {
		$slug = Output::slugify($args->values['title']);
//var_dump($slug);
		$slugs = Post::all("original_slug LIKE ?", $slug.'%');
		if ( $slugs ) {
			$nums = array();
			$slugs = array_map(function( $post ) use( $slug, &$nums ) {
				$nums[] = (int)substr($post->original_slug, strlen($slug)+1);
				return $post->original_slug;
			}, $slugs);
			rsort($nums, SORT_NUMERIC);
//print_r($slugs);
//print_r($nums);
			$slug .= '-'.($nums[0]+1);
//var_dump($slug);
		}
//exit;
		$args->values['original_slug'] = $slug;
	}
	return $chain($self, $args);
});

/**/
Post::event(array('update'), function( $self, $args, $chain ) {
	if ( isset($args->values['body']) ) {
		$args->values['body'] .= "\n\n<small>edited on ".gmdate('Y-m-d H:i:s')." =)</small>";
	}
	return $chain($self, $args);
});

/**/
Post::event('fill', function($self, $args, $chain) {
	if ( isset($args->data['created_on']) || !$self->_created_on ) {
		$self->_created_on = new DateTime($self->created_on);
	}
	return $chain($self, $args);
});
/**/


