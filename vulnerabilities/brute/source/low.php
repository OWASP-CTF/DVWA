<?php

if( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' && isset( $_POST[ 'Login' ], $_POST[ 'username' ], $_POST[ 'password' ], $_POST[ 'user_token' ] ) &&
	is_string( $_POST[ 'username' ] ) && is_string( $_POST[ 'password' ] ) && is_string( $_POST[ 'user_token' ] ) ) {
	checkToken( $_POST[ 'user_token' ], $_SESSION[ 'session_token' ] ?? '', 'index.php' );

	// Get username
	$user = $_POST[ 'username' ];

	// Get password
	$pass = $_POST[ 'password' ];
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

	// Check to see if the user has been locked out
	if( ( $row !== false ) && ( $row[ 'failed_login' ] >= $total_failed_login ) ) {
		// Calculate when the user would be allowed to login again
		$last_login = strtotime( $row[ 'last_login' ] );
		$timeout    = $last_login + ( $lockout_time * 60 );
		$timenow    = time();

		// Check to see if enough time has passed, if it hasn't the account stays locked
		if( $timenow < $timeout ) {
			$account_locked = true;
		}
	}

	// Check the database (if username matches the password)
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( ( $row !== false ) && ( $account_locked == false ) ) {
		// Get users details
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . "</p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . "\" />";

		// Reset the bad login count
		$data = $db->prepare( 'UPDATE users SET failed_login = "0" WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Login failed (or the account is locked out)
		// Always impose a delay even before the account reaches its lock threshold.
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Update the bad login count
		$data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}

	// Set the last login time
	$data = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
}

// Rotate after every response so a captured login request is single-use.
generateSessionToken();

?>
