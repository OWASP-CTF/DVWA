<?php

header ("X-Content-Type-Options: nosniff");

// Is there any input?
if( array_key_exists( "name", $_GET ) && is_string( $_GET[ 'name' ] ) && $_GET[ 'name' ] != '' ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	// NOTE: blacklisting tag substrings (e.g. removing "<script>") is not a valid
	// defence against XSS -- it is trivially bypassed with case changes, nested
	// tags (<scr<script>ipt>), or any of the countless non-<script> vectors
	// (<img onerror>, <svg onload>, event handlers, javascript: URIs, ...).
	// The only reliable fix is to encode the output for the HTML context it is
	// placed in, exactly as impossible.php does.
	$name = htmlspecialchars( strip_tags( $_GET[ 'name' ] ), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

generateSessionToken();

?>
