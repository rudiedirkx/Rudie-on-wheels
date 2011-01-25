
<pre><? print_r($form) ?></pre>

<form method=post action=<?=$app->_uri?>>
<fieldset>
	<legend>Add comment</legend>

	<p>Your username:<br><input name=username value="<?=htmlspecialchars($app->post->username)?>"></p>

	<p>Comment (uses markdown):<br><textarea name=comment><?=htmlspecialchars($app->post->comment)?></textarea></p>

	<p><input type=submit></p>

</fielset>
</form>
