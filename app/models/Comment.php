<?php

namespace app\models;

use row\database\Model;
use row\form\Validator;

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


	static public function _validator( $name ) {
		$rules['add'] = array(
			array(
				'fields' => array('username', 'comment'),
				'validator' => 'notEmpty',
			),
			array(
				'fields' => 'username',
				'validator' => function( $validator, $field ) {
					// We know input[username] is not empty...
					try {
						$user = User::one(array('username' => trim($validator->input[$field])));
						$validator->output['user_id'] = $user->user_id;
						return true;
					}
					catch ( \Exception $ex ) {}
					return 'Username doesn\'t exist?';
				}
			),
		);
		$rules['edit'] = $rules['add'];
		$rules['edit'][0]['fields'] = 'comment';
		unset($rules['edit'][1]);
		if ( null === $name ) {
			return $rules;
		}
		else if ( isset($rules[$name]) ) {
			return new Validator($rules[$name], array(
				'model' => get_called_class()
			));
		}
	}


/*	static public function _form( $name = 'add' ) {

		$form = new \row\Form(array(
			'comment' => array(
				'rules' => array(
					new \row\form\validators\NotEmpty('Comment must not be empty.'),
					new \row\form\validators\MinMaxLength(array(
						'min' => 12,
						'max' => 999,
					), 'Comment must be at least 12 characters long.'),
				),
			),
			new \row\form\validators\Custom(function( $rule, $field ) {
				$form = $rule->form;
				return $_SERVER['REMOTE_ADDR'] !== '192.168.1.1';
			}, 'Wrong IP address, buddy...'),
		));

		if ( 'add' == $name ) {

			$form->field('username', array(
				'rules' => array(
					
				)
			));

			$form->validator(new \row\form\validators\Custom(function( $rule, $field ) {
				$form = $rule->form;
				
			}));

		}

		return $form;

	}*/


/*	static public function _form( $name = 'default' ) {
		$add = array(
			'username' => array(
				'type' => 'row\form\TextField',
				'title' => 'Username',
				'rules' => array(
					// this is one way:
					new validation\ValidateNotEmpty('We neeeeeed your username!'),
					// this is another way:
					array(
						'type' => 'row\validation\ValidateNotEmpty',
						'message' => 'We neeeeeed your username!'
					),
					// this is one way:
					new validation\ValidateFunction(function( &$data, $field, $form, &$context ) {
						$value = $data[$field];
						try {
							$context->user = User::getUserFromUsername($value);
							$value = $context->user->user_id;
							return true;
						}
						catch ( \Exception $ex ) {}
						return false;
					}, 'This username doesn\'t exist...'),
					// this is another:
					array(
						'type' => 'row\validation\ValidateFunction',
						'function' => function( $data, $field, $form, $context ) {
							
						},
						'message' => 'This username doesn\'t exist...'
					),
				),
				'description' => 'Please enter a simple username: alphanumeric, at least 5 characters',
			),
			'comment' => array(
				'type' => 'row\form\TextArea',
				'title' => 'Comment',
				'rules' => array(
					// this is one way:
					new validation\ValidateNotEmpty('If you want to say nothing, don\t comment...'),
					// this is another:
					array(
						'type' => 'row\validation\ValidateNotEmpty',
						'message' => 'If you want to say nothing, don\t comment...',
					),
				),
				'description' => 'Your comment **will** be moderated.',
			),
			new validation\ValidateFunction(function( &$data, $field, $form, &$context ) {
				return strlen($data['username']) < strlen($data['comment']);
			}, 'Comment must be taller than username =)'),
		);
		$edit = $add;
		unset($edit['username']);
		$forms = compact('add', 'edit');
		if ( null === $name ) {
			return $forms;
		}
		if ( isset($forms[$name]) ) {
			return $forms[$name];
		}
	}*/

}


