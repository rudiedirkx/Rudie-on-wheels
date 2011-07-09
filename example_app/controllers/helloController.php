<?php

namespace app\controllers;

use app\specs\Controller;

class helloController extends Controller {

	public function _pre_action() {
		echo '<pre>';
	}

	public function _post_action() {
		echo '</pre>';
	}

	public function dave( $ip, $port = '17494' ) {
		function bin201($byte) {
			$dec = ord($byte);
			$str = '';
			for ( $i=7; $i>=0; $i-- ) {
				$on = 0 < ($dec & pow(2, $i));
				$str .= (string)(int)$on;
			}
			return $str;
		}

		if ( $sock = fsockopen($ip, $port, $errno, $error, 2) ) {
			fwrite($sock, chr(91));
			$rsp = fread($sock, 2);
			echo "\n\n\n\nstatus: " . bin201($rsp)."\n\n";
 
			// all off
			fwrite($sock, chr(110));

			// random 3 on
			$on = range(1, 8);
			foreach ( array_rand($on, 3) AS $n ) {
				$n = $on[$n];
				echo "turning ".$n." on\n";

				$cmd = 100 + $n;
				fwrite($sock, chr($cmd));
			}

			fwrite($sock, chr(91));
			$rsp = fread($sock, 2);
			echo "\nstatus: " . bin201($rsp)."\n";

			fclose($sock);
		}
		else {
			echo "no connecto =(\n";
			var_dump($errno, $error);
		}
	}

	public function world() {
		echo 'Hello, world!';
	}

	public function args( $id, $oele = 'x' ) {
		print_r(func_get_args());
		print_r($_GET);
	}

}


