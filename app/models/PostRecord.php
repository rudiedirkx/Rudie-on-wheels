<?php

namespace app\models;

use app\models\Post;
use row\utils\DateTime;
use app\models\VisitableRecord;

class PostRecord extends Post implements VisitableRecord {

	public function _post_fill( $data ) {
		if ( isset($data['is_published']) ) {
			$this->is_published = (bool)$this->is_published; // because a Bool is prettier than a '0' or '1'
		}
		if ( isset($data['created_on']) ) {
			$this->_created_on = new DateTime($this->created_on);
		}
	}

	public function url( $more = '' ) {
		return '/blog/view/' . $this->post_id . $more;
	}

}


