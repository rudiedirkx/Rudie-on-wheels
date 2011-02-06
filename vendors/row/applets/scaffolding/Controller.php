<?php

namespace row\applets\scaffolding;

use row\database\Model;
use row\Output;

class Controller extends \row\Controller {

	static protected $_actions = array(
		'/'								=> 'tables',
		'/table-structure/*'			=> 'table_structure',
		'/table-data/*'					=> 'table_data',
		'/table-data/*/add'				=> 'add_data',
		'/table-data/*/add/save'		=> 'insert_data',
		'/table-data/*/pk/delete/CSV'	=> 'delete_record',
		'/table-data/*/pk/CSV'			=> 'table_record', // See _init for the newly created action wildcard CSV
		'/table-data/*/pk/CSV/save'		=> 'save_table_record',
	);

	protected function _init() {
		parent::_init();

		$this->_dispatcher->options->action_path_wildcards->{'CSV'} = '(\d+(?:,\d+)*)'; // Is this very nasty?

		$this->view = new Output($this);
		$this->view->viewsFolder = __DIR__.'/views';
		$this->view->viewLayout = __DIR__.'/views/_layout.php';
		$this->view->assign('app', $this);
	}

	public function delete_record( $table, $pkValues ) {
		$pkColumns = Model::dbObject()->_getPKColumns($table);
		$pkValues = explode(',', $pkValues);
		if ( count($pkColumns) !== count($pkValues) ) {
			exit('Invalid PK');
		}
		$pkValues = array_combine($pkColumns, $pkValues);

		$db = Model::dbObject();
		if ( !$db->delete($table, $pkValues) ) {
			exit($db->error());
		}

		$this->redirect($this->_url('table-data', $table));
	}

	public function save_table_record( $table, $pkValues ) {
		$pkColumns = Model::dbObject()->_getPKColumns($table);
		$pkValues = explode(',', $pkValues);
		if ( count($pkColumns) !== count($pkValues) ) {
			exit('Invalid PK');
		}
		$pkValues = array_combine($pkColumns, $pkValues);

		foreach ( $_POST['data'] AS $k => $v ) {
			if ( isset($_POST['null'][$k]) ) {
				$_POST['data'][$k] = null;
			}
		}

		$db = Model::dbObject();
		if ( !$db->update($table, $_POST['data'], $pkValues) ) {
			exit($db->error());
		}

		$this->redirect($this->_url('table-data', $table));
	}

	public function insert_data( $table ) {
		foreach ( $_POST['data'] AS $k => $v ) {
			if ( isset($_POST['null'][$k]) ) {
				$_POST['data'][$k] = null;
			}
		}

		$db = Model::dbObject();
		if ( !$db->insert($table, $_POST['data']) ) {
			exit($db->error());
		}

		$this->redirect($this->_url('table-data', $table));
	}

	public function add_data( $table ) {
		$columns = Model::dbObject()->_getTableColumns($table);
		$pkColumns = Model::dbObject()->_getPKColumns($table);
		return $this->view->display('add_data', get_defined_vars());
	}

	public function table_record( $table, $pkValues ) {
		$pkColumns = Model::dbObject()->_getPKColumns($table);
		$pkValues = explode(',', $pkValues);
		if ( count($pkColumns) !== count($pkValues) ) {
			exit('Invalid PK');
		}
		$pkValues = array_combine($pkColumns, $pkValues);
		$data = Model::dbObject()->select($table, $pkValues, array(), true);
		$columns = Model::dbObject()->_getTableColumns($table);
		return $this->view->display('table_record', get_defined_vars());
	}

	public function table_data( $table ) {
		$pkColumns = Model::dbObject()->_getPKColumns($table);
		$data = Model::dbObject()->select($table, '1');
		if ( !$data ) {
//			exit('no data');
		}
		return $this->view->display('table_data', get_defined_vars());
	}

	public function table_structure( $table ) {
		$columns = Model::dbObject()->_getTableColumns($table);
		return $this->view->display('table_structure', get_defined_vars());
	}

	public function tables() {
		$tables = Model::dbObject()->_getTables();
		return $this->view->display('tables', get_defined_vars());
	}

	public function _url( $action = '', $more = '' ) {
		$x = explode('/', ltrim($this->_dispatcher->requestPath, '/'));
		return '/'.$x[0].( $action ? '/'.$action.( $more ? '/'.$more : '' ) : '' );
	}

}


