<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	// PATCH (CTF): only allow same-site (relative) redirects; block absolute/scheme/protocol-relative URLs.
	if (preg_match ('#^https?://#i', $target) || strpos ($target, '//') === 0) {
		http_response_code (400);
		echo "Invalid redirect target.";
		exit;
	}
	header ("location: " . $target);
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
