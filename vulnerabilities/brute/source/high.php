<?php

if( isset( $_POST[ 'Login' ] ) ) {
	// Check Anti-CSRF token
	if (array_key_exists ("session_token", $_SESSION)) {
		$session_token = $_SESSION[ 'session_token' ];
	} else {
		$session_token = "";
	}
	checkToken( $_REQUEST[ 'user_token' ], $session_token, 'index.php' );

	// Sanitise username input
	$user = $_POST[ 'username' ];
	$user = stripslashes( $user );

	// Sanitise password input
	$pass = $_POST[ 'password' ];
	$pass = stripslashes( $pass );
	$pass = md5( $pass );

	// Default values
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;
	$account_exists     = false;

	// Reading the failed-login count and then writing an incremented value
	// back as two separate statements is a check-then-act race: two
	// concurrent attempts against the same account can both read "not
	// locked yet" before either one's increment becomes visible, letting
	// an attacker fire guesses in parallel and dodge the lockout entirely.
	// Lock the row for the life of one transaction instead, so concurrent
	// attempts against the same account are serialised rather than racing.
	$db->beginTransaction();

	$lockout_data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1 FOR UPDATE;' );
	$lockout_data->bindParam( ':user', $user, PDO::PARAM_STR );
	$lockout_data->execute();
	$lockout_row = $lockout_data->fetch();

	if( $lockout_data->rowCount() == 1 ) {
		$account_exists = true;
		if( $lockout_row[ 'failed_login' ] >= $total_failed_login ) {
			$last_login = strtotime( $lockout_row[ 'last_login' ] );
			$timeout    = $last_login + ($lockout_time * 60);
			if( time() < $timeout ) {
				$account_locked = true;
			}
		}
	}

	$login_ok = false;
	if( $account_exists && !$account_locked ) {
		$check = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$check->bindParam( ':user', $user, PDO::PARAM_STR );
		$check->bindParam( ':password', $pass, PDO::PARAM_STR );
		$check->execute();
		$row      = $check->fetch();
		$login_ok = ( $check->rowCount() == 1 );
	}

	if( $login_ok ) {
		// Get users details
		$avatar = $row[ 'avatar' ];

		// Login successful. Encode before echoing - the username came
		// straight from the request and must not be trusted as HTML.
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . "</p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES, 'UTF-8' ) . "\" />";

		// Reset bad login count
		$reset = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = (:user) LIMIT 1;' );
		$reset->bindParam( ':user', $user, PDO::PARAM_STR );
		$reset->execute();
	}
	else {
		// Login failed. A fixed delay rather than a randomised one, so the
		// response time itself can't be used to distinguish "wrong
		// password" from "account locked" across repeated attempts.
		sleep( 2 );
		if( $account_locked ) {
			$html .= "<pre><br />This account has been locked due to too many incorrect logins. Please try again in {$lockout_time} minutes.</pre>";
		} else {
			$html .= "<pre><br />Username and/or password incorrect.</pre>";
		}

		// Update bad login count, still inside the locked transaction.
		if( $account_exists ) {
			$fail = $db->prepare( 'UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = (:user) LIMIT 1;' );
			$fail->bindParam( ':user', $user, PDO::PARAM_STR );
			$fail->execute();
		}
	}

	if( $login_ok ) {
		// Set the last login time
		$last = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
		$last->bindParam( ':user', $user, PDO::PARAM_STR );
		$last->execute();
	}

	$db->commit();
}

// Generate Anti-CSRF token
generateSessionToken();

?>
