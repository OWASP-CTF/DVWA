<?php

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Stripping the literal string "<script>" left every other tag and event
	// handler intact. Encode for the output context instead of blacklisting.
	$name = htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES, 'UTF-8' );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

?>
