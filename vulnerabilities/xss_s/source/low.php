<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Bound the input to what the columns actually hold, so a long entry is
	// rejected by us rather than silently truncated by the database. The form's
	// maxlength attributes are a client side hint and are deliberately not
	// mirrored here: they are a usability nicety, not a security control, and
	// clamping to them threw away most of a normal comment. What neutralises a
	// payload is the encoding in dvwaGuestbook(), not the length.
	//
	// mb_substr, because a byte-wise cut can leave half a multi-byte character
	// behind and the encoder would then drop the whole field.
	$message = mb_substr( stripslashes( $message ), 0, 300, 'UTF-8' );
	$name    = mb_substr( stripslashes( $name ), 0, 100, 'UTF-8' );

	// This level encoded neither field, so both were stored raw and ran on
	// every later page view.
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
