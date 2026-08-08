<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	$target_parts = parse_url ($target);

	// A relative URL must not select a scheme or network host. Backslashes and
	// control characters are rejected because browsers may normalize them into
	// an absolute URL or treat them as header delimiters.
	if ($target_parts !== false &&
		!isset ($target_parts['scheme']) &&
		!isset ($target_parts['host']) &&
		strpos ($target, "\\") === false &&
		!preg_match ('/[\x00-\x1F\x7F]/', $target)) {
		header ("location: " . $target);
		exit;
	}

	http_response_code (500);
	?>
	<p>Absolute URLs not allowed.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
