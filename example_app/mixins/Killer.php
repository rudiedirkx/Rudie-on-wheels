<?php

namespace app\mixins;

use row\core\Mixin;

class Killer extends Mixin {

	public function kill() {
		exit('-- I exist to kill --');
	}

}


