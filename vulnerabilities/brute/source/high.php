<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Sanitise username input
	$user = $_GET[ 'username' ];
	$user = stripslashes( $user );
	$user = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $user ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

	// Sanitise password input
	$pass = $_GET[ 'password' ];
	$pass = stripslashes( $pass );
	$pass = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
	$pass = md5( $pass );

	// Lockout settings, mirrors impossible.php to stop unthrottled brute forcing
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	// Check whether the account is currently locked out
	$lock_row = null;
	if( $lock_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], 'SELECT failed_login, last_login FROM users WHERE user = ? LIMIT 1;' ) ) {
		mysqli_stmt_bind_param( $lock_stmt, 's', $user );
		mysqli_stmt_execute( $lock_stmt );
		if( $lock_result = mysqli_stmt_get_result( $lock_stmt ) ) {
			$lock_row = mysqli_fetch_assoc( $lock_result );
		}
		mysqli_stmt_close( $lock_stmt );
	}

	// Coalesce guards against a NULL counter (unseeded/legacy schema) never tripping the lockout
	if( $lock_row && ( ( $lock_row[ 'failed_login' ] ?? 0 ) >= $total_failed_login ) ) {
		$last_login = strtotime( $lock_row[ 'last_login' ] );
		$timeout    = $last_login + ( $lockout_time * 60 );

		if( time() < $timeout ) {
			$account_locked = true;
		}
	}

	// Check database
	$query  = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

	if( $result && mysqli_num_rows( $result ) == 1 && !$account_locked ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// Reset bad login count
		$reset_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], 'UPDATE users SET failed_login = 0 WHERE user = ? LIMIT 1;' );
		mysqli_stmt_bind_param( $reset_stmt, 's', $user );
		mysqli_stmt_execute( $reset_stmt );
		mysqli_stmt_close( $reset_stmt );
	}
	else {
		// Login failed
		sleep( rand( 0, 3 ) );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Track bad login count for the lockout check above
		$fail_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], 'UPDATE users SET failed_login = COALESCE(failed_login, 0) + 1 WHERE user = ? LIMIT 1;' );
		mysqli_stmt_bind_param( $fail_stmt, 's', $user );
		mysqli_stmt_execute( $fail_stmt );
		mysqli_stmt_close( $fail_stmt );
	}

	// Set the last login time
	$time_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], 'UPDATE users SET last_login = NOW() WHERE user = ? LIMIT 1;' );
	mysqli_stmt_bind_param( $time_stmt, 's', $user );
	mysqli_stmt_execute( $time_stmt );
	mysqli_stmt_close( $time_stmt );

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
