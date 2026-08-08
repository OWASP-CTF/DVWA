<?php

if( isset( $_POST[ 'Change' ] ) ) {
	// Check Anti-CSRF token
	checkToken( $_REQUEST[ 'user_token' ], $_SESSION[ 'session_token' ], 'index.php' );

	// Hide the CAPTCHA form
	$hide_form = true;

	// Step two trusted a passed_captcha=true field supplied by the client, which
	// is the client asserting its own authorisation. There are no steps now.

	// Get input
	$pass_new  = $_POST[ 'password_new' ];
	$pass_conf = $_POST[ 'password_conf' ];
	$pass_curr = stripslashes( $_POST[ 'password_current' ] );

	// Check CAPTCHA from 3rd party. Its verdict is the only thing consulted:
	// there is no alternative branch a caller can steer themselves into.
	$resp = recaptcha_check_answer(
		$_DVWA[ 'recaptcha_private_key' ],
		$_POST['g-recaptcha-response']
	);

	if( !$resp ) {
		// What happens when the CAPTCHA was entered incorrectly
		$html .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
	}
	else {
		// Check that the current password is correct
		$current_user = dvwaCurrentUser();
		$data = $db->prepare( 'SELECT password FROM users WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
		$data->execute();
		$row = $data->fetch();

		$current_ok = ( $row !== false ) && dvwaPasswordVerify( $pass_curr, $row[ 'password' ] );

		if( ( $pass_new === $pass_conf ) && $current_ok ) {
			// Stored with password_hash(), never as a bare digest.
			dvwaPasswordStore( $current_user, stripslashes( $pass_new ) );

			// Feedback for the end user - success!
			$html .= "<pre>Password Changed.</pre>";
		}
		else {
			// Feedback for the end user - failed!
			$html .= "<pre>Either your current password is incorrect or the new passwords did not match.<br />Please try again.</pre>";
			$hide_form = false;
		}
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
