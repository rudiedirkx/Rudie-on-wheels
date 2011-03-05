
window.$ = function(q) {
	return document.querySelector(q);
}

$.post = function(url, handler, data) {
	var xhr = new XMLHttpRequest;
	xhr.open('POST', url);
	xhr.setRequestHeader('Ajax', '1');
	xhr.onreadystatechange = function(e) {
		if ( 4 === this.readyState ) {
			this.event = e;
			handler.call(this, this.responseText);
		}
	};
	xhr.send(data || '');
	return false;
}

window.$$ = function(q) {
	return document.querySelectorAll(q);
}

function doAjaxAction(el, handler) {
	return $.post(el.href, function(t) {
		handler(el, t);
	});
}

function closeOverlay() {
	var ov = $('body > .overlay:last-child');
	if ( ov ) {
		ov.parentNode.removeChild(ov);
	}
	return false;
}

function openOverlay(html) {
	var div = document.createElement('div');
	div.className = 'overlay';
	div.innerHTML = '<div>' + html + '</div>';
	document.body.appendChild(div);
	return false;
}

function openInAjaxPopup(url) {
	return $.post(url, openOverlay);
}
