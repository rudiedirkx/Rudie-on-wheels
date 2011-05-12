
<h1><?=$this->title('Request blog user account')?></h1>

<p><label><input type=checkbox id=ajax-it /> Ajaxify this form!</label></p>

<?if($errors = $form->errors()):?>
	<ul class="form-errors"><li class="error"><?=implode('</li><li class="error">', $errors)?></li></ul>
<?endif?>

<?=$form->render()?>

<?$this->section()?>
<script>
//$(function() {
	$('form').bind('submit', function(e) {
		if ( $('#ajax-it').checked ) {
			if ( e.stopPropagation ) e.stopPropagation();
			if ( e.preventDefault ) e.preventDefault();

			var data = this.serialize();
			$.ajax(this.action, function(rsp) {
				alert(rsp);
			}, data);
		}
	});
//});
</script>
<?$this->section('javascript')?>

<pre>$_POST: <? print_r($_POST) ?></pre>

<pre>$form->output: <? print_r($form->output) ?></pre>

<pre>$form->errors: <? print_r($form->errors) ?></pre>
