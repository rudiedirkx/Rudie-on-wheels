<?php

namespace row\core;

use Closure;

class Chain {

	public $id = 0;
	public $class = '';
	public $type = '';
	public $event = -1;
	public $first = false;
	public $reversed = false;
	public $events = array();

	public function __construct( $type, $class ) {
		$this->id = rand(0, 99999999);
		$this->type = $type;
		$this->class = $class;
//$this->verbose('new chain: '.$this, false);
	}

	public function start( $self, $args = null ) {
//$this->verbose('starting Chain]');
		$this->event = -1;
		if ( !$this->reversed ) {
			$this->events = array_reverse($this->events);
			$this->reversed = true;
		}
		return $this->next($self, $args, $this);
	}

	public function next( $self, Options $args = null ) {
//$this->verbose('Chain->next]');
//$this->verbose('event '.($this->event+1).' / '.count($this->events).']');
		$event = $this->nextEvent(); // last-in-first-out
		if ( $event ) {
//$this->verbose("executing event [".$this->event."] ".$this->class."->".$this->type." with ".count($args)." args]");
			return $event($self, $args, $this);
		}
//$this->verbose('-- You shouldn\'t be here: '.__CLASS__.' ['.__LINE__.']');
		// You should never be here... The framework event should have returned something...
	}

	public function __invoke( $self, $args = null ) {
		return $this->next($self, $args);
	}

	public function add( Closure $event, $name = '' ) {
		return array_push($this->events, array($event, $name));
	}

	public function first( Closure $event ) {
		if ( !$this->first ) {
			$this->first = true;
			array_unshift($this->events, array($event, 'native')); // at position 0
		}
		return $this;
	}


	public function nextEvent() {
		$this->event++;
//$this->verbose('Trying to get event # '.$this->event);
		if ( isset($this->events[$this->event]) ) {
//$this->verbose('Event exists. Chain continues');
			return $this->events[$this->event][0];
		}
//$this->verbose('Event not found. Chain exists??');
	}


	public function remove( $name ) {
		if ( $name ) {
			foreach ( $this->events AS $i => $event ) {
				if ( $event[1] === $name ) {
					array_splice($this->events, $i, 1);
					break;
				}
			}
		}
		return $this;
	}


	public function __toString() {
		return basename($this->class).'->'.$this->type.'{'.$this->id.'}';
	}

	public function verbose( $msg, $who = true ) {
		echo "\n".'[ '.( $who ? $this.' ' : '' ).$msg.' ]'."\n";
	}

} // END Class Chain


