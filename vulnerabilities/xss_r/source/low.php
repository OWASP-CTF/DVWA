<?php

header ("X-Content-Type-Options: nosniff");

// Is there any input?
if( array_key_exists( "name", $_GET ) && is_string( $_GET[ 'name' ] ) && $_GET[ 'name' ] != '' ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );
	// Get input
	$name = htmlspecialchars( strip_tags( $_GET[ 'name' ] ), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

	// Feedback for end user
	$html .= '<pre>Hello ' . $name . '</pre>';
}

generateSessionToken();

?>
