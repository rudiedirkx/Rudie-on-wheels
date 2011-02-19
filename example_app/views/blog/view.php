<?php

use row\utils\Inflector;

?>
<p><?=$this::link('Back to posts overview', 'blog')?></p>

<article class="blogpost <?=!$post->is_published ? 'unpublished' : ''?>">

	<h1>
		<?=$this->title($post->title)?>
		<?if(!$post->is_published):?>
			<?if($app->user->hasAccess('blog publish')):?>
				<smaller>(<?=$this::link('publish', 'blog/publish-post/'.$post->post_id)?>)</smaller>
			<?endif?>
		<?else:?>
			<?if($app->user->hasAccess('blog unpublish')):?>
				<smaller>(<?=$this::link('unpublish', 'blog/unpublish-post/'.$post->post_id)?>)</smaller>
			<?endif?>
		<?endif?>
	</h1>

	<section class="article">
		<footer>Posted by <em><?=$this::ajaxLink($post->author->full_name, $post->author->url())?></em> on <em utc="<?=$post->created_on?>"><?=$post->_created_on->format('Y-m-d H:i:s')?></em> in category <em><?=$this::link($post->category_name, $post->catUrl())?></em><?if($post->canEdit()):?> (<?=$this::link('edit', 'blog/edit-post/'.$post->post_id)?>)<?endif?>.</footer>
		<?=$this->markdown($post->body)."\n"?>
	</section>
	<a id="comments"></a>
	<h2>Comments (<?=$this::link('add', 'blog/add-comment/'.$post->post_id)?>)</h2>
	<section class="comments">
		<?foreach( $post->comments as $n => $comment ):?>
			<article class="comment">
				<h3><?=$this::link('# '.($n+1), $comment->url(), array('id' => 'comment-'.$comment->comment_id))?> <em><?=$this::ajaxLink($comment->author->full_name, $post->author->url())?></em> said on <em><?=$comment->_created_on->format('Y-m-d H:i:s')?></em>:</h3>
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
