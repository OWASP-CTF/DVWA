<?php

if( isset( $_POST[ 'Login' ] ) && isset ($_POST['username']) && isset ($_POST['password']) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// A fixed two second sleep() was the only brake here. It costs an attacker
	// nothing once requests are sent in parallel.

	// Get input
	$user       = stripslashes( $_POST[ 'username' ] );
	$plain_pass = stripslashes( $_POST[ 'password' ] );

	// Lockout policy
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	// Has this account been locked out?
	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( ( $data->rowCount() == 1 ) && ( $row[ 'failed_login' ] >= $total_failed_login ) )  {
		$last_login = strtotime( $row[ 'last_login' ] );
		$timeout    = $last_login + ($lockout_time * 60);

		if( time() < $timeout ) {
			$account_locked = true;
		}
	}

	// Check the credentials. The row is found by name and the password is
	// verified in PHP, so the stored format can be a salted hash.
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR);
	$data->execute();
	$row = $data->fetch();

	// Verify against a dummy hash for an unknown account, so the work done is
	// the same either way and the response time does not say whether the
	// account exists.
	$stored         = ( $row !== false ) ? $row[ 'password' ] : dvwaDummyPasswordHash();
	$credentials_ok = dvwaPasswordVerify( $plain_pass, $stored ) && ( $row !== false );

	if( $credentials_ok && ( $account_locked == false ) ) {
		// Login successful
		$avatar    = $row[ 'avatar' ];
		$safe_user = htmlspecialchars( $user, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

		$html .= "<p>Welcome to the password protected area <em>{$safe_user}</em></p>";
		$html .= "<img src=\"" . htmlspecialchars( $avatar, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ) . "\" />";

		// Record the authentication outcome (A09:2025).
		dvwaSecurityLog( 'brute.login.success', array( 'account' => $user ) );

		// Upgrade a legacy MD5 row while we hold a known good plaintext.
		if( dvwaPasswordNeedsRehash( $row[ 'password' ] ) ) {
			dvwaPasswordStore( $user, $plain_pass );
		}

		// Reset bad login count
		$data = $db->prepare( 'UPDATE users SET failed_login = "0" WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	} else {
		// Login failed. One message covers both "wrong password" and "locked",
		// so the response cannot be used to enumerate accounts.
		$html .= "<pre><br />Username and/or password incorrect.<br /><br/>Alternatively the account has been locked because of too many failed logins.<br />If this is the case, <em>please try again in {$lockout_time} minutes</em>.</pre>";

		dvwaSecurityLog( 'brute.login.failure', array( 'account' => $user, 'locked' => $account_locked ? 'yes' : 'no' ) );

		// Update bad login count
		$data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1) WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}

	// Set the last login time
	$data = $db->prepare( 'UPDATE users SET last_login = now() WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
}

// Generate Anti-CSRF token
generateSessionToken();

?>
