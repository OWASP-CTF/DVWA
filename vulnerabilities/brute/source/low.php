<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];
	$pass = md5( $pass );

	// Default values - no rate limiting at all previously meant a password could be brute
	// forced with unlimited attempts and no delay.
	$total_failed_login = 3;
	$lockout_time        = 15;
	$account_locked       = false;

	// Check to see if the user has been locked out.
	$lockCheck = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT failed_login, last_login FROM users WHERE user = ? LIMIT 1;");
	mysqli_stmt_bind_param($lockCheck, 's', $user);
	mysqli_stmt_execute($lockCheck);
	$lockResult = mysqli_stmt_get_result($lockCheck);
	if( $lockResult && ( $lockRow = mysqli_fetch_assoc( $lockResult ) ) && ( $lockRow[ 'failed_login' ] >= $total_failed_login ) ) {
		$last_login = strtotime( $lockRow[ 'last_login' ] );
		$timeout    = $last_login + ($lockout_time * 60);
		if( time() < $timeout ) {
			$account_locked = true;
		}
	}

	// Check the database - $user was previously concatenated directly into the query string.
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT * FROM `users` WHERE user = ? AND password = ?;");
	mysqli_stmt_bind_param($stmt, 'ss', $user, $pass);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if( $result && mysqli_num_rows( $result ) == 1 && !$account_locked ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// Reset bad login count
		$reset = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = 0 WHERE user = ? LIMIT 1;");
		mysqli_stmt_bind_param($reset, 's', $user);
		mysqli_stmt_execute($reset);
	}
	else {
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.<br /><br/>Alternatively, the account has been locked because of too many failed logins. If this is the case, please try again in {$lockout_time} minutes.</pre>";

		// Update bad login count
		$incr = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = failed_login + 1 WHERE user = ? LIMIT 1;");
		mysqli_stmt_bind_param($incr, 's', $user);
		mysqli_stmt_execute($incr);
	}

	// Set the last login time
	$touch = mysqli_prepare($GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = now() WHERE user = ? LIMIT 1;");
	mysqli_stmt_bind_param($touch, 's', $user);
	mysqli_stmt_execute($touch);

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
