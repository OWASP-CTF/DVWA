<?php

if( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' && isset( $_POST[ 'Change' ] ) ) {
	$user_token = isset( $_POST[ 'user_token' ] ) && is_string( $_POST[ 'user_token' ] )
		? $_POST[ 'user_token' ]
		: '';
	$session_token = isset( $_SESSION[ 'session_token' ] ) && is_string( $_SESSION[ 'session_token' ] )
		? $_SESSION[ 'session_token' ]
		: '';

	if( $session_token === '' || !hash_equals( $session_token, $user_token ) ) {
		dvwaMessagePush( 'CSRF token is incorrect' );
		dvwaRedirect( 'index.php' );
	}

	$pass_current = $_POST[ 'password_current' ] ?? null;
	$pass_new = $_POST[ 'password_new' ] ?? null;
	$pass_conf = $_POST[ 'password_conf' ] ?? null;

	if( csrfChangePassword( $pass_current, $pass_new, $pass_conf ) ) {
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		$html .= "<pre>Passwords did not match or current password incorrect.</pre>";
	}
}

generateSessionToken();

?>
