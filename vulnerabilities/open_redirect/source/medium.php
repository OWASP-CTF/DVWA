<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// The old blacklist only matched a literal "http://"/"https://" prefix, so a
	// protocol-relative URL ("//evil.com"), a different scheme ("javascript:..."),
	// or a backslash variant ("/\evil.com", which browsers treat as "//evil.com")
	// all sailed through untouched. Parse the (backslash-normalised) value and
	// reject anything carrying a scheme or a host instead of pattern-matching.
	$target = str_replace( '\\', '/', $_GET['redirect'] );
	$parts  = parse_url( $target );

	if ( !empty( $parts['scheme'] ) || !empty( $parts['host'] ) ) {
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
