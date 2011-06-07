<?php

require(__DIR__.'/base.php');

// init database
require(__DIR__.'/database.php');

// global functions
require(ROW_VENDOR_ROW_PATH.'/core/_functions.php');


// cronjob logger
function logCronjobResult( $result ) {
	$cronjob = substr(basename($_SERVER['PHP_SELF']), 0, -4);
	echo "\nSaving cronjob result with type '".$cronjob."'\n";
}


