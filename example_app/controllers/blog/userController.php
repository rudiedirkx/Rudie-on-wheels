<?php

namespace app\controllers\blog;

use app\controllers\blogController;
use row\auth\Session;
use app\models;

class userController extends blogController {

	protected function _init() {
		parent::_init();

//var_dump($this->tpl);
	}

	// 
	public function request_accountAction() {
		$form = new \app\forms\SimpleRequestAccountForm($this);
		return $this->tpl->display(__METHOD__, get_defined_vars());
	}


	// I'm allowing double logins (or "login layers"):
	// If you're logged in, you can't reach the form, but if you pass a
	// UID, it'll just create another layer. Our SessionUser allows that (by default).
	// Validation (e.g. a password check) could come from a Validator but might
	// be overkill in this case. Our blog doesn't need a password though =)
	// Note how $this->post (typeof Options) can be used to fetch _POST data.
	public function loginAction( $uid = null ) {
		if ( null !== $uid ) {
			$this->user->login(models\User::get($uid));
		}
		if ( $this->user->isLoggedIn() ) {
			$this->_redirect('/blog');
		}
		if ( !$this->post->isEmpty() ) {
			try {
				$user = models\User::one(array( 'username' => (string)$this->post->username ));
				$this->user->login($user);
				Session::success('Alright, alright, alright, you\'re logged in...');
				$this->_redirect($this->post->get('goto', '/blog'));
			}
			catch ( \Exception $ex ) {}
			Session::error('Sorry, buddy, that\'s not your username!');
		}
		$messages = Session::messages();
		return $this->tpl->display(__METHOD__, get_defined_vars());
	}


	// 
	public function profileAction( $id ) {
		try {
			$user = models\User::get($id);
		}
		catch ( \Exception $ex ) {
			throw new NotFoundException('User # '.$id);
		}

		$layout = !self::ajax();
		return $this->tpl->display(__METHOD__, get_defined_vars(), $layout); // Show only View, no Layout
	}


}


