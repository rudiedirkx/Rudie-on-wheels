<?php

namespace app\specs;

/**
 * It's very likely that some kind of Output function is missing
 * in the very basic row\Output. If there is: extend it and use
 * that one instead.
 * 
 * I added markdown() because my app likes Markdown and
 * ajaxLink() because my app uses a lot of ajax overlays (but not
 * really). (Obviously you need to create the javascript function
 * Element.openInAjaxOverlay yourself.)
 * 
 * The Output class isn't just used for static Output renderers.
 * It's also the view class, so you could do some configuration
 * in the ->_init() function, or change the way calls to ->display()
 * are handled. Etc etc etc.
 * 
 * Don't forget to use app\specs\Output instead of row\Output!
 */

class Output extends \row\Output {

	/**
	 * Change to your default config here:
	 *
	public function _init() {
		$this->extension = '.php';
		// Or make the Application available to all views always:
		$this->assign('application', $this::$application);
	}// */

	static public function markdown( $text ) {
		return \markdown\MarkdownParser::parse((string)$text); // 'markdown' is a Vendor. Is that necessary?
	}

	static public function ajaxlink( $text, $path, $options = array() ) {
		$options['onlick'] = 'return $(this).openInAjaxPopup();';
		return static::link($text, $path, $options);
	}

}


