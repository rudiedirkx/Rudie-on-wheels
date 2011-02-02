<?php

namespace row;

use row\core\Object;
use row\utils\Options;

/**
 * Only very basic unprerequisited functionality in the base
 * Output class. Application and personal specific methods
 * can/should be added in the (always present!!) application's
 * Output extension (e.g. app\specs\Output).
 * 
 * This class is far from decent and is not done! Although
 * outside functionality/api probably won't change.
 * For one: more helpers should be added. (Remember to steal
 * some from Smarty.)
 */

class Output extends Object {

	public function __tostring() {
		return 'Output';
	}

	static public $application;

	public function __construct( $app ) {
		$this::$application = $app;
		$this->_fire('init');
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
			$folder = $this::$application->_dispatcher->_module;
			$file = $this::$application->_dispatcher->_action;
			$tpl = $folder.'/'.$file;
		}
		else if ( 2 == count($view = explode('::', $tpl)) ) {
//			print_r($view);
			$file = $view[1];
			$folder = explode('\\', $view[0]);
			$folder = $folder[count($folder)-1];
//			var_dump($folder);
			if ( $this::$application->_dispatcher->options->module_class_prefix ) {
				$mcp = $this::$application->_dispatcher->options->module_class_prefix;
				$folder = substr($folder, strlen($mcp));
			}
			if ( $this::$application->_dispatcher->options->module_class_postfix ) {
				$mcp = $this::$application->_dispatcher->options->module_class_postfix;
				$folder = substr($folder, 0, -1*strlen($mcp));
			}
//			var_dump($folder);
//			print_r($this::$application);
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



	static public function nl2br( $text ) {
		return '<p>'.nl2br((string)$text).'</p>';
	}

	static public function html( $text ) {
		return htmlspecialchars((string)$text);
	}

	static public function javascript( $text ) {
		return addslashes((string)$text);
	}

	/**
	 * This function's output depends on config and evaluations in
	 * the Application... How to get there??
	 * Temporary (?) solution: static::$application
	 */
	static public function url( $path, $absolute = false ) {
		return '/'.$path;
	}

	static public function attributes( $attr, $except = array() ) {
		$html = '';
		foreach ( $attr AS $k => $v ) {
			if ( !in_array($k, $except) ) {
				$html .= ' '.$k.'="'.htmlspecialchars($v).'"';
			}
		}
		return $html;
	}

	static public function link( $text, $path, $options = array() ) {
		return '<a href="'.static::url($path, !empty($options['absolute'])).'"'.static::attributes($options, array('absolute')).'>'.static::html($text).'</a>';
	}

	static public function select( $selectOptions, $options = array() ) {
		$options = Options::make(is_string($options) ? array('name' => $options) : $options);
		$html = '';
		if ( $options->name ) {
			$html .= '<select'.static::attributes((array)$options, array('combine', 'reverse', 'flip')).'>';
		}
		$selected = (string)$options->value ?: '';
		foreach ( (array)$selectOptions AS $k => $v ) {
			$k = is_a($v, 'row\database\Model') ? implode(',', $v->_pkValue()) : (string)$k;
			$html .= '<option'.( $selected === (string)$k ? ' selected' : '' ).' value="'.static::html($k).'">'.static::html($v).'</option>';
		}
		if ( $options->name ) {
			$html .= '</select>';
		}
		return $html;
	}

	static public function translate( $text, $replace = array(), $options = array() ) {
		$options = Options::make($options);
		if ( $replace ) {
			$text = strtr($text, (array)$replace);
		}
		if ( $options->get('ucfirst', true) ) {
			$text = ucfirst($text);
		}
		return $text;
	}

}


