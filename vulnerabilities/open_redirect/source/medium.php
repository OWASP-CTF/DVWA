<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = "";

	// Blocking "http://"/"https://" substrings misses protocol-relative
	// URLs ("//evil.com"), other schemes, and doesn't stop redirecting to
	// arbitrary local paths either. Rebuild the destination from a
	// validated numeric id instead, so it can only ever be the local info
	// page.
	if (preg_match ('/^info\.php\?id=([0-9]{1,9})$/', $_GET['redirect'], $matches)) {
		$target = "info.php?id=" . intval ($matches[1]);
	}

	if ($target != "") {
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
