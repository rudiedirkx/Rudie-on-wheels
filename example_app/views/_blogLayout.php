<!doctype html>
<html class="html no-js ani">

<head>
<title><?=$this->title()?></title>
<style>
html, body { margin:0; padding:0; }
html { height: 100%; overflow-y: scroll; }
body { min-height: 100%; }
body > .body { padding: 30px; }
#messages { cursor:pointer; margin:0; padding:15px 0; width:100%; position:fixed; top:0; left:0; background-color:rgba(160, 160, 160, 0.9); font-size:18px; text-shadow:1px 1px 1px #777; }
#messages > li { padding:5px 20px; }
#messages > li.error { color:red; }
#messages > li.success { color:green; }
h1 { margin:20px 0; }
h1 smaller { font-size:16px; }
pre { margin-left:10px; padding:5px 10px; background-color:#777;  color:#fff; font-size:16px; overflow:auto; }
article, fieldset { padding:30px; border:solid 1px #bbb; }
fieldset { padding:10px 20px; margin:30px 0; }
fieldset p.field.error span.errmsg { color:red; }
fieldset p.field input, fieldset p.field textarea, fieldset p.field select { padding:1px; border:solid 1px #999; width:400px; }
fieldset p.field.error input, fieldset p.field.error textarea, fieldset p.field.error select { border-color:red; }
article { background-color:rgba(0, 0, 0, 0.04); }
article.blogpost { margin-top: 15px; padding-top: 20px; }
article.unpublished > h1, article.unpublished > header > h2 > a { color:red; }
article article { margin:0; }
article h3, article h2, article h1 { margin:0; }
article.blogpostpreview { margin-bottom:30px; }
article.comment { margin-top:30px; }
article.comment:first-child { margin-top:10px; }
article.comment p:last-child { margin-bottom:0; }
body > .overlay { position: fixed; top:0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.4); }
body > .overlay > div {
	display: inline-block;
	margin: 20px;
	padding: 20px;
	background-color: #fff;
}
body > .overlay > div > :last-child {
	margin-bottom: 0;
}
form span.input,
form span.description {
	display: block;
}
form span.description {
	font-size: 80%;
	color: #999;
	margin-left: 20px;
}
</style>
</head>

<body>
<div class="body">

<?if( $messages ):?>
	<ul id="messages">
		<?foreach( $messages AS $message ):?>
			<li class="<?=$message[1]?>"><?=$message[0]?></li>
		<?endforeach?>
	</ul>
<?endif?>

<header id="login">
	<?=$this::link($this::translate('Home'), 'blog')?> |
	<?=$this::link($this::translate('About'), 'blog/page/about')?> |
	<?=$this::link($this::translate('Help / FAQ'), 'blog/page/faq')?> |
	<?if( $app->user->isLoggedIn() ):?>
		Signed in as: <?=$this::ajaxLink($app->user, $app->user->user->url())?> (<?=$this::link($this::translate('sign out'), 'blog/logout')?>)
	<?else:?>
		You're not signed in... (<?=$this::link($this::translate('sign in', null, array('ucfirst' => false)), 'blog-user/login')?> or <?=$this::link($this::translate('request account', null, array('ucfirst' => false)), 'blog-user/request-account')?>)
	<?endif?>
</header>

<?=$content?>

<script src="<?=$this::url('js/framework.js')?>"></script>
<script>
if ( msgs = document.querySelector('#messages') ) {
	msgs.addEventListener('click', function() {
		this.parentNode.removeChild(this);
	}, false);
}
</script>

<pre>
<?print_r(\row\database\Model::dbObject()->queries)?>
</pre>

</div>
</body>

</html>