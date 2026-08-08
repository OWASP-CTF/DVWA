<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = "";

	// A substring/whitelist check on the raw value is still bypassable if
	// it isn't anchored to the whole string. Rebuild the destination from a
	// validated numeric id instead, so the redirect target can never be
	// anything other than the local info page.
	if (preg_match ('/^info\.php\?id=([0-9]{1,9})$/', $_GET['redirect'], $matches)) {
		$target = "info.php?id=" . intval ($matches[1]);
	}

	if ($target != "") {
		header ("location: " . $target);
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
