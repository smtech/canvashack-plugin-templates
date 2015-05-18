/*jslint browser: true, devel: true, eqeq: true, plusplus: true, sloppy: true, vars: true, white: true */

var STMARKS = {
	waitForDOMDelay: 100, // milliseconds
	user_id: document.getElementById('identity').getElementsByClassName('user_id')[0].innerHTML
};

/* wait for a particular DOM element to be ready (i.e. created) and then call
   a function to take the next steps. Used thus:
   
	function test(DOMElement) {
		alert(DOMElement.innerHTML);
	}
	stmarks_waitForDOMById(/.*\/courses\/\d+\/settings/, 'right-side', test);
	
	In this case, the code is waiting for div id="right-side" to be ready, at
	which point a reference to that node will be sent to the test(). Obviously,
	test() could be an inline function instead...
	
	The result of stmarks_waiForDOMById can be tested to see if the page URL
	itself matches. */
function stmarks_waitForDOMById(UrlPattern, nodeId, callback) {
	if (UrlPattern.test(document.location.href)) {
		var DOMElement = document.getElementById(nodeId);
		if (DOMElement !== undefined) {
			callback(DOMElement);
		} else {
			window.setTimeout(
				function() {
					stmarks_waitForDOMById(UrlPattern, nodeId, callback);
				},
				STMARKS.waitForDOMDelay
			);
		}
		return true;
	}
	return false;
}

function stmarks_waitForDOMByClassName(UrlPattern, className, callback) {
	if (UrlPattern.test(document.location.href)) {
		var DOMElements = document.getElementsByClassName(className);
		if (DOMElements.length > 0) {
			callback(DOMElements);
		} else {
			window.setTimeout(
				function() {
					stmarks_waitForDOMByClassName(UrlPattern, className, callback);
				}, 
				STMARKS.waitForDOMDelay
			);
		}
		return true;
	}
	return false;
}

function stmarks_globalJavascript() {
	
	/* start by framebusting -- which seems hostile, but really just lets us link
	   across courses without ending up with stupidly nested frames */
	/* if (top.location != self.location) {
    	top.location = self.location.href;
	} */

	
	// modeled on http://stackoverflow.com/a/984656, which I found to be unreliable
	var i;
	var scripts = document.getElementsByTagName('script');
	var pathToScripts;
	for (i = 0; i < scripts.length; i++) {
		if (scripts[i].src.indexOf('stmarksschool.org') != -1) {
			pathToScripts = scripts[i].src.substr(0, scripts[i].src.indexOf('global.js'));
			break;
		}
	}
	
	// Works, so long as Canvas includes JQuery ahead of us...
	// http://stackoverflow.com/a/950146

	$.getScript(
		pathToScripts + 'templates.php?user_id=' + STMARKS.user_id + '&location=' + window.location,
		function() {
			stmarks_templates();
		}
	);	
}

stmarks_globalJavascript();