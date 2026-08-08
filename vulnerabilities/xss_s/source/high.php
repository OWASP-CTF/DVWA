<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Sanitize message input
	$message = stripslashes( $message );
	$message = htmlspecialchars( $message, ENT_QUOTES, 'UTF-8' );

	// Sanitize name input
	$name = stripslashes( $name );
	$name = htmlspecialchars( $name, ENT_QUOTES, 'UTF-8' );

	// Update database, using a prepared statement so the input can never be
	// parsed as SQL.
	$data = $db->prepare( 'INSERT INTO guestbook ( comment, name ) VALUES ( :message, :name );' );
	$data->bindParam( ':message', $message, PDO::PARAM_STR );
	$data->bindParam( ':name', $name, PDO::PARAM_STR );
	$data->execute();
}

?>
