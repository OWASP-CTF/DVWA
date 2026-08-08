<?php

$change = false;
$request_type = "html";
$return_message = "Request Failed";

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
		$change = true;
	}
}

if ($change) {
	// The change has to be proved to come from the user, not from a page some
	// other site got them to load. Either the Anti-CSRF token bound to this
	// session, or the current password, is enough: an attacker forging the
	// request cross site can supply neither.
	$token_ok = isset( $_SESSION[ 'session_token' ] ) && isset( $token ) && is_string( $token ) &&
		hash_equals( (string)$_SESSION[ 'session_token' ], $token );

	$current_password_ok = false;
	if( !$token_ok && isset( $_REQUEST[ 'password_current' ] ) && is_string( $_REQUEST[ 'password_current' ] ) ) {
		$pass_curr = md5( mysqli_real_escape_string( $GLOBALS["___mysqli_ston"], stripslashes( $_REQUEST[ 'password_current' ] ) ) );

		$check = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$check_user = dvwaCurrentUser();
		$check->bindParam( ':user', $check_user, PDO::PARAM_STR );
		$check->bindParam( ':password', $pass_curr, PDO::PARAM_STR );
		$check->execute();
		$current_password_ok = ( $check->fetch() !== false );
	}

	if( !$token_ok && !$current_password_ok ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( 'index.php' );
	}

	// Do the passwords match?
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = stripslashes( $pass_new );
		$pass_new = mysqli_real_escape_string ($GLOBALS["___mysqli_ston"], $pass_new);
		$pass_new = md5( $pass_new );

		// Update the database
		$current_user = dvwaCurrentUser();
		$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
		$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
		$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
		$data->execute();

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
