<?php

// A substring check for "info.php" is bypassable (e.g. "http://evil.com/?x=info.php");
// only an exact match against known-safe local targets is trustworthy.
$allowed_redirects = array ("info.php?id=1", "info.php?id=2");

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	if (in_array ($_GET['redirect'], $allowed_redirects, true)) {
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
