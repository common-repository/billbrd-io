(function() {
	let blbrd_tracking_script = document.createElement("script");
	blbrd_tracking_script.async = true;
	blbrd_tracking_script.src = "https://billbrd.io/tracking";
	blbrd_tracking_script.onload = function() {
		tracking_obj.pubKeyVal = blbrd_vars.public_api_key;
		tracking_obj.initiateClickEvent();
	}
	let first_script = document.getElementsByTagName("script")[0];
	first_script.parentNode.insertBefore(blbrd_tracking_script, first_script);
})();