<?php
use app\specs\Output;
?>
<pre>errors:
<? print_r($validator->errors) ?></pre>

<pre>comment:
<? var_export($comment) ?></pre>

<form method=post action=<?=Output::url($app->_uri)?>>
<fieldset>
	<legend><?if(!$comment):?>Add comment<?else:?>Edit comment # <?=$comment->comment_id?><?endif?></legend>

	<?if( !$comment && !$app->user->isLoggedIn() ):?>

		<p class="field <?=$validator->ifError('username')?>">Your username:<br><input name=username value="<?=$this::html($validator->valueFor('username'))?>"><br><span class="errmsg"><?=$validator->getError('username')?></span></p>

		<p class="field <?=$validator->ifError('password')?>">Your password:<br><input name=password><br><span class="errmsg"><?=$validator->getError('password')?></span></p>

	<?endif?>

	<p class="field <?=$validator->ifError('comment')?>">Comment (uses markdown):<br><textarea rows=4 name=comment><?=$this::html($validator->valueFor('comment', $comment->comment))?></textarea><br><span class="errmsg"><?=$validator->getError('comment')?></span></p>

	<p><input type=submit></p>

</fielset>
</form>
