
<!doctype html>
<html>

<head>
<style>
body, table {
	font-family: Arial, sans-serif;
	font-size: 100%;
}
code {
	white-space: nowrap;
	padding: 3px;
	background-color: #e4e4e4;
}
p, ul, ol, table {
	margin: 15px 0;
}
</style>
<?=$this->section('css')?>
</head>

<body>

<p><?=$this::link('Check out the example application blog!', 'blog')?></p>

<?=$content?>

<script src="<?=$this::url('js/all.js')?>"></script>
<?=$this->section('javascript')?>
</body>

</html>
