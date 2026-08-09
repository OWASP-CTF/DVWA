<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	$parts  = parse_url ($target);

	// A same-site relative reference must not name a scheme or a network
	// host - that is what would let the caller point the browser somewhere
	// else entirely. Backslashes and raw control characters are rejected too,
	// since browsers may normalise a leading backslash into a scheme-relative
	// URL and a control character could be used to split the response.
	if ($parts !== false &&
		!isset ($parts['scheme']) &&
		!isset ($parts['host']) &&
		strpos ($target, "\\") === false &&
		!preg_match ('/[\x00-\x1F\x7F]/', $target)) {
		header ("location: " . $target);
		exit;
	}

	http_response_code (500);
	?>
	<p>You can only redirect to the info page.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
