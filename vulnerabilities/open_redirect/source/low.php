<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// The raw value went straight into the Location header, so any absolute URL
	// or protocol-relative "//host" sent visitors off-site. Normalise backslashes
	// to forward slashes first (browsers treat them the same, so "/\evil.com"
	// would otherwise slip past parse_url() looking like a relative path), then
	// require the parsed value to carry no scheme and no host - i.e. it must be a
	// plain same-site path.
	$target = str_replace( '\\', '/', $_GET['redirect'] );
	$parts  = parse_url( $target );

	if ( !empty( $parts['scheme'] ) || !empty( $parts['host'] ) ) {
		http_response_code (500);
		?>
		<p>Absolute URLs not allowed.</p>
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
