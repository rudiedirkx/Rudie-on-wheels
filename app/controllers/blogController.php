<?php

namespace app\controllers;

use app\controllers\ControllerParent;
use row\http\NotFoundException;
use row\database\ModelException;
use app\models\Post;

class blogController extends ControllerParent {

	static public $config = array(
		'posts_on_index' => 5,
	);

	protected function getPost( $id ) {
		try {
			$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'get' : 'getPublishedPost';
			return Post::$method($id); // does this work? Post::$method might (syntactically) just as well be a property
		}
		catch ( ModelException $ex ) {
			throw new NotFoundException('Blog post # '.$id);
		}
	}

	public function view( $id ) {
		$post = $this->getPost($id); // might throw a NotFound, which is caught outside the application
		if ( $post->author->isUnaware() ) {
			$post->is_published = true;
		}
		$post->comments;
		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function index() {
		$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'newest' : 'newestPublished';
		$posts = Post::$method(self::config('posts_on_index'));

		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

}


