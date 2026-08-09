<?php

header( "Content-Security-Policy: default-src 'self'; script-src 'self'" );

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Feedback for end user
	$name = htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	$html .= '<pre>Hello ' . $name . '</pre>';
}

?>
