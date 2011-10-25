
<form method="post">
<fieldset>

	<legend>Log in</legend>

	<p>Username:<br><input name="username" autofocus /></p>

	<p>Password:<br><input name="password" placeholder="boele" /></p>

	<p>You can use: <i>root</i>, <i>jaap</i>, <i>o.boele</i> or choose from <?=$this::link('all users', 'scaffolding/table-data/users')?></p>

	<p><input type=submit></p>

</fieldset>
</form>
