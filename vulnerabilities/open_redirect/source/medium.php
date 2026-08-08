<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// Blocking "http://"/"https://" substrings misses protocol-relative
	// URLs ("//evil.com"), other schemes, and doesn't stop redirecting to
	// arbitrary local paths either. Whitelist the known targets instead.
	$allowedRedirects = [ "info.php?id=1", "info.php?id=2" ];

	if (in_array($_GET['redirect'], $allowedRedirects, true)) {
		header ("location: " . $_GET['redirect']);
		exit;
	} else {
		http_response_code (500);
		?>
		<p>You can only redirect to the info page.</p>
		<?php
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
