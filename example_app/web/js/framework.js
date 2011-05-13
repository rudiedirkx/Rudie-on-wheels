
window.$ = function(q) {
	if ( 'function' == typeof q ) {
		if ( 'complete' == document.readyState ) {
			q();
			return;
		}
		window.bind('load', q);
		return;
	}
	return document.querySelector(q);
}
HTMLElement.prototype.$ = function(q) {
	return this.querySelector(q);
};

window.$$ = function(q) {
	return Array.prototype.slice.call(document.querySelectorAll(q), 0);
}
HTMLElement.prototype.$$ = function(q) {
	return this.querySelectorAll(q);
};

Array.prototype.each = Array.prototype.forEach;
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
HTMLElement.prototype.prev = function() {
	var s = this.previousSibling;
	if ( s && s.nodeType != 1 ) {
		s = s.previousSibling;
	}
	return s;
};
HTMLElement.prototype.next = function() {
	var s = this.nextSibling;
	if ( s && s.nodeType != 1 ) {
		s = s.nextSibling;
	}
	return s;
};
HTMLElement.prototype.html = function(html) {
	if ( html != null ) {
		this.innerHTML = html;
		return this;
	}
	return this.innerHTML;
};

window.$.ajax = function(url, handler, data) {
	var xhr = new XMLHttpRequest,
		method = data ? 'POST' : 'GET';
	xhr.open(method, url);
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
