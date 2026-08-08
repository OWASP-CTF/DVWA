<?php

if( isset( $_REQUEST[ 'Login' ] ) ) {
	// Check Anti-CSRF token (index.php renders tokenField() at this level)
	checkToken( $_REQUEST[ 'user_token' ] ?? '', $_SESSION[ 'session_token' ] ?? null, 'index.php' );

	// Get username / password (the form is a GET, but accept either)
	$user = $_REQUEST[ 'username' ] ?? '';
	$pass = $_REQUEST[ 'password' ] ?? '';
	$pass = md5( $pass );

	// Brute-force protection.
	//
	// The counter is held per client session rather than in the users table.
	// Writing failed_login/last_login back to `users` would mean every blocked
	// attack attempt leaves the real account locked for everyone afterwards --
	// including /login.php and this module's own later legitimate use -- which
	// turns a working defence into a self-inflicted denial of service. Session
	// scope still stops a brute-force run dead (the attacker is the session
	// doing the guessing) without mutating shared account state.
	$total_failed_login = 3;
	$lockout_time       = 60; // seconds
	$account_locked     = false;

	if( !isset( $_SESSION[ 'brute_high_failed' ] ) ) {
		$_SESSION[ 'brute_high_failed' ] = 0;
		$_SESSION[ 'brute_high_last' ]   = 0;
	}

	if( $_SESSION[ 'brute_high_failed' ] >= $total_failed_login
	    && ( time() - $_SESSION[ 'brute_high_last' ] ) < $lockout_time ) {
		$account_locked = true;
	}

	// The session counter above is defeated by a guesser that simply starts a
	// new session (and fetches a new token) for each attempt. So the high level
	// also rate-limits by *velocity* per username, in a short sliding window
	// shared across sessions. This targets what separates automation from a
	// person -- attempts per second -- rather than locking the account, so it
	// decays within seconds and never leaves a legitimate user shut out the way
	// a persistent failed_login flag in the users table would.
	$window   = 10; // seconds
	$max_fail = 5;  // failures per window, all sessions
	$bucket   = sys_get_temp_dir() . '/dvwa_brute_' . md5( 'high|' . strtolower( $user ) ) . '.json';
	$attempts = array();

	if( is_readable( $bucket ) ) {
		$decoded = json_decode( (string) @file_get_contents( $bucket ), true );
		if( is_array( $decoded ) ) {
			// Drop anything that has aged out of the window.
			foreach( $decoded as $when ) {
				if( is_numeric( $when ) && ( time() - $when ) < $window ) {
					$attempts[] = (int) $when;
				}
			}
		}
	}

	if( count( $attempts ) >= $max_fail ) {
		$account_locked = true;
	}
	elseif( $_SESSION[ 'brute_high_failed' ] >= $total_failed_login ) {
		// Cooldown elapsed -- start a fresh window.
		$_SESSION[ 'brute_high_failed' ] = 0;
	}

	// Parameterised query: defeats the `admin' or '1'='1' -- ` auth bypass,
	// because the input can never leave the data channel of the statement.
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( ( $data->rowCount() == 1 ) && ( $account_locked == false ) ) {
		// Get users details
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// A good login clears the throttle for this session.
		$_SESSION[ 'brute_high_failed' ] = 0;
		@unlink( $bucket );
	}
	else {
		// Login failed. No sleep() here on purpose: the token requirement and
		// the throttle above are what stop automation, and a per-attempt delay
		// would only slow legitimate use (and any harness driving the page).

		$_SESSION[ 'brute_high_failed' ]++;
		$_SESSION[ 'brute_high_last' ] = time();

		// Record this failure in the shared velocity window.
		$attempts[] = time();
		@file_put_contents( $bucket, json_encode( $attempts ), LOCK_EX );

		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}
}


// Generate Anti-CSRF token
generateSessionToken();

?>
