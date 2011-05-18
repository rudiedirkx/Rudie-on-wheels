<?php

namespace row\core;

use Closure;

class Chain {

	public $class = '';
	public $type = '';
	public $event = -1;
	public $first = false;
	public $events = array();

	public function __construct( $type, $class ) {
		$this->type = $type;
		$this->class = $class;
	}

	public function start( $self, $args = null ) {
		return $this->next($self, $args, $this);
	}

	public function next( $self, Options $args = null ) {
		$event = $this->pop(); // last-in-first-out
		if ( $event ) {
			return $event($self, $args, $this);
		}
		// You should never be here... The framework event should have returned something...
	}

	public function __invoke( $self, $args = null ) {
		return $this->start($self, $args);
	}

	public function add( Closure $event ) {
		return $this->push($event);
	}

	public function first( Closure $event ) {
		if ( !$this->first ) {
			$this->first = true;
			return $this->unshift($event);
		}
	}


	public function pop() {
		if ( isset($this->events[++$this->event]) ) {
			return $this->events[$this->event];
		}
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


