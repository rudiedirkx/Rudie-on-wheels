<?php

namespace app\controllers;

use app\specs\Controller;
use row\http\NotFoundException;
use app\models;
use row\utils\Inflector;
use row\auth\Session;

class blogController extends Controller {

	static public $config = array(
		'posts_on_index' => 5,
	);

	public function unpublish_post( $post ) {
		return $this->publish_post($post, 0);
	}

	public function publish_post( $post, $pub = null ) {
		$post = $this->getPost($post);
		if ( $this->user->hasAccess('blog publish') ) {
			$post->doPublish( is_int($pub) ? $pub : 1 );
		}
		$this->redirect($post->url());
	}

	public function category( $category ) {
		$category = models\Category::get($category);

		$messages = Session::messages();
		return $this->tpl->display(__METHOD__, get_defined_vars());
	}

	public function categories( $category = null ) {
		if ( null !== $category ) {
			return $this->category($category);
		}

		$categories = models\Category::all('1 ORDER BY category_name ASC');

		$messages = Session::messages();
		return $this->tpl->display(__METHOD__, get_defined_vars());
	}

	public function add_post() {
		$validator = models\Post::validator('edit');
		if ( !empty($_POST) ) {
			if ( $validator->validate($_POST) ) {
				$insert = $validator->output;
				$insert['author_id'] = $this->user->UserID();
				$insert['created_on'] = time();
				if ( $pid = models\Post::insert($insert) ) {
					$post = models\Post::get($pid);
					Session::success('Post Created. Look:');
					$this->redirect($post->url());
				}
				Session::error('Couldn\'t save... =( Try again!?');
			}
		}

		$post = \row\utils\Options::make(array('new' => true));
		$categories = models\Category::all();

		$messages = Session::messages();

		return $this->tpl->display(__CLASS__.'::post_form', get_defined_vars());
	}

	public function edit_post( $post ) {
		$post = $this->getPost($post);
		if ( !$post->canEdit() ) {
			throw new NotFoundException('Editable post # '.$post->post_id);
		}

		$validator = models\Post::validator('edit');
		if ( !empty($_POST) ) {
			if ( $validator->validate($_POST) ) {
				if ( $post->update($validator->output) ) {
					Session::success('Post updated =) woohooo');
					$this->redirect($post->url());
				}
				Session::error('Couldn\'t save... =( Try again!?');
			}
		}

		$categories = models\Category::all();

		$messages = Session::messages();

		return $this->tpl->display(__CLASS__.'::post_form', get_defined_vars());
	}

	public function logout() {
		$this->user->logout();
		$this->redirect('/blog');
	}

	public function login( $uid = null ) {
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
				Session::success('Alright, alright, alright, you\'re logged in...');
				$this->redirect($this->post->get('goto', '/blog'));
			}
			catch ( \Exception $ex ) {}
			Session::error('Sorry, buddy, that\'s not your username!');
		}
		$messages = Session::messages();
		return $this->tpl->display(__METHOD__, get_defined_vars());
	}

	public function edit_comment( $comment ) {
		$comment = models\Comment::get($comment);
		if ( !$comment->canEdit() ) {
			throw new NotFoundException('Editable comment # '.$comment->comment_id);
		}

		$validator = models\Comment::validator('edit');
		if ( !empty($_POST) ) {
			if ( $validator->validate($_POST) ) {
				$update = $validator->output;
				if ( $comment->update($update) ) {
					Session::success('Comment changed');
					$this->redirect($comment->url());
				}
				Session::error('Didn\'t save... Try again!?');
			}
		}

		$messages = Session::messages();

		return $this->tpl->display(__CLASS__.'::comment_form', get_defined_vars());
	}

	public function add_comment( $post ) {
		$post = $this->getPost($post);

		$anonymous = $this->user->isLoggedIn() ? '' : '_anonymous';
		$validator = models\Comment::validator('add'.$anonymous);
//echo '<pre>';
//print_r($validator); exit;
		if ( !empty($_POST) ) {
			if ( $validator->validate($_POST, $context) ) {
				$insert = $validator->output;
//print_r($insert); print_r($context); exit;
				$insert['post_id'] = $post->post_id;
				$insert['created_on'] = time();
				$insert['created_by_ip'] = $_SERVER['REMOTE_ADDR'];
//print_r($insert); exit;
				try {
					$cid = models\Comment::insert($insert);
//var_dump($cid); exit;
					$comment = models\Comment::get($cid);
//print_r($comment); exit;
					Session::success('Comment created');
					$this->redirect($comment->url());
				}
				catch ( \Exception $ex ) {
					Session::error('Didn\'t save... Try again!?');
				}
			}
		}

		$comment = \row\utils\Options::make(array('new' => true));

		$messages = Session::messages();

		return $this->tpl->display(__CLASS__.'::comment_form', get_defined_vars());
	}

	public function best( $num = 900 ) {
		exit('Showing the '.$num.' best posts...');
	}

	public function view( $post ) {

		$post = $this->getPost($post); // might throw a NotFound, which is caught outside the application

		$messages = Session::messages();

		return $this->tpl->display(__METHOD__, get_defined_vars());
	}

	public function index() {

		$unpub = $this->user->hasAccess('blog read unpublished');
		$method = $unpub ? 'newest' : 'newestPublished';
		$posts = models\Post::$method(self::config('posts_on_index'));

		$conditions = $unpub ? '' : array('is_published' => true);
		$allPosts = models\Post::count($conditions);

		$messages = Session::messages();

		return $this->tpl->display(__METHOD__, get_defined_vars());
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
			$method = $this->user->hasAccess('blog read unpublished') ? 'get' : 'getPublishedPost';
			return models\Post::$method($post); // does this work? Post::$method might (syntactically) just as well be a property
		}
		catch ( \Exception $ex ) {
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


