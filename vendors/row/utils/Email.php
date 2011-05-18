<?php

namespace row\utils;

use email\Html2text;
use email\PHPMailerLite;
use row\core\Options;

class Email extends \row\core\Object {

	static public $events;

	/* non-static */

	public $options; // typeof Options

	public $to = '';
	public $subject = '';

	public $html = '';
	public $text = '';

	public $from = '';
	public $replyTo = '';
	public $returnPath = '';

	public function __construct( $to, $subject, $body, $options = array() ) {
		$this->_fire('construct', function($self, $args, $chain) {
			extract((array)$args);

			$options = Options::make($options);
			$this->options = $options;

			$this->to = $to;
			$this->subject = $subject;

			$sendAsHtml = $options->get('sendAsHtml', static::$_sendAsHtml);
			$sendAsText = $options->get('sendAsText', static::$_sendAsText);
			if ( $sendAsHtml && $sendAsText ) {
				$this->html = $body;
				$this->text = Html2text::quick($html);
			}
			else if ( $sendAsHtml ) {
				$this->html = $body;
			}
			else if ( $sendAsText ) {
				$this->text = $body;
			}

			$this->from = (array)( $options->from ?: static::$_from );
			$this->replyTo = (array)( $options->replyTo ?: static::$_replyTo ?: $from );
			$this->returnPath = (array)( $options->returnPath ?: static::$_returnPath ?: $replyTo );
		}, compact('to', 'subject', 'body', 'options'));
	}

	public function send() {
		// Create the mail object (PHPMailer?) and send it (or return false for invalid setup)
		if ( !$this->html && !$this->text ) {
			return false;
		}
		// debug
		file_put_contents(ROW_APP_PATH.'/runtime/mail_'.time().'_to_'.$this->to.'.log', $this->text);
	}

	public function export() {
		// Return the entire e-mail incl headers and attachments etc
	}


	/* static */

	static public $_from = 'name@domain.ltd';
	static public $_replyTo = '';
	static public $_returnPath = '';

	static public $_sendAsHtml = true;
	static public $_sendAsText = true;

	/**
	 * Options:
		- from
		- replyTo
		- returnPath
		- attachments
		- sendAsHtml
		- sendAsText
		- cc
		- bcc
		- headers
	 */
	static public function make( $to, $subject, $body, $options = array() ) {
		return new static($to, $subject, $body, $options);
	}

}


