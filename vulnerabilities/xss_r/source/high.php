<?php

header ("X-Content-Type-Options: nosniff");

// Is there any input?
if( array_key_exists( "name", $_GET ) && is_string( $_GET[ 'name' ] ) && $_GET[ 'name' ] != '' ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	// NOTE: a regex blacklist for the substring "script" only stops one of the
	// countless XSS vectors, and still misses non-<script> payloads such as
	// <img src=x onerror=...>, <svg onload=...>, bare event handlers
	// (onmouseover=...), and javascript: URIs. The only reliable fix is to
	// encode the output for the HTML context it is placed in, exactly as
	// impossible.php does.
	$name = htmlspecialchars( strip_tags( $_GET[ 'name' ] ), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

generateSessionToken();

?>
