
<h1>Request blog user account</h1>

<?if($errors = $form->errors()):?>
	<ul class="form-errors"><li class="error"><?=implode('</li><li class="error">', $errors)?></li></ul>
<?endif?>

<?=$form->render()?>

<pre>$_POST: <? print_r($_POST) ?></pre>

<pre>$form->output: <? print_r($form->output) ?></pre>
