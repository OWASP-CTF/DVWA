<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Neutralise both fields for the HTML context the guestbook renders them
	// in, before they are ever persisted.
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
