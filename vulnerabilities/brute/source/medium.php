<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Sanitise username input
	$user = $_GET[ 'username' ];
	$user = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $user ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

	// Sanitise password input
	$pass = $_GET[ 'password' ];
	$pass = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
	$pass = md5( $pass );

	// Default values
	$total_failed_login = 3;
	$lockout_time        = 15;
	$account_locked       = false;

	// Check the database using a parameterised query (prevents SQLi login bypass)
	$check = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT failed_login, last_login FROM users WHERE user = ? LIMIT 1;" );
	mysqli_stmt_bind_param( $check, 's', $user );
	mysqli_stmt_execute( $check );
	$check_result = mysqli_stmt_get_result( $check );
	$check_row    = $check_result ? mysqli_fetch_assoc( $check_result ) : null;
	mysqli_stmt_close( $check );

	// Check to see if the user has been locked out
	if( $check_row && ( $check_row[ 'failed_login' ] >= $total_failed_login ) ) {
		$last_login = strtotime( $check_row[ 'last_login' ] );
		$timeout    = $last_login + ( $lockout_time * 60 );
		$timenow    = time();

		if( $timenow < $timeout ) {
			$account_locked = true;
		}
	}

	// Check the database
	$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT * FROM `users` WHERE user = ? AND password = ?;" );
	mysqli_stmt_bind_param( $stmt, 'ss', $user, $pass );
	mysqli_stmt_execute( $stmt );
	$result = mysqli_stmt_get_result( $stmt );

	if( $result && mysqli_num_rows( $result ) == 1 && !$account_locked ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// Reset bad login count
		$reset = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = 0 WHERE user = ? LIMIT 1;" );
		mysqli_stmt_bind_param( $reset, 's', $user );
		mysqli_stmt_execute( $reset );
		mysqli_stmt_close( $reset );
	}
	else {
		// Login failed
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Update bad login count
		$update = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = failed_login + 1 WHERE user = ? LIMIT 1;" );
		mysqli_stmt_bind_param( $update, 's', $user );
		mysqli_stmt_execute( $update );
		mysqli_stmt_close( $update );
	}

	// Set the last login time
	$last = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = now() WHERE user = ? LIMIT 1;" );
	mysqli_stmt_bind_param( $last, 's', $user );
	mysqli_stmt_execute( $last );
	mysqli_stmt_close( $last );

	mysqli_stmt_close( $stmt );
	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
