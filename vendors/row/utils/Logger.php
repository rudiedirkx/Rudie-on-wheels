<?php

namespace row\utils;

use row\core\Object;

class Logger extends Object {

	const ERROR = 1;
	const WARNING = 2;
	const INFO = 4;
	const DEBUG = 8;

	const OFF = 0;
	const PRODUCTION = 3;
	const VERBOSE = 15; // ERROR + WARNING + INFO + DEBUG

	public $level = self::PRODUCTION;
	public $file = '';
	public $close = false;

	public function contains( $level ) {
		return 0 < ($this->level & $level);
	}

	public function error( $msg ) {
		if ( $this->contains($this::ERROR) ) {
			$this->write($msg);
		}
	}

	public function warning( $msg ) {
		if ( $this->contains($this::WARNING) ) {
			$this->write($msg);
		}
	}

	public function info( $msg ) {
		if ( $this->contains($this::INFO) ) {
			$this->write($msg);
		}
	}

	public function debug( $msg ) {
		if ( $this->contains($this::DEBUG) ) {
			$this->write($msg);
		}
	}

	public function write( $message ) {
		static $fp;

		if ( null === $fp && $this->file ) {
			$fp = @fopen($this->file, 'a');
		}

		if ( $fp ) {
			$message = $this->format($message);

			$rv = fwrite($fp, $message);

			if ( $this->close ) {
				fclose($fp);
				$fp = null;
			}

			return $rv;
		}

		return false;
	}

	public function format( $message ) {
		$date = date('Y-m-d H:i:s');
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

		$message = '['.$date.' '.$ip.'] ' . trim($message) . "\n";

		return $message;
	}

}


