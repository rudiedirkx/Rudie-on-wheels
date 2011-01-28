
<form method="post">
<fieldset>

	<legend><?if($post->new):?>Add post<?else:?>Edit post # <?=$post->post_id?><?endif?></legend>

	<p class="field <?=$validator->ifError('category_id')?>">Category:<br><?=$this::select($categories, array('name' => 'category_id', 'value' => $validator->valueFor('category_id', $post->category_id)))?></p>

	<p class="field <?=$validator->ifError('title')?>">Title:<br><input name="title" value="<?=$this::html($validator->valueFor('title', $post->title))?>"></p>

	<p class="field <?=$validator->ifError('body')?>">Body:<br><textarea rows="5" name="body"><?=$this::html($validator->valueFor('body', $post->body))?></textarea></p>

	<p><input type=submit></p>

</fieldset>
</form>
