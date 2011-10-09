function doAjaxAction(el, handler) {
	return $.ajax(el.href, function(t) {
		handler(el, t);
	});
}

function closeOverlay() {
	var ov = $('#overlays > div:last-child');
	if ( ov ) {
		ov.parentNode.removeChild(ov);
	}
	return false;
}

function openOverlay(html) {
	var div = document.createElement('div');
	div.className = 'overlay';
	div.innerHTML = '<div>' + html + '</div>';
	$('#overlays').appendChild(div);
	return false;
}

function openInAjaxPopup(url) {
	return $.ajax(url, openOverlay);
}

(function() {

	var more = 'crap', here;

})();
