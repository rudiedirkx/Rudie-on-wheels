
<h1><?=$this->title('Posts')?></h1>

<?foreach( $posts as $post ):?>

	<article>
		<footer>Posted by <em><?=$post->author->full_name?></em> on <em utc="<?=$post->created_on?>"><?=$post->_created_on->format('Y-m-d H:i:s')?></em>.</footer>
		<header><h2><a href="<?=$post->url()?>"><?=$post->title?></a></h2></header>
		<?=$this->markdown($post->body)."\n"?>
		<footer><a href="<?=$post->url('#comments')?>"><?=count($post->comments)?> comments</a></footer>
	</article>

<?endforeach?>

<!-- <pre>
<? print_r($posts) ?>
</pre> -->
