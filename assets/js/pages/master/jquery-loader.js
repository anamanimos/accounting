// This file will load jQuery from CDN if not already loaded
// and provides a callback mechanism for dependent scripts
(function () {
	// Array to store callbacks waiting for jQuery
	window.onJQueryReady = window.onJQueryReady || [];

	function executeCallbacks() {
		while (window.onJQueryReady.length > 0) {
			var callback = window.onJQueryReady.shift();
			if (typeof callback === "function") {
				callback();
			}
		}
	}

	if (typeof window.jQuery !== "undefined") {
		// jQuery already loaded, execute any pending callbacks
		executeCallbacks();
	} else {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "https://code.jquery.com/jquery-3.6.0.min.js";
		script.onload = function () {
			console.log("jQuery loaded dynamically");
			executeCallbacks();
		};
		document.head.appendChild(script);
	}
})();
