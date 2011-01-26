<?php

namespace app\controllers;

use app\controllers\ControllerParent;
use row\http\NotFoundException;
use row\database\ModelException;
use app\models;
use row\utils\Inflector;
use row\validation\Validator;
use row\auth\Session;

class blogController extends ControllerParent {

	static public $config = array(
		'posts_on_index' => 5,
	);

	public function uitloggen() {
		$this->user->logout();
		$this->redirect('/blog');
	}

	public function inloggen( $uid = null ) {
		if ( null !== $uid ) {
			$this->user->login(models\User::get($uid));
		}
		if ( $this->user->isLoggedIn() ) {
			$this->redirect('/blog');
		}
		if ( !$this->post->isEmpty() ) {
			try {
				$user = models\User::one(array( 'username' => (string)$this->post->username ));
				$this->user->login($user);
				Session::success('Ok, ok, ok, je bent ingelogd...');
				$this->redirect($this->post->get('goto', '/blog'));
			}
			catch ( \Exception $ex ) {}
			Session::error('Jonge, da is je gebruikersnaam nie!');
		}
		$app = $this;
		$messages = Session::messages();
		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function edit_comment( $comment ) {
		$comment = models\Comment::get($comment);
		if ( !$comment->canEdit() ) {
			throw new NotFoundException('Uneditable comment # '.$comment->comment_id);
		}

		/* Future validation *
		$validator = new Validator(models\Comment::form('edit'));
		var_dump($validator->validate($_POST));
		/**/

		echo 'Yup, you can edit this... But you can\'t =)';
	}

	public function add_comment( $post ) {
		$app = $this;
		$post = $this->getPost($post);

		$form = null;
		/* Future validation *
		$form = models\Comment::form('add');
		/**/

		if ( !$this->post->isEmpty() ) {
			// Submitted

//			$validator = new Validator(models\Comment::form('add'));
//			var_dump($validator->validate($_POST));
//			exit;

			$user = models\User::getUserFromUsername($this->post->username);
			if ( $user && $this->post->comment ) {
				$commentID = models\Comment::insert(array(
					'post_id' => $post->post_id,
					'author_id' => $user->user_id,
					'comment' => $this->post->comment,
					'created_on' => time(),
					'created_by_ip' => $_SERVER['REMOTE_ADDR'],
				));
				Session::success('Comment toegevoegd');
				$this->redirect($post->url('#comment-'.$commentID));
			}
			Session::error('Vul goed in jonge!');
		}

		$messages = Session::messages();

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

		$messages = Session::messages();

		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function index() {
		$app = $this;

		$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'newest' : 'newestPublished';
		$posts = models\Post::$method(self::config('posts_on_index'));

		$messages = Session::messages();

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

	protected function getPost( $post ) {
		try {
			$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'get' : 'getPublishedPost';
			return models\Post::$method($post); // does this work? Post::$method might (syntactically) just as well be a property
		}
		catch ( ModelException $ex ) {
			throw new NotFoundException('Blog post # '.$post);
		}
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


