<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// The name field was only having the literal string "<script>" removed,
	// so any other tag or event handler was stored verbatim. Encode both
	// fields for the HTML context the guestbook renders them in.
	$message = htmlspecialchars( stripslashes( $message ), ENT_QUOTES, 'UTF-8' );
	$name    = htmlspecialchars( stripslashes( $name ), ENT_QUOTES, 'UTF-8' );

	// Update database via a prepared statement
	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "INSERT INTO guestbook ( comment, name ) VALUES ( ?, ? );" );

	if( $stmt ) {
		mysqli_stmt_bind_param( $stmt, "ss", $message, $name );
		mysqli_stmt_execute( $stmt );
		mysqli_stmt_close( $stmt );
	}
}

?>
