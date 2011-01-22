<?php

namespace app\controllers;

use app\controllers\ControllerParent;
use row\http\NotFoundException;
use row\database\ModelException;
use app\models;
use row\utils\Inflector;

class blogController extends ControllerParent {

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

	static public $config = array(
		'posts_on_index' => 5,
	);

	protected function getPost( $id ) {
		try {
			$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'get' : 'getPublishedPost';
			return models\Post::$method($id); // does this work? Post::$method might (syntactically) just as well be a property
		}
		catch ( ModelException $ex ) {
			throw new NotFoundException('Blog post # '.$id);
		}
	}

	public function best( $num = 900 ) {
		exit('Showing the '.$num.' best posts...');
	}

	public function view( $id ) {
		$post = $this->getPost($id); // might throw a NotFound, which is caught outside the application
		if ( $post->author->isUnaware() ) {
			$post->is_published = true;
		}
//		$post->comments;
		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function index() {
		$method = $this->user->hasAccess('BLOG__VIEW_UNPUBLISHED') ? 'newest' : 'newestPublished';
		$posts = models\Post::$method(self::config('posts_on_index'));

		return $this->tpl->assign(get_defined_vars())->display(__METHOD__);
	}

	public function comment( $id ) {
echo '<pre>'.time()."\n";
		$comment = models\Comment::get($id);
		$update = $comment->update(array('created_on' => time())); // no placeholder stuff here!
		var_dump($update);
		var_dump($this->db->affectedRows());
		print_r($comment);
	}

}


