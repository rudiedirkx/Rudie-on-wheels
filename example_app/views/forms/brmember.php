
<?$this->section()?>
<style>
table.br {
	border-spacing: 2px;
	border: solid 1px midnightblue;
}
table.br tr > * {
	padding: 4px;
}
table.br thead td {
	background-color: midnightblue;
	color: #fff;
	text-align: center;
}
table.br th {
	background-color: lightblue;
	text-align: right;
	padding-right: 7px;
}
table.br th:after {
	content: ':';
}
table.br tfoot td {
	text-align: center;
}
table.br span.icon {
	display: inline-block;
	width: 16px;
	height: 16px;
	text-indent: -9999px;
	overflow: hidden;
	color: transparent;
	background: none center center no-repeat;
	margin-right: 4px;
	cursor: help;
}
table.br span.icon.help {
	background-image: url(<?=$this::url('images/help.png')?>);
}
table.br td.input input {
	width: 250px;
}
table.br td.input.date input {
	width: 230px;
}
table.br input.date + img.date {
	position: relative;
	top: 2px;
	cursor: pointer;
}
</style>
<?$this->section('css')?>

<h1><?=$this->title('Test form in different format')?></h1>

<form method="post" action="" onsubmit="$.ajax(this.action, function(t){ alert(t); }, this.serialize());return false;">
<table class="br">
<thead>
	<tr>
		<td colspan="4">BR Member form</td>
	</tr>
</thead>
<tbody>
	<tr>
		<?=$form->render('username')?>
		<?=$form->render('password')?>
	</tr>
	<tr>
		<?=$form->render('firstname')?>
		<?=$form->render('middlename')?>
	</tr>
	<tr>
		<?=$form->render('lastname')?>
		<?=$form->render('email')?>
	</tr>
	<tr>
		<?=$form->render('phone1')?>
		<?=$form->render('phone2')?>
	</tr>
	<tr>
		<?=$form->render('birthdate')?>
		<?=$form->render('group')?>
	</tr>
</tbody>
<tfoot>
	<tr>
		<td colspan="4">
			<input type="submit" value="<?=$this::translate('Save')?>" />
			<input type="reset" value="<?=$this::translate('Reset')?>" />
			<input type="button" class="cancel-action" value="<?=$this::translate('Cancel')?>" />
		</td>
	</tr>
</tfoot>
</table>
</form>

<?$this->section()?>
$(function() {
	$$('input.date + img.date').each(function(el) {
		el.bind('click', function() {
			var e = this.prev(), d = prompt('Fill in a date with format YYYY-MM-DD', e.value);
			if ( d ) {
				e.value = d;
			}
		});
	});
});
<?$this->section('javascript')?>
