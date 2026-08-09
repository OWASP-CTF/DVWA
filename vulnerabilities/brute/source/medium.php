<?php

// Credentials are only submitted via POST to keep them out of URL history,
// proxy logs and Referer headers. The CSRF token prevents cross-origin replay.
if( isset( $_POST['Login'], $_POST['username'], $_POST['password'] ) ) {
	checkToken( $_REQUEST['user_token'] ?? '', $_SESSION['session_token'], 'index.php' );

	$user = stripslashes( $_POST['username'] );
	$pass = md5( stripslashes( $_POST['password'] ) );

	$total_failed_login = 3;
	$lockout_time       = 15; // minutes
	$account_locked     = false;

	// Lockout check — uses PDO to prevent SQL injection on the username itself
	$stmt = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = :user LIMIT 1;' );
	$stmt->bindParam( ':user', $user, PDO::PARAM_STR );
	$stmt->execute();
	$row = $stmt->fetch();

	if( $row && $row['failed_login'] >= $total_failed_login ) {
		$timeout = strtotime( $row['last_login'] ) + ( $lockout_time * 60 );
		if( time() < $timeout ) {
			$account_locked = true;
		}
	}

	// Credential check — parameterised so neither field is interpreted as SQL
	$stmt = $db->prepare( 'SELECT avatar FROM users WHERE user = :user AND password = :password LIMIT 1;' );
	$stmt->bindParam( ':user',     $user, PDO::PARAM_STR );
	$stmt->bindParam( ':password', $pass, PDO::PARAM_STR );
	$stmt->execute();
	$row = $stmt->fetch();

	if( $row && !$account_locked ) {
		$html .= '<p>Welcome to the password protected area '
		       . htmlspecialchars( $user, ENT_QUOTES, 'UTF-8' ) . '</p>';
		$html .= '<img src="' . htmlspecialchars( $row['avatar'], ENT_QUOTES, 'UTF-8' ) . '" />';

		// Reset failure counter on successful login
		$stmt = $db->prepare( 'UPDATE users SET failed_login = 0 WHERE user = :user LIMIT 1;' );
		$stmt->bindParam( ':user', $user, PDO::PARAM_STR );
		$stmt->execute();
	} else {
		// Fixed delay prevents automated guessing at speed
		sleep( 2 );

		$html .= "<pre><br />Username and/or password incorrect.<br /><br/>"
		       . "Alternative, the account has been locked because of too many failed logins.<br />"
		       . "If this is the case, <em>please try again in {$lockout_time} minutes</em>.</pre>";

		// Increment counter and record the timestamp of the latest failed attempt
		$stmt = $db->prepare( 'UPDATE users SET failed_login = failed_login + 1, last_login = NOW() WHERE user = :user LIMIT 1;' );
		$stmt->bindParam( ':user', $user, PDO::PARAM_STR );
		$stmt->execute();
	}
}

generateSessionToken();

?>