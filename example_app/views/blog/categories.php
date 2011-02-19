
<ul>
	<?foreach($categories AS $cat):?>
		<li><?=$this::link($cat->category_name, $cat->url())?> (<?=$cat->numPosts()?>)</li>
	<?endforeach?>
</ul>


