<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Sanitise username input
	$user = $_GET[ 'username' ];
	$user = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $user ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));

	// Sanitise password input
	$pass = $_GET[ 'password' ];
	$pass = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"],  $pass ) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
	$pass = md5( $pass );

	// Account lockout check (CWE-307)
	$total_failed_login = 3;
	$lockout_time       = 15; // minutes
	$account_locked     = false;

	$lockout_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT failed_login, last_login FROM `users` WHERE user = '$user' LIMIT 1;");
	if( $lockout_result && mysqli_num_rows( $lockout_result ) == 1 ) {
		$lockout_row = mysqli_fetch_assoc( $lockout_result );
		if( $lockout_row['failed_login'] >= $total_failed_login ) {
			$timeout = strtotime( $lockout_row['last_login'] ) + ( $lockout_time * 60 );
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
		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `users` SET failed_login = 0 WHERE user = '$user' LIMIT 1;");
	}
	else {
		// Login failed
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Increment failed login counter
		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `users` SET failed_login = (failed_login + 1), last_login = now() WHERE user = '$user' LIMIT 1;");

		if( $account_locked ) {
			$html .= "<pre>Account locked due to too many failed logins. Try again in {$lockout_time} minutes.</pre>";
		}
	}

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
