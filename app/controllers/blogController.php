<?php

namespace app\controllers;

use app\controllers\ControllerParent;
use row\http\NotFoundException;
use row\database\ModelException;
use app\models;
use row\utils\Inflector;

class blogController extends ControllerParent {

	static public $config = array(
		'posts_on_index' => 5,
	);

	protected function getPost( $post ) {
		try {
			$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'get' : 'getPublishedPost';
			return models\Post::$method($post); // does this work? Post::$method might (syntactically) just as well be a property
		}
		catch ( ModelException $ex ) {
			throw new NotFoundException('Blog post # '.$post);
		}
	}

	public function edit_comment( $comment ) {
		$comment = models\Comment::get($comment);
		if ( !$comment->canEdit() ) {
			throw new NotFoundException('Uneditable comment # '.$comment->comment_id);
		}
		echo 'Yup, you can edit this... But you can\'t =)';
	}

	public function add_comment( $post ) {
		$app = $this;
		$post = $this->getPost($post);
		if ( !$this->post->isEmpty() ) {
			// Submitted
			$user = models\User::getUserFromUsername($this->post->username);
			if ( $user && $this->post->comment ) {
				$commentID = models\Comment::insert(array(
					'post_id' => $post->post_id,
					'author_id' => $user->user_id,
					'comment' => $this->post->comment,
					'created_on' => time(),
					'created_by_ip' => $_SERVER['REMOTE_ADDR'],
				));
				$this->redirect($post->url('#comment-'.$commentID));
			}
		}
		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function best( $num = 900 ) {
		exit('Showing the '.$num.' best posts...');
	}

	public function view( $post ) {
		$post = $this->getPost($post); // might throw a NotFound, which is caught outside the application
		if ( $post->author->isUnaware() ) {
			$post->is_published = true;
		}
		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function index() {
		$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'newest' : 'newestPublished';
		$posts = models\Post::$method(self::config('posts_on_index'));

		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	/**
	 * Model update() test
	 */
	public function comment( $id ) {
echo '<pre>'.time()."\n";
		$comment = models\Comment::get($id);
		$update = $comment->update(array('created_on' => time())); // no placeholder stuff here!
		var_dump($update);
		var_dump($this->db->affectedRows());
		print_r($comment);
	}

	public function inflector() {
		echo "<pre><u>  camelcase:</u>\n\n";
		echo $txt = 'Oele boele la la';
		echo "\n";
		var_dump(Inflector::camelcase($txt));
		echo "\n";
		echo $txt = 'verified_user_address';
		echo "\n";
		var_dump(Inflector::camelcase($txt));
		echo "\n";
		echo "<u>  slugify:</u>\n\n";
		echo $txt = 'The (new) future of the old/young/restless AND... pretty!';
		echo "\n";
		var_dump(Inflector::slugify($txt));
		echo "\n";
		echo $txt = 'verified_user_address';
		echo "\n";
		var_dump(Inflector::slugify($txt));
		echo "\n";
		echo "<u>  spacify:</u>\n\n";
		echo $txt = 'the-new-future-of-the-old-young-restless-and-pretty';
		echo "\n";
		var_dump(Inflector::spacify($txt));
		echo "\n";
		echo $txt = 'verified_user_address';
		echo "\n";
		var_dump(Inflector::spacify($txt));
		echo "\n";
		echo "<u>  uncamelcase:</u>\n\n";
		echo $txt = 'verifiedUserAddress';
		echo "\n";
		var_dump(Inflector::uncamelcase($txt));
		echo '</pre>';
	}

}


