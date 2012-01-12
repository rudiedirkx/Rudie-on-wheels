
<? $this->title('Structure of `'.$table.'`') ?>

<p><a href="<?=$app->_url('table-data', $table)?>">Table data</a></p>

<?php

echo '<table><thead><tr>';
echo '<th>Name</th>';
$k0 = key($columns);
foreach ( $columns[$k0] AS $k => $v ) {
	echo '<th>' . $this::html($k) . '</th>';
}
echo '</tr></thead><tbody>';
foreach ( $columns AS $name => $row ) {
	echo '<tr>';
	echo '<td>' . $this::html(row\utils\Inflector::spacify($name)) . '</td>';
	foreach ( $row AS $k => $v ) {
		echo '<td>' . $this::html($v) . '</td>';
	}
	echo '</tr>';
}
echo '</tbody></table>';


