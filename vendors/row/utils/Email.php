<?php

namespace row\utils;

use row\utils\email\PHPMailerLite;
use row\utils\email\Html2text;
use row\utils\markdown\Parser as MarkdownParser;

class Email extends PHPMailerLite {

	static public $contexts = array();

	static public function context( $name, $options = array() ) {
		// set context
		if ( is_object($options) && is_callable($options) ) {
			static::$contexts[$name] = $options;
		}

		// execute & return context
		if ( isset(static::$contexts[$name]) ) {
			$ctx = static::$contexts[$name];
			return $ctx(get_called_class(), options($options));
		}
	}

	public function send() {
		if ( empty($this->AltBody) ) {
			$this->textify();
		}
		else if ( empty($this->Body) ) {
			$this->htmlify();
		}
		return parent::Send();
	}

	public function textify() {
		$this->AltBody = Html2text::quick($this->Body);
	}

	public function markdown() {
		$this->Body = MarkdownParser::parse($this->AltBody);
	}

	public function htmlify() {
		return $this->markdown();
	}

}


