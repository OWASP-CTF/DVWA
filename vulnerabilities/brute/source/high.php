<?php

if( isset( $_REQUEST[ 'Login' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );

	$user = is_string( $_REQUEST[ 'username' ] ?? null ) ? $_REQUEST[ 'username' ] : '';
	$pass = is_string( $_REQUEST[ 'password' ] ?? null ) ? $_REQUEST[ 'password' ] : '';
	$pass = md5( $pass );

	$total_failed_login = 3;
	$lockout_time       = 60; // seconds
	$account_locked     = false;

	// Serialize the complete read/check/update decision on the account row.
	// Without the row lock, parallel requests can all observe the same stale
	// counter before any increment is visible and exceed the failure threshold.
	$db->beginTransaction();
	$state = $db->prepare( 'SELECT * FROM users WHERE user = (:user) LIMIT 1 FOR UPDATE;' );
	$state->bindParam( ':user', $user, PDO::PARAM_STR );
	$state->execute();
	$login_state = $state->fetch();
	$account_exists = is_array( $login_state );

	if( $account_exists && (int) $login_state[ 'failed_login' ] >= $total_failed_login ) {
		$last_attempt = strtotime( $login_state[ 'last_login' ] );
		if( $last_attempt !== false && time() < $last_attempt + $lockout_time ) {
			$account_locked = true;
		}
		else {
			$login_state[ 'failed_login' ] = 0;
		}
	}

	$password_matches = $account_exists
		&& hash_equals( (string) $login_state[ 'password' ], $pass );

	if( $password_matches && !$account_locked ) {
		$avatar = htmlspecialchars( (string) $login_state[ 'avatar' ], ENT_QUOTES, 'UTF-8' );
		$safe_user = htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' );
		$html .= "<p>Welcome to the password protected area {$safe_user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		$reset = $db->prepare( 'UPDATE users SET failed_login = 0, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
		$reset->bindParam( ':user', $user, PDO::PARAM_STR );
		$reset->execute();
	}
	else {
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Do not extend an active lockout on rejected attempts: an attacker must
		// not be able to keep a victim locked out indefinitely. Unknown users get
		// the same response and delay but have no database row to mutate.
		if( $account_exists && !$account_locked ) {
			$failure = $db->prepare( 'UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
			$failure->bindParam( ':user', $user, PDO::PARAM_STR );
			$failure->execute();
		}
	}

	$db->commit();

	if( !$password_matches || $account_locked ) {
		// Equal deterministic work avoids username/status timing distinctions.
		sleep( 2 );
	}
}

generateSessionToken();

?>
