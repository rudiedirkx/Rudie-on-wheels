
<pre><? print_r($validator->errors) ?></pre>

<form method=post action=<?=$app->_uri?>>
<fieldset>
	<legend>Add comment</legend>

	<p class="field <?=$validator->ifError('username')?>">Your username:<br><input name=username value="<?=htmlspecialchars($validator->valueFor('username'))?>"></p>

	<p class="field <?=$validator->ifError('comment')?>">Comment (uses markdown):<br><textarea name=comment><?=htmlspecialchars($validator->valueFor('comment'))?></textarea></p>

	<p class="field <?=$validator->ifError('phone1')?>">Phone number 1:<br><input name=phone1 value="<?=htmlspecialchars($validator->valueFor('phone1'))?>"></p>

	<p class="field <?=$validator->ifError('phone2')?>">Another phone number?:<br><input name=phone2 value="<?=htmlspecialchars($validator->valueFor('phone2'))?>"></p>

	<p><input type=submit></p>

</fielset>
</form>
