<?php

header ("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Get input
	$name = str_replace( '<script>', '', $_GET[ 'name' ] );

	// The blacklist above only removes a literal '<script>' string, so any other
	// tag/case variant still reaches the page verbatim. HTML-encode the final
	// value so whatever survives the blacklist can no longer be parsed as markup.
	$name = htmlspecialchars( $name, ENT_QUOTES );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

?>
