<?php

if( isset( $_GET[ 'Change' ] ) ) {
	// The change has to be proved to come from the user, not from a page some
	// other site got them to load. Either the Anti-CSRF token bound to this
	// session, or the current password, is enough: an attacker forging the
	// request cross site can supply neither.
	$token_ok = isset( $_SESSION[ 'session_token' ] ) && isset( $_REQUEST[ 'user_token' ] ) &&
		is_string( $_REQUEST[ 'user_token' ] ) &&
		hash_equals( (string)$_SESSION[ 'session_token' ], $_REQUEST[ 'user_token' ] );

	$current_password_ok = false;
	if( !$token_ok && isset( $_REQUEST[ 'password_current' ] ) && is_string( $_REQUEST[ 'password_current' ] ) ) {
		$pass_curr = stripslashes( $_REQUEST[ 'password_current' ] );
		$pass_curr = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_curr ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
		$pass_curr = md5( $pass_curr );

		$check = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$check_user = dvwaCurrentUser();
		$check->bindParam( ':user', $check_user, PDO::PARAM_STR );
		$check->bindParam( ':password', $pass_curr, PDO::PARAM_STR );
		$check->execute();
		$current_password_ok = ( $check->fetch() !== false );
	}

	if( !$token_ok && !$current_password_ok ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( 'index.php' );
	}

	// Get input
	$pass_new  = $_GET[ 'password_new' ];
	$pass_conf = $_GET[ 'password_conf' ];

	// Do the passwords match?
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = stripslashes( $pass_new );
		$pass_new = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass_new ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
		$pass_new = md5( $pass_new );

		// Update the database
		$current_user = dvwaCurrentUser();
		$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
		$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
		$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
		$data->execute();

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
