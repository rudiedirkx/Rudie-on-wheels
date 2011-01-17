
<article>

	<h1><?=$this->title($post->title)?></h1>

	<section class="article">
		<footer>Posted by <em><?=$post->author->full_name?></em> on <em utc="<?=$post->created_on?>"><?=$post->_created_on->format('Y-m-d H:i:s')?></em>.</footer>
		<?=$this->markdown($post->body)."\n"?>
	</section>
	<section class="comments">
		<?foreach( $post->comments as $comment ):?>
			<article>
				<h3><em><?=$comment->author->full_name?></em> zei op <em><?=$comment->_created_on->format('Y-m-d H:i:s')?></em>:</h3>
				<?=$this->markdown($comment->comment)?>
			</article>
		<?endforeach?>
	</section>

</article>

<!-- <pre>
<? print_r($post) ?>
</pre> -->
