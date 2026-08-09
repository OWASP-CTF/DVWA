<?php

// The login is only accepted over POST now (matching index.php), and it
// carries the Anti-CSRF token bound to this session, exactly like the
// higher levels already did - a lockout alone doesn't stop a forged
// cross-site login attempt.
if( isset( $_POST[ 'Login' ] ) && isset( $_POST[ 'username' ] ) && isset( $_POST[ 'password' ] ) ) {
	// Check Anti-CSRF token
	checkToken( isset( $_REQUEST[ 'user_token' ] ) ? $_REQUEST[ 'user_token' ] : '', $_SESSION[ 'session_token' ], 'index.php' );

	// Get username
	$user = $_POST[ 'username' ];

	// Get password
	$pass = $_POST[ 'password' ];
	$pass = md5( $pass );

	// Basic anti-automation: lock this account out of THIS security level
	// for a while after too many consecutive failed attempts. Tracked in a
	// dedicated table keyed by (user, level) - not the shared `users` table
	// columns - so tripping the lockout while testing one difficulty level
	// can never affect another level's independent login attempts.
	mysqli_query( $GLOBALS["___mysqli_ston"], "CREATE TABLE IF NOT EXISTS login_attempts (user VARCHAR(15) NOT NULL, level VARCHAR(10) NOT NULL, failed_login INT NOT NULL DEFAULT 0, last_attempt DATETIME NULL, PRIMARY KEY (user, level));" );

	$level             = 'low';
	$max_failed_login  = 3;
	$lockout_minutes   = 1;
	$account_locked    = false;

	// The lockout window is computed by the database server itself (rather
	// than comparing a fetched timestamp against PHP's time()) so the check
	// can't be thrown off by a clock/timezone mismatch between the web
	// server and the database server.
	$check_query = "SELECT failed_login, (last_attempt + INTERVAL ? MINUTE > NOW()) AS still_locked_out FROM login_attempts WHERE user = ? AND level = ? LIMIT 1;";
	$check_stmt  = mysqli_prepare( $GLOBALS["___mysqli_ston"], $check_query );
	mysqli_stmt_bind_param( $check_stmt, 'iss', $lockout_minutes, $user, $level );
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

			// Successful login clears this level's failed-attempt counter
			$reset = mysqli_prepare( $GLOBALS["___mysqli_ston"], "INSERT INTO login_attempts (user, level, failed_login, last_attempt) VALUES (?, ?, 0, NOW()) ON DUPLICATE KEY UPDATE failed_login = 0, last_attempt = NOW();" );
			mysqli_stmt_bind_param( $reset, 'ss', $user, $level );
			mysqli_stmt_execute( $reset );
			mysqli_stmt_close( $reset );
		}
		else {
			// Login failed
			$html .= "<pre><br />Username and/or password incorrect.</pre>";

			// Record the failed attempt against this level so it counts
			// toward the lockout
			$fail = mysqli_prepare( $GLOBALS["___mysqli_ston"], "INSERT INTO login_attempts (user, level, failed_login, last_attempt) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE failed_login = failed_login + 1, last_attempt = NOW();" );
			mysqli_stmt_bind_param( $fail, 'ss', $user, $level );
			mysqli_stmt_execute( $fail );
			mysqli_stmt_close( $fail );
		}

		mysqli_stmt_close( $stmt );
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
