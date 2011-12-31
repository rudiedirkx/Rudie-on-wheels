
<style>
table.userdetails th {
	text-align: left;
}
table.userdetails tr > * {
	padding: 4px 9px;
}
</style>

<table class=userdetails border=1>
	<tr>
		<th>Name</th>
		<td><?=$user->full_name?> (<?=$this::link('edit', 'blog/user/edit/'.$user->user_id)?>)</td>
	</tr>
	<tr>
		<th>Bio</th>
		<td><?=$user->bio ?: '&nbsp;'?></td>
	</tr>
	<tr>
		<th>Access</th>
		<td><?=$user->access?></td>
	</tr>
	<tr>
		<th>Posts</th>
		<td><?=count($user->posts)?> or <?=$user->numPosts?></td>
	</tr>
</table>

<?include('close-overlay.php')?>
