<?php

if( isset( $_GET[ 'Login' ] ) && isset( $_GET[ 'username' ] ) && isset( $_GET[ 'password' ] ) ) {
	$user = $_GET[ 'username' ];
	$pass = md5( $_GET[ 'password' ] );

	$total_failed_login = 3;
	$lockout_time       = 15 * 60;
	$account_locked     = false;

	// Keep failed-attempt accounting with the account so a new session cannot
	// bypass the lockout.
	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$account = $data->fetch();

	if( $account && $account[ 'failed_login' ] >= $total_failed_login ) {
		$last_attempt = strtotime( $account[ 'last_login' ] );
		$account_locked = ( $last_attempt + $lockout_time ) > time();
	}

	$data = $db->prepare( 'SELECT user, avatar FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( $row && !$account_locked ) {
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		$data = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Do not extend an active lockout on every request. For an existing,
		// unlocked account, record this failure and begin a new lockout window.
		if( $account && !$account_locked ) {
			$data = $db->prepare( 'UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
			$data->bindParam( ':user', $user, PDO::PARAM_STR );
			$data->execute();
		}

		// Login failed
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}
}

?>
