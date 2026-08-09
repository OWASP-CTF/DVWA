<?php

header ("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Feedback for end user - HTML-encode before reflecting so markup in the
	// input cannot be interpreted by the browser
	$html .= '<pre>Hello ' . htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES ) . '</pre>';
}

?>
