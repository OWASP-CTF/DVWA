<?php

$change = false;
$request_type = "html";
$return_message = "Request Failed";
$pass_curr_raw = '';

if ($_SERVER['REQUEST_METHOD'] == "POST" && array_key_exists ("CONTENT_TYPE", $_SERVER) && $_SERVER['CONTENT_TYPE'] == "application/json") {
	$data = json_decode(file_get_contents('php://input'), true);
	$request_type = "json";
	if (array_key_exists("HTTP_USER_TOKEN", $_SERVER) &&
		array_key_exists("password_new", $data) &&
		array_key_exists("password_conf", $data) &&
		array_key_exists("Change", $data)) {
		$token = $_SERVER['HTTP_USER_TOKEN'];
		$pass_new = $data["password_new"];
		$pass_conf = $data["password_conf"];
		$pass_curr_raw = array_key_exists("password_current", $data) ? $data["password_current"] : '';
		$change = true;
	}
} else {
	if (array_key_exists("user_token", $_REQUEST) &&
		array_key_exists("password_new", $_REQUEST) &&
		array_key_exists("password_conf", $_REQUEST) &&
		array_key_exists("Change", $_REQUEST)) {
		$token = $_REQUEST["user_token"];
		$pass_new = $_REQUEST["password_new"];
		$pass_conf = $_REQUEST["password_conf"];
		$pass_curr_raw = array_key_exists("password_current", $_REQUEST) ? $_REQUEST["password_current"] : '';
		$change = true;
	}
}

if ($change) {
	// Check Anti-CSRF token
	checkToken( $token, $_SESSION[ 'session_token' ], 'index.php' );

	// A valid Anti-CSRF token only proves the request came from a page on
	// this site - it does not prove the account holder actually asked for
	// the change. Anything able to read the token (a same-site XSS bug, a
	// token that simply leaked, a shoulder-surfed page source, ...) can
	// replay it. Requiring the current password too - exactly as the
	// impossible level does - means a forged/replayed request still can't
	// succeed without something an off-site attacker never has access to.
	$pass_curr = stripslashes( (string) $pass_curr_raw );
	$pass_curr = mysqli_real_escape_string( $GLOBALS["___mysqli_ston"], $pass_curr );
	$pass_curr = md5( $pass_curr );

	$current_user = dvwaCurrentUser();
	$check = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$check->bindParam( ':user', $current_user, PDO::PARAM_STR );
	$check->bindParam( ':password', $pass_curr, PDO::PARAM_STR );
	$check->execute();
	$current_password_ok = ( $check->rowCount() == 1 );

	if( !$current_password_ok ) {
		// Wrong (or missing) current password - refuse the change outright,
		// regardless of whether the new passwords matched.
		$return_message = "Current password is incorrect.";
	}
	// Do the passwords match?
	else if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = mysqli_real_escape_string ($GLOBALS["___mysqli_ston"], $pass_new);
		$pass_new = md5( $pass_new );

		// Update the database
		$insert = "UPDATE `users` SET password = '" . $pass_new . "' WHERE user = '" . $current_user . "';";
		$result = mysqli_query($GLOBALS["___mysqli_ston"],  $insert );

		// Feedback for the user
		$return_message = "Password Changed.";
	}
	else {
		// Issue with passwords matching
		$return_message = "Passwords did not match.";
	}

	mysqli_close($GLOBALS["___mysqli_ston"]);

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
