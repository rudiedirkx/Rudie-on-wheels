<?php

namespace row;

use row\core\Options;

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

class Output extends \row\Component {

	static public $_application;

	public $_exceptionClass = 'OutputException';

	public $errorReporting = 2039; // Don't show notices
		public $oldErrorReporting = false;
	public $extension = '.php';
	public $viewsFolder = '';
		public $oldIncludePath = false;
	public $viewLayout = 'layout';

	public static $_var_content = 'content';
	public static $_var_title = 'title';
	public $vars = array();
	public $sections = array('javascript' => array(), 'css' => array());
	public $viewFile = '';

	public function _init() {
		// This is the only way to make the static Output methods aware of the Application & Dispatcher?
		$this::$_application = $this->application;

		// The most sensible views location
		$this->viewsFolder = ROW_APP_PATH.'/views';

		// Make sure the content and title vars exist
		$this->vars[$this::$_var_content] = $this->vars[$this::$_var_title] = '';
	}

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

	public function templateFolderTranslation( $folder ) {
		if ( $mcp = $this::$_application->_dispatcher->options->module_class_prefix ) {
			$folder = substr($folder, strlen($mcp));
		}
		if ( $mcp = $this::$_application->_dispatcher->options->module_class_postfix ) {
			$folder = substr($folder, 0, -1*strlen($mcp));
		}
		return $folder;
	}

	public function templateFileTranslation( $file ) {
		if ( $anp = $this::$_application->_dispatcher->options->action_name_prefix ) {
			$file = substr($file, strlen($anp));
		}
		if ( $anp = $this::$_application->_dispatcher->options->action_name_postfix ) {
			$file = substr($file, 0, -1*strlen($anp));
		}
		return $file;
	}

	public function viewFile( $tpl, &$viewLayout ) {
		if ( true === $tpl ) {
			// Use view of Controller+Action
			$folder = $this::$_application->_dispatcher->_modulePath;
			$file = $this::$_application->_dispatcher->_action;
			$tpl = $folder.'/'.$file;
		}
		else if ( false === $tpl ) {
			// Use no view: just the $content var
			$tpl = $viewLayout;
			$viewLayout = false;
		}
		else if ( 2 == count($view = explode('::', $tpl)) ) {
			// Use given view (probably passed __METHOD__)
			$file = $view[1];
			$file = $this->templateFileTranslation($file);
			$folder = explode('\\', $view[0]);
			unset($folder[0], $folder[1]);
			$folder = implode('/', $folder);
			$folder = $this->templateFolderTranslation($folder);
			$tpl = $folder.'/'.$file;
		}
		else {
			// Use $tpl literally
		}
		return $tpl;
	}

	public function viewFileExists( $file ) {
		return file_exists($file.$this->extension);
	}

	public function display( $tpl = true, $vars = null, $layout = true ) {
		if ( is_array($vars) ) {
			$this->assign($vars);
		}

		$viewLayout = is_string($layout) || false === $layout ? $layout : $this->viewLayout;

		$tpl = $this->viewFile($tpl, $viewLayout);
		$this->viewFile = $this->viewsFolder.'/'.$tpl;
		if ( !$this->viewFileExists($this->viewFile) ) {
			$class = $this->_exceptionClass;
			throw new $class($tpl);
		}

		$this->render($viewLayout);
	}

	protected function render( $viewLayout ) {
		// Change include_path for as short a period as possible
		if ( false === $this->oldIncludePath ) {
			$this->oldIncludePath = set_include_path($this->viewsFolder);
		}
		// Do the same for error_reporting
		if ( false === $this->oldErrorReporting ) {
			$this->oldErrorReporting = error_reporting($this->errorReporting);
		}
		// Unpack template variables
		unset($this->vars['this']);
		extract($this->vars);
		// Render template AND layout
		if ( false !== $viewLayout ) {
			ob_start();
			include($this->viewFile.$this->extension);
			$content = ob_get_contents();
			ob_end_clean();
			$this->assign('content', $content);
			$this->viewFile = $viewLayout;
			return $this->render(false);
		}
		// Render template
		include($this->viewFile.$this->extension);
		// Quickly change the include_path & error_reporting back!
		set_include_path($this->oldIncludePath);
		error_reporting($this->oldErrorReporting);
		$this->oldIncludePath = $this->oldErrorReporting = false;
	}

	public function title( $title = null ) {
		if ( is_string($title) ) {
			$this->assign('title', $title);
		}
		return $this->vars['title'];
	}

	public function section( $name = null, $addition = null ) {
		static $buffering;

		if ( $name && !isset($this->sections[$name]) ) {
			// make section exist
			$this->sections[$name] = array();
		}

		if ( $buffering ) {
			// stop running buffer
			$content = ob_get_contents();
			ob_end_clean();
			if ( $name ) {
				// assign buffer contents to section
				$this->sections[$name][] = $content;
			}
			$buffering = false;
			return;
		}

		if ( null === $name ) {
			// start section
			ob_start();
			$buffering = true;
			return;
		}

		if ( $name ) {
			return implode("\n", $this->sections[$name])."\n";
		}
		return '';
	}



	static public function slugify( $text, $replacement = '-' ) {
		return \row\utils\Inflector::slugify($text, $replacement);
	}

	static public function markdown( $text ) {
		return \markdown\Parser::parse((string)$text);
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

	static public function csv( $data, $forceScalar = true ) {
		if ( is_scalar($data) || $forceScalar ) {
			!is_bool($data) or $data = (int)$data;
			$data = (string)$data;
//var_dump($data);
			return str_replace('"', '""', $data);
		}
		return '"'.implode('","', array_map(__METHOD__, (array)$data)).'"'."\r\n";
	}

	/**
	 * This function's output depends on config and evaluations in
	 * the Application... How to get there??
	 * Temporary (?) solution: static::$application
	 */
	static public function url( $path, $absolute = false ) {
		$base = static::$_application ? static::$_application->_dispatcher->requestBasePath.'/' : '/';
		return $base.$path;
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
			$text = static::replace($text, $replace);
		}
		if ( $options->get('ucfirst', true) ) {
			$text = ucfirst($text);
		}
		return $text;
	}

	static public function replace( $str, $replace ) {
		$replacements = array();
		foreach ( (array)$replace AS $k => $v ) {
			if ( is_int($k) ) {
				$k = '%'.$k;
			}
			$replacements[$k] = $v;
		}
		return strtr($str, $replacements);
	}

}


