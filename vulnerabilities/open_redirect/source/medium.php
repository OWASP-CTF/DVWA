<?php

// Blocklisting "http(s)://" is bypassable (protocol-relative "//", odd schemes, etc);
// only an exact match against known-safe local targets is trustworthy.
$allowed_redirects = array ("info.php?id=1", "info.php?id=2");

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if (!in_array ($_GET['redirect'], $allowed_redirects, true)) {
		http_response_code (500);
		?>
		<p>Absolute URLs not allowed.</p>
		<?php
		exit;
	} else {
		header ("location: " . $_GET['redirect']);
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
