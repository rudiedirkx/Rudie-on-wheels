<?php

namespace app\forms;

use row\core\Options;
use row\utils\Email;
use app\models;
use app\specs\Output;

class BlogUser extends \app\specs\SimpleForm {

	static $_mixins = array(
		'app\mixins\Killer',
		'app\mixins\ReusableFormRenderers',
	);

	protected function _init() {
		parent::_init();
		$this->renderers['access'] = 'renderCSVList'; // renderCSVList is a mixin method from app\mixins\ReusableFormRenderers
	}

	protected function _post_validation() {
		$this->output['username'] = strtolower($this->output['username']);
		$this->output['full_name'] = ucfirst($this->output['full_name']);
	}

	protected function elements( $defaults ) {
		return array(
			'username' => array(
				'type' => 'text',
				'required' => true,
				'minlength' => 2,
				'validation' => 'unique',
				'unique' => array(
					'model' => 'app\\models\\User',
					'field' => 'username',
					'conditions' => array('user_id <> ?', array($defaults->user_id)),
				),
				'description' => Output::translate('Have you read our %1?', array(Output::ajaxLink(Output::translate('username guidelines', null, array('ucfirst' => false)), 'blog/page/username')))
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
			),
			'access' => array(
				'type' => 'text',
				'validation' => 'csv',
			),
		);
	}


}


