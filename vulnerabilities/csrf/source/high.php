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
	if (array_key_exists("user_token", $_REQUEST) &&
		array_key_exists("password_current", $_REQUEST) &&
		array_key_exists("password_new", $_REQUEST) &&
		array_key_exists("password_conf", $_REQUEST) &&
		array_key_exists("Change", $_REQUEST)) {
		$token = $_REQUEST["user_token"];
		$pass_curr = $_REQUEST["password_current"];
		$pass_new = $_REQUEST["password_new"];
		$pass_conf = $_REQUEST["password_conf"];
		$change = true;
	}
}

if ($_SERVER['REQUEST_METHOD'] != "POST" && isset($_REQUEST['Change'])) {
	http_response_code(405);
	$return_message = "Method not supported.";
}

if ($change) {
	// Check Anti-CSRF token
	checkToken( $token, $_SESSION[ 'session_token' ], 'index.php' );

	// Sensitive credential changes require reauthentication as well as a
	// request token. This matches the impossible-level security property and
	// prevents a stolen browser session alone from changing the password.
	$pass_curr_hash = md5( is_string( $pass_curr ) ? $pass_curr : '' );
	$current_user = dvwaCurrentUser();
	$current = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$current->bindParam( ':user', $current_user, PDO::PARAM_STR );
	$current->bindParam( ':password', $pass_curr_hash, PDO::PARAM_STR );
	$current->execute();

	// Do the passwords match?
	if( $pass_new == $pass_conf && $current->rowCount() == 1 ) {
		// They do!
		$pass_new = mysqli_real_escape_string ($GLOBALS["___mysqli_ston"], $pass_new);
		$pass_new = md5( $pass_new );

		// Update the database
		$update = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
		$update->bindParam( ':password', $pass_new, PDO::PARAM_STR );
		$update->bindParam( ':user', $current_user, PDO::PARAM_STR );
		$update->execute();

		// Feedback for the user
		$return_message = "Password Changed.";
	}
	else {
		// Issue with passwords matching
		$return_message = "Passwords did not match or current password incorrect.";
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
