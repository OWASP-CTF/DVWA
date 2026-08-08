<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// A substring check for "info.php" is bypassable - e.g.
	// "https://evil.com/?x=info.php" also contains that substring.
	// Whitelist the exact, known-good targets instead.
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
