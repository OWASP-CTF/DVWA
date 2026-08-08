<?php

// The login is only accepted over POST, so the credentials never travel in a
// URL where they end up in history, logs and referrers, and it carries the
// Anti-CSRF token bound to this session, exactly as the impossible level does.
if( isset( $_POST[ 'Login' ] ) && isset( $_POST[ 'username' ] ) && isset( $_POST[ 'password' ] ) ) {
	// Check Anti-CSRF token
	checkToken( isset( $_REQUEST[ 'user_token' ] ) ? $_REQUEST[ 'user_token' ] : '', $_SESSION[ 'session_token' ], 'index.php' );

	// Sanitise username input
	$user = $_POST[ 'username' ];
	$user = stripslashes( $user );

	// Sanitise password input
	$pass = $_POST[ 'password' ];
	$pass = stripslashes( $pass );
	$pass = md5( $pass );

	// Default values
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	// Check the database (Check user information)
	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	// Check to see if the user has been locked out.
	if( ( $row !== false ) && ( $row[ 'failed_login' ] >= $total_failed_login ) ) {
		// Calculate when the user would be allowed to login again
		$last_login = strtotime( $row[ 'last_login' ] );
		$timeout    = $last_login + ($lockout_time * 60);
		$timenow    = time();

		// Check to see if enough time has passed, if it hasn't lock the account
		if( $timenow < $timeout ) {
			$account_locked = true;
		}
	}

	// Check the database (if username matches the password), using a prepared
	// statement so neither field can be parsed as SQL.
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	// If its a valid login...
	if( ( $row !== false ) && ( $account_locked == false ) ) {
		// Get users details
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . "</p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES, 'UTF-8' ) . "\" />";

		// Reset bad login count
		$data = $db->prepare( 'UPDATE users SET failed_login = "0" WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Login failed. Count it, so repeated guesses lock the account, and
		// delay every failure the way the impossible level does so guesses
		// cannot be made at speed.
		sleep( 2 );

		$html .= "<pre><br />Username and/or password incorrect.<br /><br/>Alternative, the account has been locked because of too many failed logins.<br />If this is the case, <em>please try again in {$lockout_time} minutes</em>.</pre>";

		// Update bad login count
		$data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}

	// Set the last login time
	$data = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
}

// Generate Anti-CSRF token
generateSessionToken();

?>
