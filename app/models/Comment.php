<?php

namespace app\models;

use row\database\Model;

class Comment extends Model {

	static public $_table = 'comments';

	static public $_pk = 'comment_id';

	static public $_getters = array(
		'author' => array( self::GETTER_ONE, true, 'app\models\User', 'author_id', 'user_id' ),
		'post' => array( self::GETTER_ONE, true, 'app\models\Post', 'post_id', 'post_id' ),
	);

	static public function getCommentsBetween( $a, $b ) {
		return self::all('id BETWEEN ? AND ?', array($a, $b));
	}

	static public function _form( $name = 'default' ) {
		$add = array(
			'username' => array(
				'type' => 'row\form\TextField',
				'title' => 'Username',
				'rules' => array(
					new ValidateFunction(function( &$value, $field, $form, &$context ) use ($db) {
						try {
							$context->user = User::getUserFromUsername($value);
							$value = $context->user->user_id;
							return true;
						}
						catch ( \Exception $ex ) {}
						return false;
					}, 'This username doesn\'t exist...'),
				),
				'description' => 'Please enter a simple username: alphanumeric, at least 5 characters',
			),
			'comment' => array(
				'type' => 'row\form\TextArea',
				'title' => 'Comment',
				'rules' => array(
					new ValidateNotEmpty('If you want to say nothing, don\t comment...'),
				),
				'description' => 'Your comment **will** be moderated.',
			),
		);
		$edit = $add;
		unset($edit['username']);
		if ( null === $name ) {
			return compact('add', 'edit');
		}
		$forms = compact('add', 'edit');
		if ( isset($forms[$name]) ) {
			return $forms[$name];
		}
	}

}


