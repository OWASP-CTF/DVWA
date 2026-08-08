<?php

$change = false;
$request_type = "html";
$return_message = "Request Failed";

if( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
	$content_type = isset( $_SERVER[ 'CONTENT_TYPE' ] ) && is_string( $_SERVER[ 'CONTENT_TYPE' ] )
		? strtolower( trim( explode( ';', $_SERVER[ 'CONTENT_TYPE' ], 2 )[0] ) )
		: '';

	if( $content_type === 'application/json' ) {
		$data = json_decode( file_get_contents( 'php://input' ), true );
		$request_type = "json";

		if( is_array( $data ) && isset( $_SERVER[ 'HTTP_USER_TOKEN' ] ) &&
			array_key_exists( 'password_current', $data ) &&
			array_key_exists( 'password_new', $data ) &&
			array_key_exists( 'password_conf', $data ) &&
			array_key_exists( 'Change', $data ) ) {
			$token = $_SERVER[ 'HTTP_USER_TOKEN' ];
			$pass_current = $data[ 'password_current' ];
			$pass_new = $data[ 'password_new' ];
			$pass_conf = $data[ 'password_conf' ];
			$change = true;
		}
	}
	elseif( isset( $_POST[ 'user_token' ], $_POST[ 'password_current' ], $_POST[ 'password_new' ], $_POST[ 'password_conf' ], $_POST[ 'Change' ] ) ) {
		$token = $_POST[ 'user_token' ];
		$pass_current = $_POST[ 'password_current' ];
		$pass_new = $_POST[ 'password_new' ];
		$pass_conf = $_POST[ 'password_conf' ];
		$change = true;
	}
}

if( $change ) {
	$user_token = is_string( $token ) ? $token : '';
	$session_token = isset( $_SESSION[ 'session_token' ] ) && is_string( $_SESSION[ 'session_token' ] )
		? $_SESSION[ 'session_token' ]
		: '';

	if( $session_token === '' || !hash_equals( $session_token, $user_token ) ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( 'index.php' );
	}

	if( csrfChangePassword( $pass_current, $pass_new, $pass_conf ) ) {
		$return_message = "Password Changed.";
	}
	else {
		$return_message = "Passwords did not match or current password incorrect.";
	}

	if( $request_type === 'json' ) {
		generateSessionToken();
		header( "Content-Type: application/json" );
		print json_encode( array( "Message" => $return_message ) );
		exit;
	}

	$html .= "<pre>" . htmlspecialchars( $return_message, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . "</pre>";
}

generateSessionToken();

?>
