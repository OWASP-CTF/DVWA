<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];
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

	// Check the database
	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT * FROM `users` WHERE user = ? AND password = ?;" );
	mysqli_stmt_bind_param( $stmt, 'ss', $user, $pass );
	mysqli_stmt_execute( $stmt );
	$result = mysqli_stmt_get_result( $stmt ) or die( '<pre>' . mysqli_stmt_error( $stmt ) . '</pre>' );

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
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Track bad login count for the lockout check above
		$fail_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], 'UPDATE users SET failed_login = COALESCE(failed_login, 0) + 1 WHERE user = ? LIMIT 1;' );
		mysqli_stmt_bind_param( $fail_stmt, 's', $user );
		mysqli_stmt_execute( $fail_stmt );
		mysqli_stmt_close( $fail_stmt );
	}
	mysqli_stmt_close( $stmt );

	// Set the last login time
	$time_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], 'UPDATE users SET last_login = NOW() WHERE user = ? LIMIT 1;' );
	mysqli_stmt_bind_param( $time_stmt, 's', $user );
	mysqli_stmt_execute( $time_stmt );
	mysqli_stmt_close( $time_stmt );

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
