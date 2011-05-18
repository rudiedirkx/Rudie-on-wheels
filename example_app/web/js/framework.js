
$ = function(q) {
	if ( 'function' == typeof q ) {
		if ( 'complete' == document.readyState ) {
			q();
			return;
		}
		this.bind('load', q);
		return;
	}
	return document.querySelector(q);
}
document.$ = $;
HTMLElement.prototype.one = function(q) {
	if ( 'string' != typeof q ) {
		return q;
	}
	return this.querySelector(q);
};

A = function(arr) {
	try {
		return Array.prototype.slice.call(arr);
	}
	catch (ex) {
		for ( var r = [], L = arr.length, i = 0; i<L; i++ ) {
			r.push(arr[i]);
		}
		return r;
	}
};

Array.prototype.contains = function(el) {
	for ( var L=this.length, i=0; i<L; i++ ) {
		if ( el == this[i] ) {
			return true;
		}
	}
	return false;
};

$$ = function(q) {
	return A(document.querySelectorAll(q), 0);
}
document.$$ = $$;
HTMLElement.prototype.all = function(q) {
	return A(this.querySelectorAll(q));
};

Array.prototype.each = Array.prototype.forEach;
Object.prototype.bind = function(type, event) { // Object so Window inherits it too
	this.addEventListener(type, event, false);
	return this;
};
Object.prototype.invoke = function(method, args) {
	this[method].apply(this, args);
	return this;
};
Array.prototype.invoke = function(method, args) {
	this.each(function(el) {
		el[method].apply(el, args);
	});
	return this;
};
Array.prototype.bind = function(type, fn) {
	return this.invoke('bind', [type, fn]);
};

HTMLElement.prototype.attr = function(key, val) {
	if ( val === undefined ) {
		return this.getAttribute(key);
	}
	this.setAttribute(key, val);
	return this;
};

HTMLElement.prototype.serialize = function() {
	var v = [];
	A(this.elements).forEach(function(el, i) {
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
		if ( 'function' == typeof html ) {
			html = html(this);
		}
		this.innerHTML = html;
		return this;
	}
	return this.innerHTML;
};

HTMLElement.prototype.is = function(q) {
	return this.parentNode.all(q).contains(this);
};
HTMLElement.prototype.parent = function(q) {
	if ( !q ) {
		return this.parentNode;
	}
	var p = this.parentNode;
	try {
		while ( p && !p.is(q) ) {
			p = p.parentNode;
		}
		return p;
	}
	catch (ex) {}
	return false;
};

$.ajax = function(url, handler, data, options) {
	var xhr = new XMLHttpRequest,
		method = data ? 'POST' : 'GET';
	xhr.open(method, url);
	xhr.setRequestHeader('Ajax', '1');
	if ( data ) {
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	}
	if ( options ) {
		try {
			for ( x in options ) {
				if ( options.hasOwnProperty(x) ) {
					xhr[x] = options[x];
				}
			}
		}
		catch (ex) { alert(ex.message); }
	}
	xhr.onreadystatechange = function(e) {
		if ( 4 === this.readyState ) {
			this.event = e;
			handler.call(this, this.responseText);
		}
	};
	xhr.send(data || '');
console.log(xhr);
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
