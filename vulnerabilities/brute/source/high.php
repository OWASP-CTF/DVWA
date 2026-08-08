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

	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	// Check the server-side failure count for this account.
	$data = $db->prepare( 'SELECT password, avatar, failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch( PDO::FETCH_ASSOC );

	if( $row && (int) $row[ 'failed_login' ] >= $total_failed_login ) {
		$last_login = strtotime( $row[ 'last_login' ] );
		$account_locked = $last_login !== false && time() < $last_login + ( $lockout_time * 60 );
	}

	if( $row && !$account_locked && hash_equals( $row[ 'password' ], $pass ) ) {
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// A successful login starts with a clean failure count.
		$data = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Login failed
		sleep( rand( 0, 3 ) );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		if( $row && !$account_locked ) {
			// An expired lockout begins a new attempt window.
			$failed_login = (int) $row[ 'failed_login' ] >= $total_failed_login ? 1 : (int) $row[ 'failed_login' ] + 1;
			$data = $db->prepare( 'UPDATE users SET failed_login = (:failed_login), last_login = now() WHERE user = (:user) LIMIT 1;' );
			$data->bindParam( ':failed_login', $failed_login, PDO::PARAM_INT );
			$data->bindParam( ':user', $user, PDO::PARAM_STR );
			$data->execute();
		}
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
