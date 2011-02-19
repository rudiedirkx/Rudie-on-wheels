<?php

namespace app\controllers;

use app\specs\Controller;
use symfony\Component\Yaml\Parser;

class doctrineController extends Controller {

	public function index() {
		$parser = new Parser;
		var_dump($parser);
//		$parser = new DoctrineYamlParser; // won't work, because class Parser isn't declared in namespace doctrine\..., but in symfony\...
//		var_dump($parser);
	}

}


