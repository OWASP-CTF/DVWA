<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Sanitize message input
	$message = htmlspecialchars( stripslashes( $message ), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

	// Sanitize name input
	$name = htmlspecialchars( stripslashes( $name ), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

	// Update database
	$stmt = $GLOBALS["___mysqli_ston"]->prepare( 'INSERT INTO guestbook ( comment, name ) VALUES ( ?, ? );' );
	$stmt->bind_param( 'ss', $message, $name );
	$stmt->execute();

	//mysql_close();
}

?>
