<?php

namespace row;

use row\core\Object;

class View extends Object {

	public $_application;

	public function __construct( $app ) {
		$this->_application = $app;
	}

	public $extension = '.php';
	public $viewsfolder = '';

	public $vars = array(
		'title' => '',
	);
	public $viewfile;

	public function assign( $key, $val = null ) {
		if ( 1 == func_num_args() ) {
			foreach ( (array)$key as $k => $v ) {
				$this->vars[$k] =& $v;
			}
			return $this;
		}
		$this->vars[$key] =& $val;
		return $this;
	}

	public function display( $tpl = true ) {
//		var_dump($tpl);
		if ( true === $tpl ) {
			$folder = $this->_application->_dispatcher->_module;
			$file = $this->_application->_dispatcher->_action;
			$tpl = $folder.'/'.$file;
		}
		else if ( 2 == count($view = explode('::', $tpl)) ) {
//			print_r($view);
			$file = $view[1];
			$folder = explode('\\', $view[0]);
			$folder = $folder[count($folder)-1];
//			var_dump($folder);
			if ( $this->_application->_dispatcher->options->module_class_prefix ) {
				$mcp = $this->_application->_dispatcher->options->module_class_prefix;
				$folder = substr($folder, strlen($mcp));
			}
			if ( $this->_application->_dispatcher->options->module_class_postfix ) {
				$mcp = $this->_application->_dispatcher->options->module_class_postfix;
				$folder = substr($folder, 0, -1*strlen($mcp));
			}
//			var_dump($folder);
//			print_r($this->_application);
			$tpl = $folder.'/'.$file;
		}
		$this->viewfile = $tpl;
		$this->render();
	}

	public function render() {
		extract($this->vars);
		include($this->viewsfolder.'/'.$this->viewfile.$this->extension);
		
	}

	public function title( $title = null ) {
		if ( is_string($title) ) {
			$this->assign('title', $title);
		}
		return $this->vars['title'];
	}

	public function markdown( $text ) {
		require_once(ROW_VENDORS_PATH.'/phpMarkdownExtra/Markdown.php');
		return Markdown($text);
//		return $this->nl2br($text);
	}

	public function nl2br( $text ) {
		return '<p>'.nl2br($text).'</p>';
	}

}


