<?php

// Allowlist of known-safe local targets; the redirect param must be an exact match.
$allowed_redirects = array ("info.php?id=1", "info.php?id=2");

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if (in_array ($_GET['redirect'], $allowed_redirects, true)) {
		header ("location: " . $_GET['redirect']);
		exit;
	}

	http_response_code (500);
	?>
	<p>Invalid redirect target.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
