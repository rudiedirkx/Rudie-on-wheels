
<h1>
	<?=$this->title('Posts')?>
	<?if ($canCreatePosts):?>
		<smaller>(<?=$this::link('new', 'blog/add-post')?>)</smaller>
	<?endif?>
	<smaller>(<?=$this::link('archive.csv', 'blog/csv-archive/archive.csv')?>)</smaller>
</h1>

<p>Showing <?=count($posts)?> newest (of <?=$numAllPosts?> total) posts...</p>

<?=$pager = $this::paginate($numAllPosts, $postsPerPage, 'page', array('start' => 1, 'type' => 'page', 'show' => 10, 'prevnext' => false, 'firstlast' => false))."\n"?>

<div class="articles">
	<?foreach( $posts as $post ):?>
		<article class="blogpostpreview <?if (!$post->is_published):?>unpublished<?endif?>">
			<footer>Posted by <em><?=$this::ajaxLink($post->author->full_name, $post->author->url())?></em> on <em utc="<?=$post->created_on?>"><?=$post->_created_on->format('Y-m-d H:i:s')?></em>.</footer>
			<header><h2><a href="<?=$post->url()?>"><?=$post->title?></a></h2></header>
			<content url="<?=$this::url($post->url())?>"><?=$this::markdown($post->body)."\n"?></content>
			<footer>In <?=$this::link($post->category_name, $post->catUrl())?> | <a href="<?=$post->url('#comments')?>"><?=count($post->comments)?> comments</a></footer>
		</article>
	<?endforeach?>
</div>

<?=$pager?>

<?$this->section('javascript')?>
$$('content').bind('dblclick', function(e) {
	var self = this;
	if ( self.oldInnerHTML ) {
		return;
	}
	$.ajax(self.attr('url')+'?json=1', function(t) {
		var o = JSON.parse(t);
		if ( o ) {
			self.oldInnerHTML = self.html();
			self.html('<form action="' + self.attr('url') + '" method=post onsubmit="return $.ajax(this.action, function(t){ this.element.html(t).oldInnerHTML=false; }, this.serialize(), {element: this.parentNode});"><textarea name=body></textarea><br><input type=submit> <a href="javascript:void(0);" onclick="this.parentNode.parentNode.html(function(obj){ return obj.oldInnerHTML; }).oldInnerHTML=false;">cancel</a></form>');
			self.one('textarea').value = o.body;
		}
		else {
			alert("Que?\n\n" + t);
		}
	}, '', {
		element: self
	});
});
<?$this->section('javascript')?>
