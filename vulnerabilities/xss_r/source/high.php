<?php

header ("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Get input
	$name = preg_replace( '/<(.*)s(.*)c(.*)r(.*)i(.*)p(.*)t/i', '', $_GET[ 'name' ] );

	// The regex above only strips things that spell out "script"; other tags
	// (<img>, <svg>, <body onload=...>, etc.) pass through untouched. HTML-encode
	// the final value so nothing that remains can be parsed as markup.
	$name = htmlspecialchars( $name, ENT_QUOTES );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

?>
