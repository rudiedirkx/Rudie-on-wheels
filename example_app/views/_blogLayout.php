<!doctype html>
<html class="html no-js ani">

<head>
<title><?=$this->title()?></title>
<link rel=stylesheet href="<?=$this::url('css/all.css')?>" />
<script src="<?=$this::url('js/all.js')?>"></script>
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