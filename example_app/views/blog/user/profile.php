
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
		<td><?=$user->full_name?></td>
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

<?if( 'POST' == $_SERVER['REQUEST_METHOD'] ):?>
	<p><a href="javascript:closeOverlay();void(0);">close</a></p>
<?endif?>
