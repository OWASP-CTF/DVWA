<?php

if( isset( $_POST[ 'Login' ] ) && isset( $_POST[ 'username' ] ) && isset( $_POST[ 'password' ] ) ) {
	$user = is_string( $_POST[ 'username' ] ) ? $_POST[ 'username' ] : '';
	$pass = is_string( $_POST[ 'password' ] ) ? md5( $_POST[ 'password' ] ) : '';

	$total_failed_login = 5;
	$lockout_time       = 15;
	$account_locked     = false;
	$user_row           = false;

	// Look up lockout state without exposing whether the username exists.
	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$user_row = $data->fetch();

	if( $user_row ) {
		$last_login = strtotime( $user_row[ 'last_login' ] );
		if( (int) $user_row[ 'failed_login' ] >= $total_failed_login && $last_login !== false ) {
			$timeout = $last_login + ( $lockout_time * 60 );
			$account_locked = time() < $timeout;
		}
	}

	$data = $db->prepare( 'SELECT avatar FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( $row && !$account_locked ) {
		$avatar = htmlspecialchars( $row[ 'avatar' ], ENT_QUOTES, 'UTF-8' );
		$safe_user = htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' );

		$html .= "<p>Welcome to the password protected area {$safe_user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		$data = $db->prepare( 'UPDATE users SET failed_login = 0, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Count failed attempts for known users, but do not extend an active lockout window.
		if( $user_row && !$account_locked ) {
			$data = $db->prepare( 'UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
			$data->bindParam( ':user', $user, PDO::PARAM_STR );
			$data->execute();
		}
	}
}

?>
