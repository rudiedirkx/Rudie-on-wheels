<?php

namespace app\models;

use app\specs\Model;
use app\specs\Validator;
use app\specs\SessionUser;
use row\utils\DateTime;

class Comment extends Model {

	static public $_table = 'comments';

	static public $_pk = 'comment_id';

	static public $_getters = array(
		'author' => array( self::GETTER_ONE, true, 'app\models\User', 'author_id', 'user_id' ),
		'post' => array( self::GETTER_ONE, true, 'app\models\Post', 'post_id', 'post_id' ),
	);

	public function _post_fill( $data ) {
		if ( isset($data['created_on']) ) {
			$this->_created_on = new DateTime($this->created_on);
		}
	}

	public function canEdit() {
		$sessionUser = SessionUser::user();
		$owner = $_SERVER['REMOTE_ADDR'] === $this->created_by_ip;
		$owner = $sessionUser->userID() === (int)$this->author_id;
		$inTime = 300 > time() - $this->created_on;
		// How do I reach the session user from here? It's only registered in the $application
		return ( $owner && $inTime ) || $sessionUser->hasAccess('always edit comments');
	}

	public function url( $more = '' ) {
		return 'blog/view/' . $this->post_id . '#comment-' . $this->comment_id;
	}

	static public function getCommentsBetween( $a, $b ) {
		return self::all('id BETWEEN ? AND ?', array($a, $b));
	}

	static public function _validator( $name ) {
		$rules['add'] = array(
			'requireds' => array(
				'field' => 'comment',
				'validator' => 'notEmpty',
				'min' => 12,
				'message' => 'We need at least 12 characters from you, buddy!',
			),
		);

		$rules['add_anonymous'] = $rules['add'];
		$rules['add_anonymous']['requireds']['field'] = array('username', 'password', 'comment');
		$rules['add_anonymous']['login'] = array(
			'validator' => function( $validator ) {
				try {
					$user = User::one(array(
						'username' => trim($validator->input['username']),
						'password' => $validator->input['password'],
					));
					$validator->context['user'] = $user;
					$validator->output['author_id'] = $user->user_id;
//					SessionUser::user()->login($user);
					return true;
				}
				catch ( \Exception $ex ) {}
				$validator->setError(array('username', 'password'), 'I don\'t know that username/password combination...');
			}
		);
		$rules['add_anonymous']['removes'] = array(
			'validator' => 'remove',
			'field' => array('username', 'password'),
		);

		$rules['add']['user'] = array(
			'validator' => function( $validator ) {
				$validator->output['author_id'] = SessionUser::user()->userID();
			}
		);

		$rules['edit'] =& $rules['add'];
		$rules['edit_anonymous'] =& $rules['add_anonymous'];

		if ( null === $name ) {
			return $rules;
		}
		else if ( isset($rules[$name]) ) {
			return new Validator($rules[$name], array(
				'model' => get_called_class()
			));
		}
	}

}


