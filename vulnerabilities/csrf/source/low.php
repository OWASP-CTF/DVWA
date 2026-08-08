<?php

if( isset( $_GET[ 'Change' ] ) ) {
	// A password change must be proven to originate from this application's
	// own form, not from a third-party page riding the session cookie.
	checkToken( isset( $_REQUEST[ 'user_token' ] ) ? $_REQUEST[ 'user_token' ] : '', $_SESSION[ 'session_token' ], 'index.php' );

	// Get input
	$pass_new  = $_GET[ 'password_new' ];
	$pass_conf = $_GET[ 'password_conf' ];

	// Do the passwords match?
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = md5( stripslashes( $pass_new ) );

		// Update the database
		$current_user = dvwaCurrentUser();
		$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE `users` SET password = ? WHERE user = ?;" );

		if( $stmt ) {
			mysqli_stmt_bind_param( $stmt, "ss", $pass_new, $current_user );
			mysqli_stmt_execute( $stmt );
			mysqli_stmt_close( $stmt );
		}

		// Feedback for the user
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		// Issue with passwords matching
		$html .= "<pre>Passwords did not match.</pre>";
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
