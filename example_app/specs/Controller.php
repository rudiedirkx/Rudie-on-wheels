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

	static public $events;

	protected function _post_action() {
		// display view example
		if ( is_array($this->_response) ) {
			$this->tpl->display(
				$this->_response, // params
				!$this->_ajax() // layout
			);
		}
		parent::_post_action();
	}


	public function aclCheckAccess( $zone ) {
		return $this->user->hasAccess($zone);
	}

	protected function aclAccessFail( $zone, $action ) {
		exit('You no have the access ('.$zone.')!');
	}

}

Controller::event('construct', function( $self, $args, $chain ) {
	// Because I don't use ROW's _init, I have to do this myself:
	// Make the session user always available in every controller:
	$self->user = SessionUser::user();

	// Might come in handy sometimes: direct access to the DBAL:
	$self->db = $GLOBALS['db'];

	// Initialize Output/Views (used in 90% of controller actions):
	$self->tpl = new Output($self);
	$self->tpl->viewLayout = '_blogLayout';
	$self->tpl->assign('app', $self);

	// Prep e-mail
	Email::$_from = 'blog@blog.blog';
	Email::$_returnPath = 'bounces@blog.blog';
	Email::$_sendAsHtml = false;

	// don't continue chain, because native ROW is crap =)
});


