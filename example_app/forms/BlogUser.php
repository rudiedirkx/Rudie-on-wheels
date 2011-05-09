<?php

namespace app\forms;

use row\core\Options;
use row\utils\Email;
use app\models;
use app\specs\Output;

class BlogUser extends \app\specs\SimpleForm {

	static $_mixins = array('app\mixins\Killer');

	protected function _init() {
		parent::_init();
		$this->renderers['access'] = 'renderElementAccess';
	}

	// Element specific rendering
	protected function renderElementAccess( $name, $element, $form ) {
		$html = $form->renderTextElement($name, $element, false);
		$html = str_replace('<input ', '<input disabled ', $html);

		$html = '<div><div style="display: none;">'.$html.'</div><div><a href="javascript:void(0);" onclick="this.parentNode.style.display=\'none\';this.parentNode.previousSibling.style.display=\'block\';this.parentNode.previousSibling.getElementsByTagName(\'input\')[0].removeAttribute(\'disabled\');">Click here to edit</a></div></div>';

		return $form->renderElementWrapperWithTitle($html, $element);
	}

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
			),
			'access' => array(
				'type' => 'text',
				'validation' => 'csv',
			),
		);
	}


}


