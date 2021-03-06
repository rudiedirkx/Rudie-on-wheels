<?php

namespace row;

use row\core\Object;
use row\core\Options;
use row\core\RowException;
use row\utils\markdown\Parser as MarkdownParser;

class OutputException extends RowException {}
class OutputBufferException extends OutputException {}

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

	static public $class = __CLASS__;

	static public $application;
	public $options; // typeof Options

	public $_exceptionClass = 'row\OutputException';

	public $errorReporting = 2039; // Don't show notices
		public $oldErrorReporting = false;
	public $extension = '.php';
	public $viewsFolder = '';
		public $oldIncludePath = false;
	public $viewLayout = '_layout';

	public static $_var_content = 'content';
	public static $_var_title = 'title';
	public $vars = array();
	public $sections = array('javascript' => array(), 'css' => array());
	public $viewFile = '';

	final public function __construct( \row\Controller $application, $options = array() ) {
		$this::$application = $application;
		$this->options = Options::make($options);

		$this->_fire('init');
	}

	protected function _init() {
		// The most sensible views location
		$this->viewsFolder = ROW_APP_PATH.'/views';

		// Make sure the content and title vars exist
		$this->vars[$this::$_var_content] = $this->vars[$this::$_var_title] = '';

		$this->assign('Application', $this::$application);
		$this->assign('User', $this::$application->user);
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
		if ( $mcp = $this::$application->dispatcher->options->module_class_prefix ) {
			$folder = substr($folder, strlen($mcp));
		}

		if ( $mcp = $this::$application->dispatcher->options->module_class_postfix ) {
			$folder = substr($folder, 0, -1*strlen($mcp));
		}

		return $folder;
	}

	public function templateFileTranslation( $file ) {
		// don't transliterate controllers with action paths
		if ( !$this::$application->_getActionPaths() ) {
			if ( $anp = $this::$application->dispatcher->options->action_name_prefix ) {
				$file = substr($file, strlen($anp));
			}

			if ( $anp = $this::$application->dispatcher->options->action_name_postfix ) {
				$file = substr($file, 0, -1*strlen($anp));
			}
		}

		return $file;
	}

	public function viewFile( $tpl, &$viewLayout ) {
		if ( true === $tpl ) {
			// Use view of Controller+Action
			$controller = get_class($this::$application);
			$action = $this::$application->dispatcher->actionInfo['action'] ?: $this::$application->dispatcher->options->default_action;
			$tpl = $controller . '::' . $action;
		}
		else if ( false === $tpl ) {
			// Use no view: just the $content var
			$tpl = $viewLayout;
			$viewLayout = false;
		}

		if ( 2 == count($view = explode('::', $tpl)) ) {
			// Use given view (probably passed __METHOD__)
			$file = $view[1];
			$file = $this->templateFileTranslation($file);
			$folder = explode('\\', $view[0]);
			unset($folder[0], $folder[1]); // 0 = "app", 1 = "controllers"
			$folder = implode('/', $folder);
			$folder = $this->templateFolderTranslation($folder);
			$tpl = $folder.'/'.$file;
		}

		return $tpl;
	}

	public function viewFileExists( $file ) {
		return file_exists($file.$this->extension);
	}

	public function display( $tpl = true, $vars = null, $layout = true ) {
		if ( is_array($tpl) && !is_array($vars) ) {
			$layout = $vars;
			$vars = $tpl;
			$tpl = true;
		}

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
		if ( $title ) {
			$this->assign('title', (string)$title);
		}

		return $this->vars['title'];
	}

	public function section( $name, $return = false ) {
		static $_buffering;

		// buffering per section, so it's an array
		is_array($_buffering) or $_buffering = array();

		// make buffering status exist
		isset($_buffering[$name]) or $_buffering[$name] = false;

		// make section exist
		isset($this->sections[$name]) or $this->sections[$name] = array();

		if ( $return ) {
			if ( $_buffering[$name] ) {
				throw new OutputBufferException($name);
			}

			return isset($this->sections[$name][0]) ? implode("\n", $this->sections[$name])."\n" : '';
		}

		if ( !$_buffering[$name] ) {
			// start section
			ob_start();
			$_buffering[$name] = true;
		}

		else {
			// end section, stop buffer
			$content = ob_get_contents();
			ob_end_clean();
			$_buffering[$name] = false;

			// assign buffer contents to section
			$this->sections[$name][] = $content;
		}
	}



	static public function slugify( $text, $replacement = '-' ) {
		return \row\utils\Inflector::slugify($text, $replacement);
	}

	static public function markdown( $text ) {
		return MarkdownParser::parse((string)$text);
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

	static public function filter( $data, $keep ) {
		$keep = (array)$keep;
		$data = (array)$data;

		$output = array();
		foreach ( $keep AS $k ) {
			if ( isset($data[$k]) ) {
				$output[$k] = $data[$k];
			}
		}

		return $output;
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

	static public function asset( $path, $options = array() ) {
		$options = options( is_bool($options) ? array('absolute' => $options) : $options );

		$options->file = true;

		return static::url($path, $options);
	}

	static public function url( $path, $options = array() ) {
		$options = options( is_bool($options) ? array('absolute' => $options) : $options );

		$base = isset($GLOBALS['Dispatcher']) ? $GLOBALS['Dispatcher']->{ $options->file ? 'fileBasePath' : 'requestBasePath' } : '/';

		$prefix = '';
		if ( $options->absolute && isset($_SERVER['SERVER_PORT'], $_SERVER['HTTP_HOST']) ) {
			$scheme = 'http';
			$port = '';
			if ( 443 == $_SERVER['SERVER_PORT'] ) {
				$scheme = 'https';
			}
			else if ( 80 != $_SERVER['SERVER_PORT'] ) {
				$port = ':' . $_SERVER['SERVER_PORT'];
			}
			$prefix = $scheme . '://' . $_SERVER['HTTP_HOST'] . $port;
		}

		true !== $path or !static::$application or $path = static::$application->uri;

		$query = '';
		if ( $options->get ) {
			$query = '?' . ( is_scalar($options->get) ? $options->get : static::urlrevert(http_build_query((array)$options->get, '')) );
		}

		return $prefix . $base . $path . $query;
	}

	static public function attributes( $attr, $except = array() ) {
		$html = '';
		foreach ( $attr AS $k => $v ) {
			if ( !in_array($k, $except) ) {
				$html .= ' ' . $k . ( true === $v ? '' : '="'.static::html((string)$v).'"' );
			}
		}
		return $html;
	}

	static public function link( $text, $path, $options = array() ) {
		$options = options($options);

		$href = static::url($path, array(
			'absolute' => (bool)$options->absolute,
			'get' => $options->get,
		));
		$attributes = static::attributes($options, array('absolute', 'get'));

		return '<a href="'.$href.'"'.$attributes.'>'.static::html($text).'</a>';
	}

	static public function urlrevert( $in ) {
		$revert = array(
			'%5B' => '[',
			'%5D' => ']',
			'%2F' => '/',
			'%20' => '+',
		);

		return strtr($in, $revert);
	}

	static public function urlencode( $in ) {
		$out = urlencode($in);
		$out = static::urlrevert($out);

		return $out;
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
				$k = '%'.($k+1);
			}
			$replacements[$k] = $v;
		}
		return strtr($str, $replacements);
	}

	static public function cookie( $name, $value = null, $options = array() ) {
		if ( null === $value ) {
			return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
		}

		$base = static::$application ? static::$application->dispatcher->requestBasePath.'/' : '/';

		$domain = Options::one($options, 'domain', null);
		$path = $options->path ?: $base;
		$expires = $options->expires ?: $options->expire ?: 0;
		$secure = $options->get('secure', false);
		$httponly = $options->get('httponly', false);

		return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
	}

	static public function paginate( $total, $perPage, $name, $options = array() ) {
		$return = Options::one($options, 'return', false);
		$start = (int)(bool)$options->get('start', 1); // always 0 or 1
		$prevnext = $options->prevnext;
		$firstlast = $options->firstlast;

		$pages = ceil($total / $perPage);
		$current = isset($_GET[$name]) ? max($start, (int)$_GET[$name]) : $start;
		$end = $pages - 1 + $start;

		$g = $_GET;

		$html = '<ul class="pager">';
		if ( true === $firstlast || ( null === $firstlast && $start < $current ) ) {
			$page = $start;
			$g[$name] = $page;
			$html .= '<li class="first'.( $current == $start ? ' disabled' : '' ).'">'.static::link('first', true, array('get' => $g)).'</li>';
		}
		if ( true === $prevnext || ( null === $prevnext && $start < $current ) ) {
			$page = max($start, $current - 1);
			$g[$name] = $page;
			$html .= '<li class="prev'.( $current == $g[$name] ? ' disabled' : '' ).'">'.static::link('prev', true, array('get' => $g)).'</li>';
		}
		for ( $i=0; $i<$pages; $i++ ) {
			$page = $i + $start;
			$g[$name] = $page;
			$html .= '<li class="page page-'.$page.( $current == $page ? ' current' : '' ).( $start == $page ? ' first-page' : ( $end == $page ? ' last-page' : '' ) ).'">'.static::link($page, true, array('get' => $g)).'</li>';
		}
		if ( true === $prevnext || ( null === $prevnext && $end > $current ) ) {
			$page = min($end, $current + 1);
			$g[$name] = $page;
			$html .= '<li class="prev'.( $current == $g[$name] ? ' disabled' : '' ).'">'.static::link('next', true, array('get' => $g)).'</li>';
		}
		if ( true === $firstlast || ( null === $firstlast && $end > $current ) ) {
			$page = $end;
			$g[$name] = $page;
			$html .= '<li class="last'.( $current == $end ? ' disabled' : '' ).'">'.static::link('last', true, array('get' => $g)).'</li>';
		}
		$html .= '</ul>';

		return $html;
	}

}


