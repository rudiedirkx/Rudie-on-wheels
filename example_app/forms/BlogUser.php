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

		$this->input['password'] = '';
	}

	protected function _post_validation() {
		$this->output['default']['username'] = strtolower($this->output['default']['username']);
		$this->output['default']['full_name'] = ucfirst($this->output['default']['full_name']);

		$this->output['in_domains']['domains'] = array_map('trim', explode(',', $this->output['in_domains']['domains']));
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
				'minlength' => 2,
				'description' => Output::html(Output::translate('Current password: %1', array($defaults->password))),
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
			'domains' => array(
				'type' => 'text',
				'validation' => 'csv',
				'storage' => 'in_domains',
				'description' => Output::translate('Comma (+ space) separated. Will be checked individually and created if no existo.'),
			),
		);
	}


}


