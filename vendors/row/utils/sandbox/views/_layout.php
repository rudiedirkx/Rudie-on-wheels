<!doctype html>
<html>

<head>
<title><?=$title?></title>
<style>
table { border-spacing:0; border-collapse:collapse; }
tr > * { border:solid 2px #888; padding:5px; }
</style>
</head>

<body>

<p><a href="<?=$app->_url()?>">Sandbox Home</a></p>

<h1><?=$title?></h1>

<!-- $content -->
<?=$content?>
<!-- end $content -->

</body>

</html>