<?phpecho '<table><thead><tr>';$k0 = key($data);foreach ( $data[$k0] AS $k => $v ) {	echo '<th>'.$k.'</th>';}echo '<td></td>';echo '</tr></thead><tbody>';foreach ( $data AS $row ) {	echo '<tr>';	$pkValues = array();	foreach ( $row AS $k => $v ) {		echo '<td>'.$v.'</td>';		if ( in_array($k, $pkColumns) ) {			$pkValues[] = $v;		}	}	echo '<td><a href="'.$app->url('table-data', $table.'/'.implode(',', $pkValues)).'">edit</a></td>';	echo '</tr>';}echo '</tbody></table>';