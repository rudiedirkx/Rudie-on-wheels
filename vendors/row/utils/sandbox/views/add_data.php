<?php

use row\utils\Inflector;

?>

<? $this->title('New record to populate `'.$table.'`') ?>

<form method="post" action="<?=$app->_uri?>/save">
<table>
	<tr>
		<th></th>
		<th></th>
		<th>NULL</th>
	</tr>
<?foreach( $columns AS $name => $details ):?>
	<tr>
		<th><?=Inflector::spacify($name)?></th>
		<td><textarea name="data[<?=$name?>]"><?=htmlspecialchars((string)$columns[$name]['default'])?></textarea></td>
		<td align="center"><input type="checkbox" name="null[<?=$name?>]"<?if( !$columns[$name]['null'] ):?> disabled<?elseif( null === $columns[$name]['default'] ):?> checked<?endif?> /></td>
	</tr>
<?endforeach?>
	<tr>
		<td colspan="3" align="center">
			<input type=submit />
		</td>
	</tr>
</table>
</form>

