<?php

$change = false;
$request_type = "html";
$return_message = "Request Failed";
$pass_curr_raw = null;

if ($_SERVER['REQUEST_METHOD'] == "POST" && array_key_exists ("CONTENT_TYPE", $_SERVER) && $_SERVER['CONTENT_TYPE'] == "application/json") {
	$data = json_decode(file_get_contents('php://input'), true);
	$request_type = "json";
	if (is_array ($data) &&
		array_key_exists("HTTP_USER_TOKEN", $_SERVER) &&
		array_key_exists("password_new", $data) &&
		array_key_exists("password_conf", $data) &&
		array_key_exists("Change", $data)) {
		$token = $_SERVER['HTTP_USER_TOKEN'];
		$pass_new = $data["password_new"];
		$pass_conf = $data["password_conf"];
		if (array_key_exists("password_current", $data)) {
			$pass_curr_raw = $data["password_current"];
		}
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
		if (array_key_exists("password_current", $_REQUEST)) {
			$pass_curr_raw = $_REQUEST["password_current"];
		}
		$change = true;
	}
}

if ($change) {
	// An Anti-CSRF token on its own only proves the request came from a page on
	// this site; anything able to read the page, such as an injected script,
	// can read the token too and replay it. So the change also has to be proved
	// by knowledge of the current password, which lives only with the account
	// holder. This is the pair of checks the impossible level makes.
	$token_ok = isset( $_SESSION[ 'session_token' ] ) && isset( $token ) && is_string( $token ) &&
		hash_equals( (string)$_SESSION[ 'session_token' ], $token );

	$current_password_ok = false;
	if( isset( $pass_curr_raw ) && is_string( $pass_curr_raw ) ) {
		$pass_curr = md5( stripslashes( $pass_curr_raw ) );

		$check = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$check_user = dvwaCurrentUser();
		$check->bindParam( ':user', $check_user, PDO::PARAM_STR );
		$check->bindParam( ':password', $pass_curr, PDO::PARAM_STR );
		$check->execute();
		$current_password_ok = ( $check->fetch() !== false );
	}

	if( !$token_ok || !$current_password_ok ) {
		$return_message = "Either your current password is incorrect or the request could not be verified.";

		if ($request_type == "json") {
			generateSessionToken();
			header ("Content-Type: application/json");
			print json_encode (array("Message" =>$return_message));
			exit;
		}

		$html .= "<pre>" . $return_message . "</pre>";

		// Generate Anti-CSRF token
		generateSessionToken();
		return;
	}

	// Do the passwords match?
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = md5( stripslashes( $pass_new ) );

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
