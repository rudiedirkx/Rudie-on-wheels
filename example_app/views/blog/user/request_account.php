
<h1>Request blog user account</h1>

<? $errors = $form->errors() ?>
<?if($errors):?>
	<ul class="form-errors"><li class="error"><?=implode('</li><li class="error">', $errors)?></li></ul>
<?endif?>

<?=$form->render()?>
