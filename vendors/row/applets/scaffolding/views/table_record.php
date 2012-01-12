
<? $this->title('`'.$table.'` record # '.implode(',', $pkValues)) ?>

<form method="post" action="<?=$app->_url('table-data', $table.'/pk/'.implode(',', $pkValues).'/save')?>">
<table>
	<tr>
		<th></th>
		<th></th>
		<th>NULL</th>
	</tr>
<?php

foreach ( $data AS $k => $v ) {
	echo '<tr>';
	echo '<th>'.$k.'</th>';
	if ( in_array($k, $pkColumns) ) {
		echo '<td colspan="2">'.$this::html($v).'</td>';
	}
	else {
		echo '<td><textarea name="data['.$k.']">'.$this::html($v).'</textarea></td>';
		echo '<td align="center"><input type="checkbox" name="null['.$k.']"'.( null === $v ? ' checked' : '' ).( !$columns[$k]['null'] ? ' disabled' : '' ).' /></td>';
	}
	echo '</tr>';
}

?>
	<tr>
		<td colspan="3" align="center">
			<input type=submit />
		</td>
	</tr>
</table>
</form>


