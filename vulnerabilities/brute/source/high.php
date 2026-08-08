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
	$lockout_time       = 10; // seconds; matches the shared sliding window below
	$account_locked     = false;

	if( !isset( $_SESSION[ 'brute_high_failed' ] ) ) {
		$_SESSION[ 'brute_high_failed' ] = 0;
		$_SESSION[ 'brute_high_last' ]   = 0;
	}

	if( $_SESSION[ 'brute_high_failed' ] >= $total_failed_login
	    && ( time() - $_SESSION[ 'brute_high_last' ] ) < $lockout_time ) {
		$account_locked = true;
	}
	elseif( $_SESSION[ 'brute_high_failed' ] >= $total_failed_login ) {
		// The cooldown elapsed before this attempt, so legitimate credentials
		// can be accepted immediately rather than being rejected one extra time.
		$_SESSION[ 'brute_high_failed' ] = 0;
	}

	// The session counter above is defeated by a guesser that simply starts a
	// new session (and fetches a new token) for each attempt. So the high level
	// also rate-limits by *velocity* per username, in a short sliding window
	// shared across sessions. This targets what separates automation from a
	// person -- attempts per second -- rather than locking the account, so it
	// decays within seconds and never leaves a legitimate user shut out the way
	// a persistent failed_login flag in the users table would.
	$window   = 10; // seconds
	$max_fail = $total_failed_login; // same three-failure policy across sessions
	$bucket   = sys_get_temp_dir() . '/dvwa_brute_' . md5( 'high|' . strtolower( $user ) ) . '.json';
	$attempts = array();
	$bucket_handle = @fopen( $bucket, 'c+' );
	$bucket_locked = false;

	// Hold one exclusive lock across the read/check/update sequence. Without
	// this, concurrent requests can all observe the same pre-limit count and
	// then overwrite one another's updates.
	if( $bucket_handle !== false && flock( $bucket_handle, LOCK_EX ) ) {
		$bucket_locked = true;
		rewind( $bucket_handle );
		$decoded = json_decode( (string) stream_get_contents( $bucket_handle ), true );
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
		if( $bucket_locked ) {
			ftruncate( $bucket_handle, 0 );
			fflush( $bucket_handle );
		}
	}
	else {
		// Add a deterministic cost to every failed guess. The original random
		// 0-3 second delay could be zero and therefore did not reliably slow an
		// online guesser; two seconds matches the minimum used by impossible.php.
		sleep( 2 );

		$_SESSION[ 'brute_high_failed' ]++;
		$_SESSION[ 'brute_high_last' ] = time();

		// Record this failure in the shared velocity window.
		$attempts[] = time();
		if( $bucket_locked ) {
			ftruncate( $bucket_handle, 0 );
			rewind( $bucket_handle );
			fwrite( $bucket_handle, json_encode( $attempts ) );
			fflush( $bucket_handle );
		}

		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	if( $bucket_handle !== false ) {
		if( $bucket_locked ) {
			flock( $bucket_handle, LOCK_UN );
		}
		fclose( $bucket_handle );
	}
}


// Generate Anti-CSRF token
generateSessionToken();

?>
