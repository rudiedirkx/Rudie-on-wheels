<?php

namespace app\specs;

class Dispatcher extends \row\http\Dispatcher {

	public $cache = false;

	public function getDefaultOptions() {
		$options = parent::getDefaultOptions();

		$options->action_name_postfix = '';

		return $options;
	}

}


