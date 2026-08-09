<?php

header("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
    // SECURE FIX: Sanitize output to prevent Reflected XSS
    $name = htmlspecialchars( $_GET[ 'name' ], ENT_QUOTES, 'UTF-8' );

    // Feedback for the user
    echo "<pre>Hello {$name}</pre>";
}

?>