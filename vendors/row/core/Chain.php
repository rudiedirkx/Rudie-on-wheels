<?php

namespace row\core;

use Closure;

class Chain {

	public $type = '';
	public $class = '';
	public $events = array();

	public function __construct( $type, $class ) {
		$this->type = $type;
		$this->class = $class;
	}

	public function start( $self, $args ) {
		return $this->next($self, $args, $this);
	}

	public function next( $self, Options $args ) {
		$event = $this->pop(); // last-in-first-out
		if ( $event ) {
			return $event($self, $args, $this);
		}
		// You should never be here... The framework event should have returned something...
	}

	public function __invoke( $self, $args ) {
		return $this->next($self, $args);
	}

	public function add( Closure $event ) {
		return $this->push($event);
	}

	public function first( Closure $event ) {
		return $this->unshift($event);
	}


	public function pop() {
		return array_pop($this->events);
	}

	public function shift() {
		return array_shift($this->events);
	}

	public function unshift( Closure $event ) {
		return array_unshift($this->events, $event);
	}

	public function push( Closure $event ) {
		return array_push($this->events, $event);
	}

} // END Class Chain


