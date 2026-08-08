<?php

if( isset( $_GET[ 'Login' ] ) && isset( $_GET[ 'username' ] ) && isset( $_GET[ 'password' ] ) ) {
	$user               = $_GET[ 'username' ];
	$pass               = md5( $_GET[ 'password' ] );
	$total_failed_login = 3;
	$lockout_time       = 15 * 60;
	$connection         = $GLOBALS[ "___mysqli_ston" ];
	$login_successful   = false;

	// Lock the account row while checking and recording this attempt. This
	// prevents parallel requests from racing past the failure limit.
	mysqli_begin_transaction( $connection );
	$data = mysqli_prepare( $connection, 'SELECT password, avatar, failed_login, last_login FROM users WHERE user = ? LIMIT 1 FOR UPDATE;' );
	mysqli_stmt_bind_param( $data, 's', $user );
	mysqli_stmt_execute( $data );
	mysqli_stmt_bind_result( $data, $stored_password, $avatar, $failed_login, $last_login );
	$user_exists = mysqli_stmt_fetch( $data );
	mysqli_stmt_close( $data );

	if( $user_exists ) {
		$locked = $failed_login >= $total_failed_login && time() < ( strtotime( $last_login ) + $lockout_time );

		// Start a fresh accounting window once an old lockout has expired.
		if( $failed_login >= $total_failed_login && !$locked ) {
			$failed_login = 0;
		}

		if( !$locked && hash_equals( $stored_password, $pass ) ) {
			$data = mysqli_prepare( $connection, 'UPDATE users SET failed_login = 0 WHERE user = ? LIMIT 1;' );
			mysqli_stmt_bind_param( $data, 's', $user );
			mysqli_stmt_execute( $data );
			mysqli_stmt_close( $data );
			$login_successful = true;
		}
		elseif( !$locked ) {
			$failed_login++;
			$data = mysqli_prepare( $connection, 'UPDATE users SET failed_login = ?, last_login = NOW() WHERE user = ? LIMIT 1;' );
			mysqli_stmt_bind_param( $data, 'is', $failed_login, $user );
			mysqli_stmt_execute( $data );
			mysqli_stmt_close( $data );
		}
	}

	mysqli_commit( $connection );

	if( $login_successful ) {
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";
	}
	else {
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	((is_null($___mysqli_res = mysqli_close($connection))) ? false : $___mysqli_res);
}

?>
