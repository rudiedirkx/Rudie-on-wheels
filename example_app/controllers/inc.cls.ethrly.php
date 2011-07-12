<?php

class ETHRLY {

	public $ip;
	public $port;
	public $timeout;

	public $socket;
	public $error;
	public $errno;

	function __construct( $ip, $port = 17494, $timeout = 1 ) {
		$this->ip = $ip;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	function socket() {
		if ( null === $this->socket ) {
			$this->socket = @fsockopen($this->ip, $this->port, $this->errno, $this->error, $this->timeout);
		}

		return $this->socket;
	}

	function write( $code, $read = false ) {
		$byte = chr($code);

		$written = fwrite($this->socket(), $byte);

		return $read ? $this->read() : $written;
	}

	function read() {
		$byte = fread($this->socket(), 1);
		return $byte;
	}

	function status() {
		$byte = $this->write(91, true);

		$status = $this->bin201($byte);

		return $status;
	}

	function relay( $number, $on ) {
		$code = 100 + $number + ( $on ? 0 : 10 );

		return $this->write($code);
	}

	function on( $relays = null ) {
		if ( null === $relays ) {
			return $this->write(100);
		}

		foreach ( (array)$relays AS $n ) {
			$this->write(100+$n);
		}
	}

	function off( $relays = null ) {
		if ( null === $relays ) {
			return $this->write(110);
		}

		foreach ( (array)$relays AS $n ) {
			$this->write(110+$n);
		}
	}

	function bin201( $byte ) {
		$dec = ord($byte);

		$bin = array();
		for ( $i=7; $i>=0; $i-- ) {
			$on = 0 < ($dec & pow(2, $i));
			$bin[$i+1] = (int)$on;
		}

		ksort($bin);

		return $bin;
	}


} // END Class ETHRLY


