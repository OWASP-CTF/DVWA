<?php

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// The old pattern only removed things that looked like a <script tag, so
	// <img src=x onerror=...> and friends sailed through. Encode for the
	// output context instead of blacklisting.
	$name = htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES, 'UTF-8' );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

?>
