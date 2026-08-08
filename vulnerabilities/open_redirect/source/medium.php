<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$redirect = $_GET['redirect'];
	// Only allow same-site relative paths: reject absolute/scheme-relative URLs and backslashes.
	if (preg_match ('#^[a-z][a-z0-9+.-]*:#i', $redirect) || substr ($redirect, 0, 2) === "//" || strpos ($redirect, "\\") !== false) {
		http_response_code (400);
		?>
		<p>Only relative redirects are allowed.</p>
		<?php
		exit;
	} else {
		header ("location: " . $redirect);
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
