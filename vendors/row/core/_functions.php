<?php

namespace row;

function class_exists( $class ) {
echo '['.__FUNCTION__.']';
	$args = func_get_args();
	return call_user_func_array('\class_exists', $args);
}


