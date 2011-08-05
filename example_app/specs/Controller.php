<?php

namespace app\specs;

/**
 * No need to `use` classes SessionUser and Output because
 * they exist in the same namespace: app\specs.
 * 
 * Your should extend row\Controller because it contains
 * no functionality besides the very basic Controller.
 * Below are two (also very basic) examples of what you
 * could/should do in the init:
 *	- initialize SessionUser
 *	- initialize Controller (Action) ACL
 *	- initialize Views (Output)
 * What might come in handy: the database object. (The database
 * object is also available with `Model::dbObject()`.)
 */

use row\utils\Email;

abstract class Controller extends \row\Controller {

	protected function _init() {
		// I don't want to load ROW's default _init, because it does unwanted stuff, so I don't:
		// parent::_init();

		// Because I don't use ROW's _init, I have to do this myself:
		// Make the session user always available in every controller:
		$this->user = SessionUser::user();

		// Might come in handy sometimes: direct access to the DBAL:
		$this->db = $GLOBALS['db'];

		// Initialize Output/Views (used in 90% of controller actions):
		$this->tpl = new Output($this);
		$this->tpl->viewLayout = '_blogLayout';
		$this->tpl->assign('app', $this);

		// Blog `Email` context
		Email::context('blog', function( $class, $options ) {
			$mailer = new $class;
			$mailer->setFrom('blog@blog.blog', 'Tha Blog');
			$mailer->Sender = 'bounces@blog.blog';
			return $mailer;
		});
	}


	public function aclCheckAccess( $zone ) {
		return $this->user->hasAccess($zone);
	}

	protected function aclAccessFail( $zone, $action ) {
		exit('You no have the access ('.$zone.')!');
	}

}


