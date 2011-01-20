<?php

namespace row\utils\sandbox;

use row\Controller;
use row\database\Model;
use row\View;

class sandboxController extends Controller {

	protected function _init() {
		$this->view = new View($this);
		$this->view->viewsFolder = __DIR__.'/views';
		$this->view->viewLayout = __DIR__.'/views/_layout.php';
	}

	public function table_data( $table = null, $pkValues = null ) {
		if ( !$table ) {
			return $this->index();
		}
		$app = $this;
		$pkColumns = Model::dbObject()->_getPKColumns($table);
		if ( $pkValues ) {
			$pkValues = explode(',', $pkValues);
			if ( count($pkColumns) !== count($pkValues) ) {
				exit('Invalid PK');
			}
			$pkValues = array_combine($pkColumns, $pkValues);
			$data = Model::dbObject()->select($table, $pkValues, array(), true);
			return $this->view->display('table_record', get_defined_vars());
			exit;
		}
		$data = Model::dbObject()->select($table, '1');
		if ( !$data ) {
			exit('no data');
		}
		return $this->view->display('table_data', get_defined_vars());
	}

	public function table_structure( $table = null ) {
		if ( !$table ) {
			return $this->index();
		}
		$columns = Model::dbObject()->_getTableColumns($table);
		$app = $this;
		return $this->view->display('table_structure', get_defined_vars());
	}

	public function index() {
		$app = $this;
		$tables = Model::dbObject()->_getTables();
		return $this->view->display('tables', get_defined_vars());
	}

	private function printData( $data ) {
		echo '<table><thead><tr>';
//		echo '<td></td>';
		$k0 = key($data);
		foreach ( $data[$k0] AS $k => $v ) {
			echo '<th>'.$k.'</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ( $data AS $row ) {
			echo '<tr>';
//			echo '<td><a href="'.$this->url('table-data', 'table/0,0').'"></a></td>';
			foreach ( $row AS $v ) {
				echo '<td>'.$v.'</td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	public function url( $action, $more = '' ) {
		return '/'.$this->_dispatcher->_module.'/'.$action.( $more ? '/'.$more : '' );
	}

}


