<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Get username and password
	$user = $_GET[ 'username' ];
	$pass = $_GET[ 'password' ];

	// Check for account lockout
	$lockout_query = "SELECT failed_login_attempts, locked_until FROM users WHERE user = ?";
	$lockout_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $lockout_query);
	if ($lockout_stmt) {
		mysqli_stmt_bind_param($lockout_stmt, "s", $user);
		mysqli_stmt_execute($lockout_stmt);
		$lockout_result = mysqli_stmt_get_result($lockout_stmt);
		if ($lockout_result && mysqli_num_rows($lockout_result) == 1) {
			$lockout_info = mysqli_fetch_assoc($lockout_result);
			$failed_attempts = intval($lockout_info['failed_login_attempts']);
			$locked_until = $lockout_info['locked_until'];
			
			// Check if account is locked
			if ($locked_until && strtotime($locked_until) > time()) {
				$html .= "<pre><br />Account temporarily locked. Try again later.</pre>";
				mysqli_stmt_close($lockout_stmt);
				mysqli_close($GLOBALS["___mysqli_ston"]);
				generateSessionToken();
				return;
			}
			
			// Reset failed attempts if locked_until has passed
			if ($locked_until && strtotime($locked_until) <= time() && $failed_attempts >= 5) {
				$reset_query = "UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE user = ?";
				$reset_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $reset_query);
				if ($reset_stmt) {
					mysqli_stmt_bind_param($reset_stmt, "s", $user);
					mysqli_stmt_execute($reset_stmt);
					mysqli_stmt_close($reset_stmt);
				}
			}
		}
		mysqli_stmt_close($lockout_stmt);
	}

	// Check the database using a prepared statement
	$query  = "SELECT * FROM `users` WHERE user = ? AND password = MD5(?)";
	$stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
	if (!$stmt) {
		$html .= "<pre><br />Database error.</pre>";
		generateSessionToken();
		return;
	}
	mysqli_stmt_bind_param($stmt, "ss", $user, $pass);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if( $result && mysqli_num_rows( $result ) == 1 ) {
		// Get users details
		$row    = mysqli_fetch_assoc( $result );
		$avatar = $row["avatar"];

		// Login successful - reset failed attempts
		$reset_query = "UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE user = ?";
		$reset_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $reset_query);
		if ($reset_stmt) {
			mysqli_stmt_bind_param($reset_stmt, "s", $user);
			mysqli_stmt_execute($reset_stmt);
			mysqli_stmt_close($reset_stmt);
		}

		// Login successful
		$html .= "<p>Welcome to the password protected area " . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . "</p>";
		$html .= "<img src=\"" . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') . "\" />";
	}
	else {
		// Login failed - increment failed attempts
		$increment_query = "UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE user = ?";
		$increment_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $increment_query);
		if ($increment_stmt) {
			mysqli_stmt_bind_param($increment_stmt, "s", $user);
			mysqli_stmt_execute($increment_stmt);
			
			// Check if should lock account (5 failed attempts)
			if ($failed_attempts + 1 >= 5) {
				$lock_query = "UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE user = ?";
				$lock_stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $lock_query);
				if ($lock_stmt) {
					mysqli_stmt_bind_param($lock_stmt, "s", $user);
					mysqli_stmt_execute($lock_stmt);
					mysqli_stmt_close($lock_stmt);
				}
			}
			mysqli_stmt_close($increment_stmt);
		}
		
		// Login failed - random delay to slow brute force
		sleep( rand( 0, 3 ) );
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	mysqli_stmt_close($stmt);
	mysqli_close($GLOBALS["___mysqli_ston"]);
}

// Generate Anti-CSRF token
generateSessionToken();

?>
