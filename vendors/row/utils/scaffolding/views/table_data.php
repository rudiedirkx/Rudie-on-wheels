
<? $this->title('Data from `'.$table.'`') ?>

<?if( !$data ):?>

<p>No data found...</p>

<ul>
	<li>See <a href="<?=$app->_url('table-structure', $table)?>">table structure here</a>.</li>
	<li><a href="<?=$app->_url('table-data', $table.'/add')?>">Add data</a></li>
</ul>

<?else:?>

<p><a href="<?=$app->_url('table-data', $table.'/add')?>">Add more data</a></p>

<table>
<thead>
	<tr>
<? $k0 = key($data) ?>
<?foreach( $data[$k0] AS $k => $v ):?>
		<th><?=row\utils\Inflector::spacify($k)?></th>
<?endforeach?>
		<td></td>
	</tr>
</thead>
<tbody>
<?foreach( $data AS $row ):?>
	<tr>
	<? $pkValues = array() ?>
	<?foreach( $row AS $k => $v ):?>
		<td><?=$v?></td>
		<?if( in_array($k, $pkColumns) ) $pkValues[] = $v;?>
	<?endforeach?>
		<td><a href="<?=$app->_url('table-data', $table.'/pk/'.implode(',', $pkValues))?>">edit</a></td>
	</tr>
<?endforeach?>
</tbody>
</table>

<?endif?>


