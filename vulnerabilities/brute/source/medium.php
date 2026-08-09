<?php

if( isset( $_POST[ 'Login' ] ) ) {
	// Check Anti-CSRF token
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}
	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'index.php' );

	// Get username
	$user = $_POST[ 'username' ];

	// Get password
	$pass = $_POST[ 'password' ];
	$pass = md5( $pass );

	// Default values
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	// Check to see if the account is currently locked out
	$lockout_query = "SELECT failed_login, last_login FROM users WHERE user = ? LIMIT 1;";
	$lockout_stmt  = mysqli_prepare($GLOBALS["___mysqli_ston"], $lockout_query);
	mysqli_stmt_bind_param($lockout_stmt, 's', $user);
	mysqli_stmt_execute($lockout_stmt);
	$lockout_result = mysqli_stmt_get_result($lockout_stmt);
	if( $lockout_result && mysqli_num_rows( $lockout_result ) == 1 ) {
		$lockout_row = mysqli_fetch_assoc( $lockout_result );
		if( $lockout_row[ 'failed_login' ] >= $total_failed_login ) {
			$last_login = strtotime( $lockout_row[ 'last_login' ] );
			$timeout    = $last_login + ($lockout_time * 60);
			if( time() < $timeout ) {
				$account_locked = true;
			}
		}
	}
	mysqli_stmt_close($lockout_stmt);

	// Check the database
	$query  = "SELECT * FROM `users` WHERE user = ? AND password = ?;";
	$stmt   = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
	mysqli_stmt_bind_param($stmt, 'ss', $user, $pass);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if( $result && mysqli_num_rows( $result ) == 1 && !$account_locked ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful. Encode before echoing - the username came
		// straight from the request and must not be trusted as HTML.
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . "</p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES, 'UTF-8' ) . "\" />";

		// Reset bad login count
		$reset_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = 0 WHERE user = ? LIMIT 1;");
		mysqli_stmt_bind_param($reset_stmt, 's', $user);
		mysqli_stmt_execute($reset_stmt);
		mysqli_stmt_close($reset_stmt);
	}
	else {
		// Login failed
		sleep( 2 );
		if( $account_locked ) {
			$html .= "<pre><br />This account has been locked due to too many incorrect logins. Please try again in {$lockout_time} minutes.</pre>";
		} else {
			$html .= "<pre><br />Username and/or password incorrect.</pre>";
		}

		// Update bad login count
		$fail_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = failed_login + 1 WHERE user = ? LIMIT 1;");
		mysqli_stmt_bind_param($fail_stmt, 's', $user);
		mysqli_stmt_execute($fail_stmt);
		mysqli_stmt_close($fail_stmt);
	}
	mysqli_stmt_close($stmt);

	// Set the last login time
	$last_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = now() WHERE user = ? LIMIT 1;");
	mysqli_stmt_bind_param($last_stmt, 's', $user);
	mysqli_stmt_execute($last_stmt);
	mysqli_stmt_close($last_stmt);

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
