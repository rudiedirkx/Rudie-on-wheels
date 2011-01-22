<?php

namespace row;

use row\core\Object;

class View extends Object {

	public function __tostring() {
		return 'View';
	}

	public $_application;

	public function __construct( $app ) {
		$this->_application = $app;
	}

	public $extension = '.php';
	public $viewsFolder = '';
	public $viewLayout = false;

	public $vars = array(
		'title' => '',
	);
	public $viewFile = '';

	public function assign( $key, $val = null ) {
		if ( 1 == func_num_args() ) {
			foreach ( (array)$key as $k => $v ) {
				$this->vars[$k] =& $v;
				unset($v);
			}
			return $this;
		}
		$this->vars[$key] =& $val;
		return $this;
	}

	public function display( $tpl = true, $vars = null, $layout = null ) {
		if ( is_array($vars) ) {
			$this->assign($vars);
		}

		$viewLayout = is_string($layout) || false === $layout ? $layout : $this->viewLayout;
//		if ( is_string($layout) && $layout !== $this->viewLayout ) {
//			$oldViewLayout = $this->viewLayout;
//			$this->viewLayout = $layout;
//		}

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
		$this->viewFile = $this->viewsFolder.'/'.$tpl.$this->extension;
		$this->render($viewLayout);

//		if ( isset($oldViewLayout) ) {
//			$this->viewLayout = $oldViewLayout;
//		}
	}

	public function render( $viewLayout ) {
//var_dump($this->viewFile);
		extract($this->vars);
		if ( false !== $viewLayout ) {
			ob_start();
			include($this->viewFile);
			$content = ob_get_contents();
			ob_end_clean();
//exit($content);
			$this->assign('content', $content);
			$this->viewFile = $viewLayout;
			return $this->render(false);
		}
		include($this->viewFile);
	}

	public function title( $title = null ) {
		if ( is_string($title) ) {
			$this->assign('title', $title);
		}
		return $this->vars['title'];
	}



	/**
		Maybe put stuff like this and htmlspecialchar() and such in a FrontEnd class?
	 */

	public function markdown( $text ) {
		return \markdown\MarkdownParser::parse($text); // 'markdown' is a Vendor. Is that necessary?
	}

	public function nl2br( $text ) {
		return '<p>'.nl2br($text).'</p>';
	}

}


