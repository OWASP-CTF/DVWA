<?php

if( isset( $_REQUEST[ 'Login' ] ) ) {
	// Get username / password (accept GET or POST, per the level's form)
	$user = $_REQUEST[ 'username' ] ?? '';
	$pass = $_REQUEST[ 'password' ] ?? '';
	$pass = md5( $pass );

	// Brute-force protection settings (mirrors impossible.php)
	$total_failed_login = 3;
	$lockout_time        = 15; // minutes
	$account_locked       = false;

	// Check whether this account is currently locked out
	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( ( $data->rowCount() == 1 ) && ( $row[ 'failed_login' ] >= $total_failed_login ) ) {
		// Work out whether enough time has passed since the last attempt
		$last_login = strtotime( $row[ 'last_login' ] );
		$timeout    = $last_login + ( $lockout_time * 60 );
		$timenow    = time();

		if( $timenow < $timeout ) {
			$account_locked = true;
		}
	}

	// Check the database using a parameterised query (defeats the SQLi bypass)
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	// Constant delay applied on every outcome (success, failure or lockout),
	// so response timing can't be used to enumerate accounts or guess credentials.
	sleep( 2 );

	if( ( $data->rowCount() == 1 ) && ( $account_locked == false ) ) {
		// Get users details
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// Reset the failed-login counter on a successful login
		$data = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Track the failed attempt against this account
		$data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}

	// Record the time of this attempt
	$data = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
}

?>
