<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// strpos() only checked that the string "info.php" appears *somewhere* in the
	// value, so "https://evil.com/info.php", "//evil.com/info.php", and even
	// "info.php.evil.com" all matched. A follow-up attempt (parse_url() on a
	// backslash-normalised copy, requiring no scheme/host and an exact
	// "info.php" path) still echoed the attacker-controlled $_GET['redirect']
	// value straight into the Location header whenever the check passed - which
	// leaves a parser-differential gap open: a byte sequence a browser's URL
	// parser strips or reinterprets (control characters, stray slashes, etc.)
	// before navigating can look harmless to PHP's parse_url() while the
	// browser still ends up somewhere else. The only legitimate destination is
	// info.php with a numeric quote id (see index.php's links), so validate
	// that shape and rebuild the Location value from scratch - nothing the
	// visitor supplies is ever reflected back verbatim.
	$id = null;
	if ( preg_match( '/^info\.php\?id=([0-9]{1,9})$/', $_GET['redirect'], $matches ) ) {
		$id = intval( $matches[1] );
	}

	if ( $id !== null ) {
		header ("location: info.php?id=" . $id);
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
