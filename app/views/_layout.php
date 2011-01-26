<!doctype html>
<html class="html no-js ani">

<head>
<title><?=$this->title()?></title>
<style>
html, body { margin:0; padding:0; }
body { padding:0 30px; }
#messages { cursor:pointer; margin:0; padding:15px 0; width:100%; position:fixed; top:0; left:0; background-color:rgba(160, 160, 160, 0.9); font-size:18px; text-shadow:1px 1px 1px #777; }
#messages > li { padding:5px 20px; }
#messages > li.error { color:red; }
#messages > li.success { color:green; }
#login { margin-top:20px; }
h1 { margin:20px 0; }
article, fieldset { padding:30px; border:solid 1px #bbb; }
fieldset { padding:10px 20px; margin:30px 0; }
article:hover { background-color:rgba(0, 0, 0, 0.03); }
article.not-published > h1 { color:red; }
article article { margin:0; }
article h3, article h2, article h1 { margin:0; }
article.blogpostpreview { margin-bottom:30px; }
article.comment { margin-top:30px; }
article.comment:first-child { margin-top:10px; }
article.comment p:last-child { margin-bottom:0; }
</style>
</head>

<body>

<?if( $messages ):?>
	<ul id="messages">
		<?foreach( $messages AS $message ):?>
			<li class="<?=$message[1]?>"><?=$message[0]?></li>
		<?endforeach?>
	</ul>
<?endif?>

<header id="login">
	<?if( $app->user->isLoggedIn() ):?>
		Ingelogd als: <?=$app->user?> (<a href="/blog/uitloggen">uitloggen</a>)
	<?else:?>
		Je bent niet ingelogd... (<a href="/blog/inloggen">inloggen</a>)
	<?endif?>
</header>

<?=$content?>

<script>
if ( msgs = document.querySelector('#messages') ) {
	msgs.onclick = function() {
		this.parentNode.removeChild(this);
	};
}
</script>

</body>

</html>