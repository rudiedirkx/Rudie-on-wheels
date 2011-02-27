<?php

namespace app\specs;

class Error extends \row\Controller {

	public function index( $ex ) {
		switch ( get_class($ex) ) {
			case 'row\http\NotFoundException':
				exit('[404] Not Found: '.$ex->getMessage());
			case 'row\database\DatabaseException':
				exit('[Model (config?)] '.$ex->getMessage().'');
			case 'row\database\ModelException':
				exit('[Model (config?)] '.$ex->getMessage().'');
		}
		exit('Unknown error encountered: '.$ex->getMessage().'');
	}

}


