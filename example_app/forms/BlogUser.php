<?php

namespace app\forms;

use row\core\Options;
use row\utils\Email;
use app\models;
use app\specs\Output;

class BlogUser extends \app\specs\SimpleForm {

	protected function elements( $defaults = null ) {
		is_a($defaults, 'row\\database\\Model') or $defaults = Options::make((array)$defaults);
		return array(
			'username' => array(
				'type' => 'text',
				'required' => true,
				'minlength' => 2,
				'validation' => 'unique',
				'unique' => array(
					'model' => 'app\\models\\User',
					'field' => 'username',
					'conditions' => array('is_deleted = ? AND user_id <> ?', array(false, (int)$defaults->user_id)),
				),
				'description' => Output::translate('Have you read our %0?', array(Output::ajaxLink(Output::translate('username guidelines', null, array('ucfirst' => false)), 'blog/page/username')))
			),
			'password' => array(
				'type' => 'text',
				'required' => true,
				'minlength' => 2,
			),
			'full_name' => array(
				'type' => 'text',
				'required' => true,
				'minlength' => 3,
			),
			'bio' => array(
				'type' => 'textarea',
				'required' => false,
			),
			'access' => array(
				'type' => 'text',
				'required' => false,
				'validation' => 'csv',
			),
		);
	}


}


