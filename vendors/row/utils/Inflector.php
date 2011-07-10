<?php

namespace row\utils;

use row\core\Object;

class Inflector extends Object {

	static public function uncamelcase( $text, $delimiter = '-' ) {
		$text = preg_replace('#([A-Z])#e', '"'.addslashes($delimiter).'".strtolower("\1")', trim($text));
		return $text;
	}

	static public function camelcase( $text ) {
		$text = preg_replace('#[\-_ ](.)#e', 'strtoupper("\1")', strtolower(trim($text)));
		return $text;
	}

	static public function slugify( $text, $replacement = '-' ) {
		$text = preg_replace('#[^a-z0-9]#i', $replacement, $text);
		$text = strtolower(trim($text, '-'));
		$text = preg_replace('#['.preg_quote($replacement).']{2,}#', $replacement, $text);
		return $text;
	}

	static public function spacify( $text ) {
		$text = preg_replace('#(\-|_)#', ' ', $text);
		$text = strtolower(trim($text));
		$text = preg_replace('#(^|\s)id(\s|$)#', '\1ID\2', $text);
		$text = ucfirst($text);
		return $text;
	}

}


