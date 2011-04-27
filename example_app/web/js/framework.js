
window.$ = function(q) {
	if ( 'function' == typeof q ) {
		window.bind('load', q);
		return;
	}
	return document.querySelector(q);
}

Object.prototype.bind = function(type, event) { // Object so Window inherits it too
	this.addEventListener(type, event, false);
};
HTMLElement.prototype.serialize = function() {
	var v = [];
	Array.prototype.slice.call(this.elements).forEach(function(el, i) {
		if ( !el.name ) {
			return;
		}
		var type = el.type || el.nodeName.toLowerCase();
		switch( type ) {
			case 'fieldset':
				// no value
			break;
			case 'checkbox':
			case 'radio':
				if ( el.checked ) {
					v.push(encodeURIComponent(el.name) + '=' + encodeURIComponent( el.value || '1' ));
				}
			break;
			default:
				v.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value));
			break;
		}
	});
	return v.join('&');
};

$.post = function(url, handler, data) {
	var xhr = new XMLHttpRequest;
	xhr.open('POST', url);
	xhr.setRequestHeader('Ajax', '1');
	if ( data ) {
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	}
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
