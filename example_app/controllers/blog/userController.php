<?php

namespace app\controllers\blog;

use app\controllers\blogController;
use row\core\Options;
use row\auth\Session;
use app\models;
use app\forms\BlogUser;
use app\forms\RequestAccount;
use row\http\NotFoundException;

class userController extends blogController {

	// Very usefull no?
	protected function _init() {
		parent::_init();
		$this->_dispatcher->options->restful = true; // REST sucks!? Look at the annoying separation of GET and POST below
	}


	public function create() {
		$form = new BlogUser($this);

		if ( $this->POST ) {
			$valid = $form->validate($_POST);
			if ( $valid ) {
				return 'OK';
			}
		}

		return get_defined_vars();
	}


	public function POST_edit( $user = null ) {
		$user = models\User::get($user);

		$form = new BlogUser($this, array('defaults' => $user));

		$valid = $form->validate($_POST);
		if ( $valid ) {
			$user->update($form->output);

			Session::success('User saved. Probably. Didn\'t check for ->update feedback.');

			return $this->_redirect('blog-user/edit/' . $user->user_id);
		}

		$messages = Session::messages();

		return get_defined_vars();
	}

	public function GET_edit( $user = null ) {
		$user = models\User::get($user);
//		$user->password = '';

		$form = new BlogUser($this, array('defaults' => $user));

		$messages = Session::messages();

		return get_defined_vars();
	}


	public function POST_request_account() {
		$form = new RequestAccount($this);

		$valid = $form->validate($_POST);
		if ( $valid ) {
			if ( $this->_ajax() ) {
				return 'OK';
			}

			return "<h1>THIS FORM IS VALIDATED! And now what..?</h1>\n\n\n";
		}

		if ( $this->_ajax() ) {
			return 'ERROR'."\n\n* ".implode("\n* ", $form->errors());
		}

		$messages = Session::messages();

		return get_defined_vars();
	}

	// 
	public function GET_request_account() {
		$form = new RequestAccount($this);

		$messages = Session::messages();

		return get_defined_vars();
	}


	public function POST_login( $uid = null ) {
		$post = options($_POST);
		$get = options($_GET);

		try {
			// get user object
			$user = models\User::withCredentials(array(
				'username' => (string)$post->username,
				'password' => (string)$post->password,
			));

			// log user in(to SessionUser)
			$this->user->login($user);

			// debug direct logged in status
			Session::message('<pre>'.var_export($this->user->isLoggedIn(), 1).'</pre>');

			// message OK
			Session::success('Alright, alright, alright, you\'re logged in...');

			// back to blog
			return $this->_redirect($post->get('goto', $get->get('goto', 'blog')));
		}
		catch ( \Exception $ex ) {}

		// message FAIL
		Session::error('Sorry, buddy, that\'s not your username!');

		// get messages (old n new)
		$messages = Session::messages();

		// reshow login form
		return get_defined_vars();
	}

	// I'm allowing double logins (or "login layers"):
	// If you're logged in, you can't reach the form, but if you pass a
	// UID, it'll just create another layer. Our SessionUser allows that (by default).
	// Validation (e.g. a password check) could come from a Validator but might
	// be overkill in this case. Our blog doesn't need a password though =)
	public function GET_login( $uid = null ) {
		if ( null !== $uid ) {
			$this->user->login(models\User::get($uid));
		}

		if ( $this->user->isLoggedIn() ) {
			$this->_redirect('/blog');
		}

/*		if ( $this->POST ) {
			try {
				$user = models\User::one(array( 'username' => (string)$this->post->username ));
				$this->user->login($user);
				Session::success('Alright, alright, alright, you\'re logged in...');
				$this->_redirect($this->post->get('goto', '/blog'));
			}
			catch ( \Exception $ex ) {}
			Session::error('Sorry, buddy, that\'s not your username!');
		}*/

		$messages = Session::messages();

		return get_defined_vars();
	}


	// 
	public function GET_profile( $id ) {
		try {
			$user = models\User::get($id);
		}
		catch ( \Exception $ex ) {
			throw new NotFoundException('User # '.$id);
		}

		return get_defined_vars();
	}


}


