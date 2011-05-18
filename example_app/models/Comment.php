<?php

namespace app\models;

use app\specs\Model;
use app\specs\Validator;
use app\specs\SessionUser;
use row\utils\DateTime;
use \Exception;

class Comment extends Model {

	static public $_table = 'comments';

	static public $_pk = 'comment_id';

	static public $_getters = array(
		'author' => array( self::GETTER_ONE, true, 'app\models\User', 'author_id', 'user_id' ),
		'post' => array( self::GETTER_ONE, true, 'app\models\Post', 'post_id', 'post_id' ),
	);

/*	protected function _post_fill( $data ) {
		if ( isset($data['created_on']) ) {
			$this->_created_on = new DateTime($this->created_on);
		}
	}*/

	public function canEdit() {
		$sessionUser = SessionUser::user();

		$owner = $_SERVER['REMOTE_ADDR'] === $this->created_by_ip;
		$owner = $sessionUser->userID() === (int)$this->author_id;

		$inTime = 600 > time() - $this->created_on; // 10 minutes

		return ( $owner && $inTime ) || $sessionUser->hasAccess('always edit comments');
	}

	public function url( $more = '' ) {
		return $this->post->url('#comment-' . $this->comment_id);
	}

	static public function getCommentsBetween( $a, $b ) {
		return self::all('id BETWEEN ? AND ?', array($a, $b));
	}

	static public function _validator( $name ) {
		$comment = array(
			'field' => 'comment',
			'validator' => 'notEmpty',
			'min' => 12,
			'message' => 'We need at least 12 characters from you, buddy!',
		);
		$requireds = array(
			'field' => array('username', 'password'),
			'validator' => 'notEmpty',
		);
		$login = array(
			'validator' => function( $validator ) {
				try {
					$user = User::one(array(
						'username' => trim($validator->input['username']),
						'password' => $validator->input['password'],
					));
					$validator->context['user'] = $user;
					$validator->output['author_id'] = $user->user_id;
//					SessionUser::user()->login($user); // Auto-login?
					return true;
				}
				catch ( Exception $ex ) {}
				$validator->setError(array('username', 'password'), 'I don\'t know that username/password combination...');
			}
		);
		$removes = array(
			'validator' => 'remove',
			'field' => array('username', 'password'),
		);
		$setUser = array(
			'validator' => function( $validator ) {
				$validator->output['author_id'] = SessionUser::user()->userID();
			}
		);

		switch ( $name ) {
			case 'add':
				return new Validator(array($comment, $setUser), array('model' => get_called_class()));
			case 'add_anonymous':
				return new Validator(array($requireds, $comment, $login, $removes), array('model' => get_called_class()));
			case 'edit':
				return new Validator(array($comment), array('model' => get_called_class()));
		}
	}

}

Comment::event('insert', function( $self, $args, $chain ) {
	$args->values['created_by_ip'] = 'WOOHOO';
	return $chain($self, $args);
});

Comment::event('fill', function( $self, $args, $chain ) {
	if ( isset($args->data['created_on']) || !$self->_created_on ) {
		$self->_created_on = new DateTime($self->created_on);
	}
	return $chain($self, $args);
});


