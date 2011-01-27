
<!-- <pre><? print_r($validator) ?></pre> -->

<form method=post action=<?=$app->_uri?>>
<fieldset>
	<legend>Add comment</legend>

	<p class="field <?=$validator->ifError('username')?>">Your username:<br><input name=username value="<?=htmlspecialchars($app->post->username)?>"></p>

	<p class="field <?=$validator->ifError('comment')?>">Comment (uses markdown):<br><textarea name=comment><?=htmlspecialchars($app->post->comment)?></textarea></p>

	<p><input type=submit></p>

</fielset>
</form>
