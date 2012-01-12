<!doctype html>
<html>

<head>
<title><?=$this::html($title)?></title>
<style>
table { border-spacing:0; border-collapse:collapse; }
tr > * { border:solid 2px #888; padding:5px; }
</style>
</head>

<body>

<p><a href="<?=$app->_url()?>">Scaffolding Home</a></p>

<h1><?=$this::html($title)?></h1>

<?=$content?>

</body>

</html>