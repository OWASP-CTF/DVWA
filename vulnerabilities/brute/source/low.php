<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];
	$pass = md5( $pass );

	// Lockout configuration (CWE-307)
	$total_failed_login = 3;
	$lockout_time       = 15; // minutes
	$account_locked     = false;

	// Check if user is currently locked out
	$lockout_query  = "SELECT failed_login, last_login FROM `users` WHERE user = '$user' LIMIT 1;";
	$lockout_result = mysqli_query($GLOBALS["___mysqli_ston"], $lockout_query);
	if( $lockout_result && mysqli_num_rows( $lockout_result ) == 1 ) {
		$lockout_row = mysqli_fetch_assoc( $lockout_result );
		if( $lockout_row['failed_login'] >= $total_failed_login ) {
			$last_login = strtotime( $lockout_row['last_login'] );
			$timeout    = $last_login + ( $lockout_time * 60 );
			if( time() < $timeout ) {
				$account_locked = true;
			}
		}
	}

	// Check the database
	$query  = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

	if( $result && mysqli_num_rows( $result ) == 1 && !$account_locked ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// Reset failed login counter
		$reset_query = "UPDATE `users` SET failed_login = 0 WHERE user = '$user' LIMIT 1;";
		mysqli_query($GLOBALS["___mysqli_ston"], $reset_query);
	}
	else {
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Increment failed login counter and update last_login timestamp
		$update_query = "UPDATE `users` SET failed_login = (failed_login + 1), last_login = now() WHERE user = '$user' LIMIT 1;";
		mysqli_query($GLOBALS["___mysqli_ston"], $update_query);

		if( $account_locked ) {
			$html .= "<pre>This account has been locked due to too many failed logins. Please try again in {$lockout_time} minutes.</pre>";
		}
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
