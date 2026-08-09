<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	// Sanitize message input
	$message = htmlspecialchars( stripslashes( $message ), ENT_QUOTES, 'UTF-8' );

	// Sanitize name input
	$name = htmlspecialchars( stripslashes( $name ), ENT_QUOTES, 'UTF-8' );

	// Update database
	$data = $db->prepare( 'INSERT INTO guestbook ( comment, name ) VALUES ( :message, :name );' );
	$data->bindParam( ':message', $message, PDO::PARAM_STR );
	$data->bindParam( ':name', $name, PDO::PARAM_STR );
	$data->execute();

	//mysql_close();
}

?>
