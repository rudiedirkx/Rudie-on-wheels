
<img src="http://hotblocks.nl/stars.jpg?nocache=<?=rand(0,99999999999)?>" width=100 />

<script>
<?$this->section('javascript')?>
$(function() {

	// domready

	window.setTimeout(function( who ) {
		alert(who + ' suck!');
	}, 500, 'you');

	$$('a').bind('click', function(e) {
		e.preventDefault()
		console.log(e)
	});

})
<?$this->section('javascript')?>
</script>
