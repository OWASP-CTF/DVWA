<?php

// Read through $_REQUEST so the handler works whichever way the change arrives.
// The form posts, which keeps the new password out of the URL, but what stops
// a forged request is the Anti-CSRF token and the current-password check, not
// the HTTP verb.
if( isset( $_REQUEST[ 'Change' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Reference implementation: token, re-authentication and bound parameters.

	// Get input
	$pass_curr = isset( $_REQUEST[ 'password_current' ] ) ? $_REQUEST[ 'password_current' ] : '';
	$pass_new  = isset( $_REQUEST[ 'password_new' ] ) ? $_REQUEST[ 'password_new' ] : '';
	$pass_conf = isset( $_REQUEST[ 'password_conf' ] ) ? $_REQUEST[ 'password_conf' ] : '';

	// Check that the current password is correct. Knowing the token is not
	// enough on its own; the request also has to prove it knows the password
	// it is about to replace.
	$current_user = dvwaCurrentUser();
	$data = $db->prepare( 'SELECT password FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	$current_ok = ( $row !== false ) && dvwaPasswordVerify( stripslashes( $pass_curr ), $row[ 'password' ] );

	// Do both new passwords match and does the current password match the user?
	if( ( $pass_new === $pass_conf ) && $current_ok ) {
		// Stored with password_hash(), never as a bare digest.
		dvwaPasswordStore( $current_user, stripslashes( $pass_new ) );

		// Feedback for the user
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		// Deliberately one message for both cases, so it cannot be used to
		// probe whether a given current password was right.
		$html .= "<pre>Passwords did not match or current password incorrect.</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
