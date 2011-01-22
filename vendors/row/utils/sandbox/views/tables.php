
<? $this->title('Tables') ?>

<ul>
<?foreach( $tables AS $table ):?>
	<li><?=row\utils\Inflector::spacify($table)?> &nbsp; &nbsp; ( <a href="<?=$app->_url('table-data', $table)?>">data</a> - <a href="<?=$app->_url('table-structure', $table)?>">structure</a> )</li>
<?endforeach?>
</ul>


