<?php

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Encode the value for the HTML context it is echoed into, so it can only
	// ever render as text.
	$name = htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES, 'UTF-8' );

	// Feedback for end user
	$html .= "<pre>Hello {$name}</pre>";
}

?>
