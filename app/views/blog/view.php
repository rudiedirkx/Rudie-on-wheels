
<p><a href="/blog">Terug naar overzicht</a></p>

<article class="blogpost <?=!$post->is_published ? 'not-published' : ''?>">

	<h1><?=$this->title($post->title)?></h1>

	<section class="article">
		<footer>Posted by <em><?=$post->author->full_name?></em> on <em utc="<?=$post->created_on?>"><?=$post->_created_on->format('Y-m-d H:i:s')?></em>.</footer>
		<?=$this->markdown($post->body)."\n"?>
	</section>
	<a id="comments"></a>
	<h2>Comments (<a href="/blog/add-comment/<?=$post->post_id?>">add</a>)</h2>
	<section class="comments">
		<?foreach( $post->comments as $n => $comment ):?>
			<article class="comment">
				<h3><a id="comment-<?=$comment->comment_id?>" href="<?=$comment->url()?>"># <?=($n+1)?></a> <em><?=$comment->author->full_name?></em> zei op <em><?=$comment->_created_on->format('Y-m-d H:i:s')?></em>:</h3>
				<?=$this->markdown($comment->comment)?>
				<?if( $comment->canEdit() ):?>
					<footer>You can <a href="/blog/edit-comment/<?=$comment->comment_id?>">edit</a> this post...</footer>
				<?endif?>
			</article>
		<?endforeach?>
	</section>

</article>

<p><a href="#">Naar boven</a></p>

<!-- <pre>
<? print_r($post) ?>
</pre> -->
