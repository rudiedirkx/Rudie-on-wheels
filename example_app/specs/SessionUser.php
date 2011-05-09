<?php

namespace app\specs;

use row\auth\Session;
use app\models;
use \Exception;

/**
 * This class overwrites (but uses) the framework's base SessionUser,
 * because the framework doesn't know how YOU (= the app) want to log
 * in and verify sessions. It also doesn't know what the database
 * looks like and how you named your columns.
 * 
 * This class knows all that, because you made it =)
 * 
 * You can do more app-specific things here. A few standard things are
 * built into the framework: session security (with IP and UA), session
 * init, salt security (partly) and it prepared Auth login and validation.
 */

class SessionUser extends \row\auth\SessionUser {

	/**
	 * This could be anything. It defaults to "return false" because
	 * Access & Validation are different in every app.
	 *	Note: Access doesn't have to come from a database. It can come
	 * from the environment: the IP address, the request method, the
	 * given $salt (a session specific secret; automatically and always
	 * created).
	 *	Note: This function is incredibly useful to put both ACL and evironment
	 * control in, because it's so tightly linked to a controller's ACL and
	 * doesn't need a valid (authenticated) User to work. (You can ALWAYS use
	 * SessionUser, even when your app doesn't even have logins or even
	 * authenticated users.)
	 */
	public function hasAccess( $zone ) {
		switch ( $zone ) {
			case 'true':
				return true;
			case 'logged in':
				return $this->isLoggedIn();
			case 'method get':
				return 'GET' === $_SERVER['HTTP_METHOD'];
			case 'method post':
				return 'POST' === $_SERVER['HTTP_METHOD'];
			case 'check salt':
				return isset($_GET['salt']) && $this->salt === $_GET['salt'];
		}
		// For everything else, you have to be logged in (IN THIS CASE) to have any access
		return $this->isLoggedIn() and ( $this->user->hasAccess(strtolower($zone)) or $this->user->hasAccess('everything') );
//		return $this->isLoggedIn() && ( in_array(strtolower($zone), $this->user->acl) || in_array('everything', $this->user->acl) );
	}

	/**
	 * You might add a translation here... E.g. Output::translate('Anonymous')
	 */
	public function displayName() {
		return $this->isLoggedin() ? (string)$this->user->full_name : 'Anonymous';
	}

	/**
	 * If your authentication process required database records for authed
	 * users, you should DELETE that db row here.
	 */
	public function logout() {
		if ( $this->isLoggedIn() ) {
			array_pop(Session::$session['logins']);
			Session::success('Well done matey! Now you\'re gonne have to log-back-in!?');
		}
	}

	/**
	 * You can only log in a Model. Why? Consistency.
	 *	An authenticated user means a database record. That db record
	 * should have a Model. The model shouldn't have a login method
	 * because it knows nothing of the environment.
	 */
	public function login( \row\database\Model $user ) {
		// In this case I use the base login method, but you don't have to.
		// You can put whatever you want in the database and/or session...
		$login = parent::login($user);
		extract($login); // Extracts arrays $login and $insert

		// Prepare session login layer
		$login['user_id'] = $user->user_id;

		// Add login to _SESSION
		Session::$session['logins'][] = $login;

		// isLoggedIn() for this HTTP request
		$this->user = $user;

		return true; // Why would this ever be false??
	}

	/**
	 * This validation is not great, but good enough. The Session
	 * environment has already been validated, so all you need is a
	 * valid user ID.
	 */
	public function validate() {
		$login = parent::validate();
		if ( is_array($login) && isset($login['user_id'], $login['salt']) ) {
			try {
				$this->user = models\User::get($login['user_id']);
				$this->salt = $login['salt'];
			}
			catch ( Exception $ex ) {}
		}
	}

	/**
	 * Only you know where a User's ID is found, so userID() is extended
	 * from the base SessionUser class.
	 */
	public function userID() {
		return $this->isLoggedin() ? (int)$this->user->user_id : 0;
	}

}


