<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// The maxlength attributes on the form are a client side hint only, so the
	// lengths are enforced again here. mb_substr, because a byte-wise cut can
	// leave half a multi-byte character behind and the encoder would then drop
	// the whole field.
	$message = mb_substr( stripslashes( $message ), 0, 50, 'UTF-8' );
	$name    = mb_substr( stripslashes( $name ), 0, 10, 'UTF-8' );

	// This level encoded the message but left the name on a '<script>'
	// blacklist.
	//
	// Encoding happens on output, in dvwaGuestbook(), which covers every level
	// and also neutralises rows written before that fix. Encoding here as well
	// would store "&amp;" for a literal "&".

	// Update database with a prepared statement, so the values are never
	// parsed as SQL either.
	$data = $db->prepare( 'INSERT INTO guestbook ( comment, name ) VALUES ( :message, :name );' );
	$data->bindParam( ':message', $message, PDO::PARAM_STR );
	$data->bindParam( ':name', $name, PDO::PARAM_STR );
	$data->execute();
}

// Generate Anti-CSRF token
generateSessionToken();

?>
