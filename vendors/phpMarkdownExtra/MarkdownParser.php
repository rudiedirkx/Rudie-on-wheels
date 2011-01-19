<?php

namespace markdown;

require(__DIR__.'/Markdown.php');

class MarkdownParser extends \MarkdownExtra_Parser {

	static public function parse( $text ) {
		static $parser;
		if (!isset($parser)) {
			$parser = new \MarkdownExtra_Parser;
		}

		# Transform text using parser.
		return $parser->transform($text);
	}

}


