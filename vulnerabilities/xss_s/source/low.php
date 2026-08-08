<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Sanitize message input: strip any HTML/JS structure before it is ever
	// written to the database, then encode anything that remains
	$message = strip_tags( $message );
	$message = htmlspecialchars( $message, ENT_QUOTES );

	// Sanitize name input
	$name = strip_tags( $name );
	$name = htmlspecialchars( $name, ENT_QUOTES );

	// Update database using a parameterised (prepared) statement
	$data = $db->prepare( 'INSERT INTO guestbook ( comment, name ) VALUES ( :message, :name );' );
	$data->bindParam( ':message', $message, PDO::PARAM_STR );
	$data->bindParam( ':name', $name, PDO::PARAM_STR );
	$data->execute();
}

?>
