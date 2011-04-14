<?php

namespace row\utils;

class Image extends \row\core\Object {

	public function resize( $width = 0, $height = 0 ) {
		if ( !$width && !$height ) {
			$this->error('$width and/or $height must be given');
		}
		else if ( !$width ) {
			$width = $height / $this->height * $this->width;
		}
		else if ( !$height ) {
			$height = $width / $this->width * $this->height;
		}

		$newImage = imagecreatetruecolor($width, $height);
		imagecopyresized($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		$this->image = $newImage;

		$this->sizes();
	}

	public function output( $headers = true ) {
		if ( $headers ) {
			header('Content-type: image/png');
		}
		imagepng($this->image);
	}

	protected $error = '';
	protected $image;
	protected $width = 0;
	protected $height = 0;

	public function __construct( $file, $width = 0, $height = 0 ) {
		if ( $file ) {
			if ( is_string($file) && file_exists($file) ) {
				$is = getimagesize($file);
				if ( is_array($is) ) {
					switch ( $is['mime'] ) {
						case 'image/jepg':
						case 'image/jpg':
							$this->image = imagecreatefromjpeg($file);
						break;
						case 'image/png':
							$this->image = imagecreatefrompng($file);
						break;
						case 'image/gif':
							$this->image = imagecreatefromgif($file);
						break;
						default:
							$this->error('Invalid image type "'.$is['mime'].'"');
					}
				}
//				$this->image = 
			}
			else if ( is_resource($file) ) {
				$this->image = $file; // Hopefully it's the right kind of resource
			}
		}
		else if ( $width && $height ) {
			$this->image = imagecreatetruecolor($width, $height);
		}
		else {
			$this->error('Invalid image argument $file');
		}

		// transparency? //
		imagealphablending($this->image, true); // setting alpha blending on
		imagesavealpha($this->image, true); // save alphablending setting (important)
		// transparency //

		$this->sizes();
	}

	protected function sizes() {
		if ( $this->image && !$this->error() ) {
			$this->width = imagesx($this->image);
			$this->height = imagesy($this->image);
		}
	}

	public function error( $error = '' ) {
		if ( $error ) {
			$this->error = $error;
		}
		return $this->error;
	}

}


