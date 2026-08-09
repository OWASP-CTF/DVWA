<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	// strpos() only checked that the string "info.php" appears *somewhere* in the
	// value, so "https://evil.com/info.php", "//evil.com/info.php", and even
	// "info.php.evil.com" all matched. Parse the (backslash-normalised, since
	// browsers treat "\" as "/") value instead and only allow it through when it
	// carries no scheme/host and its path is exactly the literal "info.php".
	$target = str_replace( '\\', '/', $_GET['redirect'] );
	$parts  = parse_url( $target );
	$path   = isset( $parts['path'] ) ? $parts['path'] : '';

	if ( empty( $parts['scheme'] ) && empty( $parts['host'] ) && $path === 'info.php' ) {
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
