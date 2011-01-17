<?php

namespace app\controllers;

use row\Controller;
use symfony\Component\Yaml\Parser;
//use doctrine\Symfony\Component\Yaml\Parser AS DoctrineYamlParser; // won't find

class doctrineController extends Controller {

	public function index() {
		$parser = new Parser;
		var_dump($parser);
//		$parser = new DoctrineYamlParser; // won't work, because class Parser isn't declared in namespace doctrine\..., but in symfony\...
//		var_dump($parser);
	}

}


