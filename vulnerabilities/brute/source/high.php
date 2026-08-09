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

	// Default values - a randomised short sleep on failure only slows a brute force attempt
	// down, it doesn't stop one. Add a real lockout after repeated failures.
	$total_failed_login = 3;
	$lockout_time        = 15;
	$account_locked       = false;

	$lockQuery  = "SELECT failed_login, last_login FROM users WHERE user = '$user' LIMIT 1;";
	$lockResult = mysqli_query($GLOBALS["___mysqli_ston"], $lockQuery);
	if( $lockResult && ( $lockRow = mysqli_fetch_assoc( $lockResult ) ) && ( $lockRow[ 'failed_login' ] >= $total_failed_login ) ) {
		$last_login = strtotime( $lockRow[ 'last_login' ] );
		$timeout    = $last_login + ($lockout_time * 60);
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

		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = 0 WHERE user = '$user' LIMIT 1;");
	}
	else {
		// Login failed
		sleep( rand( 0, 3 ) );
		$html .= "<pre><br />Username and/or password incorrect.<br /><br/>Alternatively, the account has been locked because of too many failed logins. If this is the case, please try again in {$lockout_time} minutes.</pre>";

		mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET failed_login = failed_login + 1 WHERE user = '$user' LIMIT 1;");
	}

	mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE users SET last_login = now() WHERE user = '$user' LIMIT 1;");

	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
