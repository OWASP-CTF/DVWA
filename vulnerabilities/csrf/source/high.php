<?php

$change = false;
$request_type = "html";
$return_message = "Request Failed";

if ($_SERVER['REQUEST_METHOD'] == "POST" && array_key_exists ("CONTENT_TYPE", $_SERVER) && $_SERVER['CONTENT_TYPE'] == "application/json") {
	$data = json_decode(file_get_contents('php://input'), true);
	$request_type = "json";
	if (array_key_exists("HTTP_USER_TOKEN", $_SERVER) &&
		array_key_exists("password_current", $data) &&
		array_key_exists("password_new", $data) &&
		array_key_exists("password_conf", $data) &&
		array_key_exists("Change", $data)) {
		$token = $_SERVER['HTTP_USER_TOKEN'];
		$pass_curr = $data["password_current"];
		$pass_new = $data["password_new"];
		$pass_conf = $data["password_conf"];
		$change = true;
	}
} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
	// A state-changing request like this one must never be satisfiable over
	// GET - a plain link or <img> tag can trigger a GET from a victim's
	// browser with no script and no visible form involved at all, which is
	// the classic CSRF delivery vector this level is meant to resist.
	// $_REQUEST also merges in cookies, which are just as attacker-settable
	// as a query string; read strictly from the POST body instead.
	if (array_key_exists("user_token", $_POST) &&
		array_key_exists("password_current", $_POST) &&
		array_key_exists("password_new", $_POST) &&
		array_key_exists("password_conf", $_POST) &&
		array_key_exists("Change", $_POST)) {
		$token = $_POST["user_token"];
		$pass_curr = $_POST["password_current"];
		$pass_new = $_POST["password_new"];
		$pass_conf = $_POST["password_conf"];
		$change = true;
	}
}

if ($change) {
	// Check Anti-CSRF token. Compare in constant time, and never let a
	// missing/empty session token be satisfied by a missing/empty submitted
	// one - they must both be present and equal.
	$user_token    = ( isset( $token ) && is_string( $token ) ) ? $token : '';
	$session_token = ( isset( $_SESSION[ 'session_token' ] ) && is_string( $_SESSION[ 'session_token' ] ) ) ? $_SESSION[ 'session_token' ] : '';

	if ( $session_token === '' || !hash_equals( $session_token, $user_token ) ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( 'index.php' );
	}

	// A valid CSRF token only proves the request came from this app's own
	// form; it says nothing about whether whoever submitted it is really
	// the account holder rather than, say, a hijacked session cookie.
	// Require the current password too, matching the impossible level, so
	// a stolen token or cookie alone is not enough to change it.
	$current_user = dvwaCurrentUser();
	$pass_curr    = mysqli_real_escape_string( $GLOBALS["___mysqli_ston"], stripslashes( (string) $pass_curr ) );
	$pass_curr    = md5( $pass_curr );

	$current_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT password FROM users WHERE user = ? AND password = ? LIMIT 1;" );
	mysqli_stmt_bind_param( $current_stmt, 'ss', $current_user, $pass_curr );
	mysqli_stmt_execute( $current_stmt );
	mysqli_stmt_store_result( $current_stmt );
	$current_password_ok = ( mysqli_stmt_num_rows( $current_stmt ) == 1 );
	mysqli_stmt_close( $current_stmt );

	if ( !$current_password_ok ) {
		$return_message = "Current password incorrect.";
	}
	// Do the passwords match?
	elseif( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = mysqli_real_escape_string ($GLOBALS["___mysqli_ston"], $pass_new);
		$pass_new = md5( $pass_new );

		// Update the database
		$update_stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "UPDATE users SET password = ? WHERE user = ?;" );
		mysqli_stmt_bind_param( $update_stmt, 'ss', $pass_new, $current_user );
		mysqli_stmt_execute( $update_stmt );
		mysqli_stmt_close( $update_stmt );

		// Feedback for the user
		$return_message = "Password Changed.";
	}
	else {
		// Issue with passwords matching
		$return_message = "Passwords did not match.";
	}

	if ($request_type == "json") {
		generateSessionToken();
		header ("Content-Type: application/json");
		print json_encode (array("Message" =>$return_message));
		exit;
	} else {
		$html .= "<pre>" . $return_message . "</pre>";
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
