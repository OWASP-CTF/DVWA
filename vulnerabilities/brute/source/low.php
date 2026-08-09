<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];
	$pass = md5( $pass );

	// Basic anti-automation: lock an account out for a while after too many
	// failed attempts in a row, instead of allowing unlimited guesses.
	$max_failed_login = 3;
	$lockout_minutes  = 1;
	$account_locked   = false;

	// The lockout window is computed by the database server itself (rather
	// than comparing a fetched timestamp against PHP's time()) so the check
	// can't be thrown off by a clock/timezone mismatch between the web
	// server and the database server.
	$check_query = "SELECT failed_login, (last_login + INTERVAL ? MINUTE > NOW()) AS still_locked_out FROM users WHERE user = ? LIMIT 1;";
	$check_stmt  = mysqli_prepare( $GLOBALS["___mysqli_ston"], $check_query );
	mysqli_stmt_bind_param( $check_stmt, 'is', $lockout_minutes, $user );
	mysqli_stmt_execute( $check_stmt );
	$check_row = mysqli_fetch_assoc( mysqli_stmt_get_result( $check_stmt ) );
	mysqli_stmt_close( $check_stmt );

	if( $check_row && $check_row[ 'failed_login' ] >= $max_failed_login && $check_row[ 'still_locked_out' ] ) {
		$account_locked = true;
	}

	if( $account_locked ) {
		$html .= "<pre><br />This account is temporarily locked due to too many failed logins. Please try again in {$lockout_minutes} minute(s).</pre>";
	} else {
		// Check the database
		$query = "SELECT * FROM `users` WHERE user = ? AND password = ?;";
		$stmt  = mysqli_prepare( $GLOBALS["___mysqli_ston"], $query );
		mysqli_stmt_bind_param( $stmt, 'ss', $user, $pass );
		mysqli_stmt_execute( $stmt ) or die( '<pre>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '</pre>' );
		$result = mysqli_stmt_get_result( $stmt );

		if( $result && mysqli_num_rows( $result ) == 1 ) {
			// Get users details
			$row    = mysqli_fetch_assoc( $result );
			$avatar = $row["avatar"];

			// Login successful
			$html .= "<p>Welcome to the password protected area {$user}</p>";
			$html .= "<img src=\"{$avatar}\" />";

			// Successful login clears the failed-attempt counter
			$reset = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = 0, last_login = NOW() WHERE user = ?;" );
			mysqli_stmt_bind_param( $reset, 's', $user );
			mysqli_stmt_execute( $reset );
			mysqli_stmt_close( $reset );
		}
		else {
			// Login failed
			$html .= "<pre><br />Username and/or password incorrect.</pre>";

			// Record the failed attempt so it counts toward the lockout
			$fail = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = ?;" );
			mysqli_stmt_bind_param( $fail, 's', $user );
			mysqli_stmt_execute( $fail );
			mysqli_stmt_close( $fail );
		}

		mysqli_stmt_close( $stmt );
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
