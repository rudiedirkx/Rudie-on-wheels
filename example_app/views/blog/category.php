
<p><?=$this::link('All categories', 'blog/categories')?></p>

<h1><?=$category->category_name?></h1>

<p>Posts in this category:</p>

<ul>
	<?foreach($category->posts AS $post):?>
		<li><?=$this::link($post->title, $post->url())?></li>
	<?endforeach?>
</ul>

<pre>
<? print_r($User) ?>
<? print_r(get_class_methods($User->user)) ?>
</pre>


