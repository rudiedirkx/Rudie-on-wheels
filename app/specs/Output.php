<?php

namespace app\specs;

class Output extends \row\Output {

	static public function ajaxlink( $text, $path, $options = array() ) {
		$options['onlick'] = 'return $(this).openInOverlay();';
		return static::link($text, $path, $options);
	}

}


