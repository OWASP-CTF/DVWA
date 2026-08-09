<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// strpos() only checked that the string "info.php" appears *somewhere* in the
	// value, so "https://evil.com/info.php", "//evil.com/info.php", and even
	// "info.php.evil.com" all matched - a bare substring test can't tell an
	// off-site absolute/protocol-relative URL from the intended same-site path
	// just because "info.php" shows up in it somewhere. Reject the value
	// whenever it carries a scheme or a host instead, using the same test the
	// other two levels of this module rely on: normalise backslashes to
	// forward slashes first (browsers resolve "\" the same as "/" when
	// navigating), then parse_url() the result and look at scheme/host rather
	// than scanning for a substring.
	$target = str_replace( '\\', '/', $_GET['redirect'] );
	$parts  = parse_url( $target );

	if ( !empty( $parts['scheme'] ) || !empty( $parts['host'] ) ) {
		http_response_code (500);
		?>
		<p>You can only redirect to the info page.</p>
		<?php
		exit;
	}

	header ("location: " . $_GET['redirect']);
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
