<?php

if( isset( $_REQUEST[ 'Login' ] ) ) {
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );

	$user = is_string( $_REQUEST[ 'username' ] ?? null ) ? $_REQUEST[ 'username' ] : '';
	$pass = is_string( $_REQUEST[ 'password' ] ?? null ) ? $_REQUEST[ 'password' ] : '';
	$pass = md5( $pass );

	// A short credential list can contain the known demo password within three
	// guesses, so a three-failure threshold still lets the scripted attack
	// complete. Permit one failed guess per cooldown at the high level.
	$total_failed_login = 1;
	$lockout_time       = 60; // seconds
	$account_locked     = false;

	// Keep the failed-attempt state with the account, as the repository's
	// impossible implementation does. A session-only counter is bypassed by
	// rotating PHPSESSID, while an ad-hoc file bucket is not transactional with
	// the account record. The database increment below is atomic across sessions.
	$state = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$state->bindParam( ':user', $user, PDO::PARAM_STR );
	$state->execute();
	$login_state = $state->fetch();

	if( $state->rowCount() == 1 && (int) $login_state[ 'failed_login' ] >= $total_failed_login ) {
		$last_attempt = strtotime( $login_state[ 'last_login' ] );
		if( $last_attempt !== false && time() < $last_attempt + $lockout_time ) {
			$account_locked = true;
		}
		else {
			$reset = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = (:user) LIMIT 1;' );
			$reset->bindParam( ':user', $user, PDO::PARAM_STR );
			$reset->execute();
		}
	}

	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( $data->rowCount() == 1 && !$account_locked ) {
		$avatar = htmlspecialchars( (string) $row[ 'avatar' ], ENT_QUOTES, 'UTF-8' );
		$safe_user = htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' );
		$html .= "<p>Welcome to the password protected area {$safe_user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		$reset = $db->prepare( 'UPDATE users SET failed_login = 0, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
		$reset->bindParam( ':user', $user, PDO::PARAM_STR );
		$reset->execute();
	}
	else {
		// Give every failed guess a deterministic cost, including attempts made
		// while the account is locked, without disclosing whether the user exists.
		sleep( 2 );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		$failure = $db->prepare( 'UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
		$failure->bindParam( ':user', $user, PDO::PARAM_STR );
		$failure->execute();
	}
}

generateSessionToken();

?>
