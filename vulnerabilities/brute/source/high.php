<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get username
	$user = isset( $_GET[ 'username' ] ) ? stripslashes( $_GET[ 'username' ] ) : '';

	// Get password
	$pass = isset( $_GET[ 'password' ] ) ? stripslashes( $_GET[ 'password' ] ) : '';
	$pass = md5( $pass );

	// Unlimited guessing is the whole attack, so an account is taken out of
	// service for a while after a few consecutive failures.
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT failed_login, last_login FROM users WHERE user = ? LIMIT 1;" );

	if( $stmt ) {
		mysqli_stmt_bind_param( $stmt, "s", $user );
		mysqli_stmt_execute( $stmt );
		$lookup = mysqli_stmt_get_result( $stmt );
		$record = $lookup ? mysqli_fetch_assoc( $lookup ) : false;
		mysqli_stmt_close( $stmt );

		if( $record && $record[ 'failed_login' ] >= $total_failed_login ) {
			$timeout = strtotime( $record[ 'last_login' ] ) + ( $lockout_time * 60 );

			if( time() < $timeout ) {
				$account_locked = true;
			}
		}
	}

	// Check the database with a prepared statement
	$row  = false;
	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT avatar FROM users WHERE user = ? AND password = ? LIMIT 1;" );

	if( $stmt ) {
		mysqli_stmt_bind_param( $stmt, "ss", $user, $pass );
		mysqli_stmt_execute( $stmt );
		$result = mysqli_stmt_get_result( $stmt );
		$row    = $result ? mysqli_fetch_assoc( $result ) : false;
		mysqli_stmt_close( $stmt );
	}

	if( $row && !$account_locked ) {
		// Get users details
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . "</p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES, 'UTF-8' ) . "\" />";

		// Clear the failure counter
		$reset = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = 0 WHERE user = ? LIMIT 1;" );

		if( $reset ) {
			mysqli_stmt_bind_param( $reset, "s", $user );
			mysqli_stmt_execute( $reset );
			mysqli_stmt_close( $reset );
		}
	}
	else {
		// Login failed. A random delay alone only slowed an attacker down, so
		// it is now backed by the lockout above. The wording is identical
		// whether the credentials were wrong or the account is locked, so
		// nothing is disclosed.
		sleep( rand( 0, 3 ) );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		$bump = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = ( failed_login + 1 ) WHERE user = ? LIMIT 1;" );

		if( $bump ) {
			mysqli_stmt_bind_param( $bump, "s", $user );
			mysqli_stmt_execute( $bump );
			mysqli_stmt_close( $bump );
		}
	}

	// Record when this attempt happened
	$touch = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = now() WHERE user = ? LIMIT 1;" );

	if( $touch ) {
		mysqli_stmt_bind_param( $touch, "s", $user );
		mysqli_stmt_execute( $touch );
		mysqli_stmt_close( $touch );
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
